<?php

namespace App\Services;

use App\Models\JenisIuran;
use App\Models\KategoriTransaksi;
use App\Models\PembayaranIuran;
use App\Models\Periode;
use App\Models\Role;
use App\Models\TagihanIuran;
use App\Models\TransaksiKas;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class IuranService
{
    public function createBill(User $admin, array $data): TagihanIuran
    {
        Gate::forUser($admin)->authorize('create', TagihanIuran::class);
        try {
            return DB::transaction(function () use ($data) {
                $member = User::whereKey($data['user_id'])->lockForUpdate()->first();
                if (! $member || ! $member->isAktif() || $member->role?->kode !== Role::ANGGOTA) {
                    throw ValidationException::withMessages(['user_id' => 'Pilih anggota aktif yang belum diarsipkan.']);
                }
                $jenis = JenisIuran::whereKey($data['jenis_iuran_id'])->lockForUpdate()->firstOrFail();
                if (! $jenis->aktif || bccomp($jenis->nominal, '0', 2) <= 0) {
                    throw ValidationException::withMessages(['jenis_iuran_id' => 'Pilih jenis iuran aktif dengan nominal lebih dari nol.']);
                }
                $this->openPeriod($data['periode_id']);

                return TagihanIuran::create([
                    'user_id' => $member->id, 'jenis_iuran_id' => $jenis->id, 'periode_id' => $data['periode_id'],
                    'nominal' => $jenis->nominal, 'jatuh_tempo' => $data['jatuh_tempo'] ?? null, 'status' => TagihanIuran::BELUM_LUNAS,
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['user_id' => 'Tagihan anggota untuk jenis iuran dan periode ini sudah ada.']);
        }
    }

    public function submit(User $member, TagihanIuran $bill, array $data, ?string $proofPath): PembayaranIuran
    {
        return DB::transaction(function () use ($member, $bill, $data, $proofPath) {
            // Semua pengajuan/verifikasi mengunci induk yang sama, termasuk saat belum ada pembayaran.
            $bill = TagihanIuran::whereKey($bill->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($member)->authorize('pay', $bill);
            $this->openPeriod($bill->periode_id);
            if ($bill->status !== TagihanIuran::BELUM_LUNAS || $bill->pembayaranIuran()->whereIn('status', ['menunggu', 'disetujui'])->exists()
                || TransaksiKas::withTrashed()->where('tagihan_iuran_id', $bill->id)->exists()) {
                throw ValidationException::withMessages(['pembayaran' => 'Tagihan sudah lunas atau masih memiliki pengajuan yang menunggu verifikasi.']);
            }
            if (bccomp($bill->nominal, '0', 2) <= 0 || ($data['metode'] === 'transfer' && ! $proofPath)) {
                throw ValidationException::withMessages(['pembayaran' => 'Nominal tagihan atau bukti transfer tidak valid.']);
            }

            return PembayaranIuran::create([
                'tagihan_iuran_id' => $bill->id, 'diajukan_oleh' => $member->id, 'metode' => $data['metode'],
                'nominal' => $bill->nominal, 'tanggal_bayar' => $data['tanggal_bayar'],
                'bukti_path' => $data['metode'] === 'transfer' ? $proofPath : null,
                'status' => PembayaranIuran::STATUS_MENUNGGU, 'catatan' => $data['catatan'] ?? null,
            ]);
        }, 3);
    }

    public function verify(User $admin, PembayaranIuran $payment, array $data): void
    {
        Gate::forUser($admin)->authorize('verify', $payment);
        try {
            DB::transaction(function () use ($admin, $payment, $data) {
                $bill = TagihanIuran::whereKey($payment->tagihan_iuran_id)->lockForUpdate()->firstOrFail();
                $payment = PembayaranIuran::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($payment->status !== PembayaranIuran::STATUS_MENUNGGU || $bill->status !== TagihanIuran::BELUM_LUNAS) {
                    throw ValidationException::withMessages(['pembayaran' => 'Pengajuan sudah diproses atau tagihan sudah lunas.']);
                }
                if ($data['keputusan'] === 'disetujui') {
                    $this->openPeriod($bill->periode_id);
                    if (bccomp($payment->nominal, $bill->nominal, 2) !== 0 || bccomp($bill->nominal, '0', 2) <= 0
                        || ($payment->metode === 'transfer' && (! $payment->bukti_path || ! Storage::disk('bukti')->exists($payment->bukti_path)))) {
                        throw ValidationException::withMessages(['pembayaran' => 'Nominal atau bukti pengajuan tidak sesuai dengan tagihan.']);
                    }
                    $category = KategoriTransaksi::where('nama', 'Iuran anggota')->where('jenis', 'pemasukan')->where('is_sistem', true)->first();
                    if (! $category) {
                        throw ValidationException::withMessages(['pembayaran' => 'Kategori sistem Iuran anggota belum tersedia. Hubungi pengelola.']);
                    }
                    TransaksiKas::create([
                        'tanggal' => today()->toDateString(), 'jenis' => 'pemasukan', 'kategori_transaksi_id' => $category->id,
                        'tagihan_iuran_id' => $bill->id, 'pembayaran_iuran_id' => $payment->id,
                        'keterangan' => 'Pembayaran iuran tagihan #'.$bill->id,
                        'nominal' => $bill->nominal, 'sumber' => 'pembayaran_iuran', 'dibuat_oleh' => $admin->id,
                    ]);
                    $bill->update(['status' => TagihanIuran::LUNAS, 'lunas_at' => now()]);
                }
                $payment->update([
                    'status' => $data['keputusan'], 'diverifikasi_oleh' => $admin->id, 'diverifikasi_at' => now(),
                    'alasan_penolakan' => $data['keputusan'] === 'ditolak' ? $data['alasan_penolakan'] : null,
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['pembayaran' => 'Pemasukan untuk tagihan ini sudah tercatat. Pengajuan tidak diproses ulang.']);
        }
    }

    private function openPeriod(int $id): Periode
    {
        $period = Periode::whereKey($id)->lockForUpdate()->firstOrFail();
        if ($period->status !== Periode::TERBUKA) {
            throw ValidationException::withMessages(['periode_id' => 'Periode telah ditutup. Hubungi Admin untuk tindak lanjut.']);
        }

        return $period;
    }
}
