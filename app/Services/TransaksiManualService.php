<?php

namespace App\Services;

use App\Models\KategoriTransaksi;
use App\Models\Periode;
use App\Models\TransaksiKas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TransaksiManualService
{
    public function save(User $admin, array $data, ?TransaksiKas $transaction = null): TransaksiKas
    {
        return DB::transaction(function () use ($admin, $data, $transaction) {
            if ($transaction) {
                $transaction = TransaksiKas::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
                Gate::forUser($admin)->authorize('update', $transaction);
            } else {
                Gate::forUser($admin)->authorize('create', TransaksiKas::class);
            }
            // Skema tidak punya periode_id: kunci periode dalam urutan yang sama,
            // lalu periksa tanggal lama DAN baru, termasuk periode yang overlap.
            $periods = Periode::orderBy('id')->lockForUpdate()->get();
            if ($transaction) {
                $this->openPeriodForDate($periods, $transaction->tanggal->toDateString());
            }
            $this->openPeriodForDate($periods, $data['tanggal'], (int) $data['periode_id']);
            $category = KategoriTransaksi::whereKey($data['kategori_transaksi_id'])->lockForUpdate()->first();
            if (! $category || $category->jenis !== $data['jenis']) {
                throw ValidationException::withMessages(['kategori_transaksi_id' => 'Pilih kategori yang sesuai jenis transaksi.']);
            }
            // Tetap periksa data ketika service dipanggil di luar HTTP Form Request.
            if (! is_string($data['nominal']) || ! preg_match('/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', $data['nominal']) || bccomp($data['nominal'], '0', 2) <= 0) {
                throw ValidationException::withMessages(['nominal' => 'Nominal harus positif dengan maksimal 13 digit dan dua angka desimal.']);
            }
            $attributes = [
                'tanggal' => $data['tanggal'], 'jenis' => $data['jenis'], 'kategori_transaksi_id' => $category->id,
                'nominal' => bcadd($data['nominal'], '0', 2), 'keterangan' => $data['keterangan'],
            ];
            if ($transaction) {
                $transaction->update($attributes + ['diubah_oleh' => $admin->id]);
            } else {
                $transaction = TransaksiKas::create($attributes + [
                    'sumber' => TransaksiKas::SUMBER_MANUAL, 'tagihan_iuran_id' => null, 'pembayaran_iuran_id' => null,
                    'dibuat_oleh' => $admin->id, 'diubah_oleh' => null,
                ]);
            }

            return $transaction;
        }, 3);
    }

    public function delete(User $admin, TransaksiKas $transaction): int
    {
        return DB::transaction(function () use ($admin, $transaction) {
            $transaction = TransaksiKas::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($admin)->authorize('delete', $transaction);
            $periods = Periode::orderBy('id')->lockForUpdate()->get();
            $period = $this->openPeriodForDate($periods, $transaction->tanggal->toDateString());
            $transaction->update(['diubah_oleh' => $admin->id]);
            $transaction->delete(); // Tidak menyentuh tagihan/pembayaran; FK tetap aktif.

            return $period->id;
        }, 3);
    }

    private function openPeriodForDate($periods, string $date, ?int $selected = null): Periode
    {
        $chosen = $selected === null ? null : $periods->firstWhere('id', $selected);
        if ($selected !== null && (! $chosen || $chosen->tanggal_mulai->toDateString() > $date || $chosen->tanggal_selesai->toDateString() < $date)) {
            throw ValidationException::withMessages(['tanggal' => 'Tanggal transaksi harus berada dalam rentang periode yang dipilih.']);
        }
        $matches = $periods->filter(fn ($p) => $p->tanggal_mulai->toDateString() <= $date && $p->tanggal_selesai->toDateString() >= $date);
        if ($matches->count() !== 1 || $matches->first()->status !== Periode::TERBUKA) {
            throw ValidationException::withMessages(['periode_id' => 'Tanggal transaksi harus berada pada tepat satu periode terbuka. Periode tertutup, tidak tersedia, atau tumpang tindih tidak dapat diubah.']);
        }
        $period = $matches->first();
        if ($selected !== null && $period->id !== $selected) {
            throw ValidationException::withMessages(['tanggal' => 'Tanggal transaksi harus berada dalam rentang periode yang dipilih.']);
        }

        return $period;
    }
}
