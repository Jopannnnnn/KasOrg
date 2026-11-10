<?php

namespace App\Services;

use App\Models\KategoriTransaksi;
use App\Models\Periode;
use App\Models\TransaksiKas;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RingkasanKasService
{
    public function scope(array $filters): array
    {
        $periods = Periode::orderBy('tanggal_mulai')->orderBy('id')->get();
        $default = $periods->filter(fn ($p) => $p->status === Periode::TERBUKA && $p->tanggal_mulai->toDateString() <= today()->toDateString())->last()
            ?? $periods->last();
        $selected = $filters['periode_id'] ?? $default?->id;
        $selectedPeriods = $selected === 'semua' ? $periods : $periods->where('id', $selected)->values();
        $first = $selectedPeriods->first();
        $last = $selectedPeriods->sortBy('tanggal_selesai')->last();

        return [
            'periods' => $periods, 'selected' => $selected, 'ids' => $selectedPeriods->pluck('id')->all(),
            'start' => $first?->tanggal_mulai->toDateString(), 'end' => $last?->tanggal_selesai->toDateString(),
            'opening' => $first?->saldo_awal ?? '0.00',
            'label' => $selected === 'semua' ? 'Semua periode' : ($first?->tanggal_mulai->locale('id')->translatedFormat('F Y') ?? 'Belum ada periode'),
            'closed' => $last?->status === Periode::DITUTUP,
            'snapshot' => $last?->status === Periode::DITUTUP ? $last->saldo_akhir_snapshot : null,
        ];
    }

    public function transactions(array $scope): Builder
    {
        $query = TransaksiKas::query(); // SoftDeletes otomatis mengecualikan arsip.
        if (! $scope['start'] || ! $scope['end']) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereBetween('tanggal', [$scope['start'], $scope['end']])->where(function ($query) {
            $query->where(function ($manual) {
                $manual->where('sumber', 'manual')->whereNull('pembayaran_iuran_id')->whereNull('tagihan_iuran_id');
            })->orWhere(function ($payment) {
                $payment->where('sumber', 'pembayaran_iuran')->where('jenis', 'pemasukan')
                    ->whereHas('pembayaranIuran', function ($p) {
                        $p->where('status', 'disetujui')
                            ->whereColumn('pembayaran_iuran.tagihan_iuran_id', 'transaksi_kas.tagihan_iuran_id')
                            ->whereColumn('pembayaran_iuran.nominal', 'transaksi_kas.nominal');
                    });
            });
        });
    }

    public function summary(array $scope): array
    {
        $income = $expense = '0.00';
        $monthly = $categories = [];
        $count = 0;
        $updatedAt = null;
        // Satu lintasan nilai DECIMAL, tidak memakai SUM float SQLite atau uang dalam float PHP.
        foreach ($this->transactions($scope)->select(['id', 'tanggal', 'jenis', 'nominal', 'kategori_transaksi_id', 'updated_at'])->cursor() as $row) {
            $count++;
            $month = $row->tanggal->format('Y-m');
            $monthly[$month] ??= ['income' => '0.00', 'expense' => '0.00'];
            $key = $row->jenis === 'pemasukan' ? 'income' : 'expense';
            $monthly[$month][$key] = bcadd($monthly[$month][$key], $row->nominal, 2);
            if ($key === 'income') {
                $income = bcadd($income, $row->nominal, 2);
            } else {
                $expense = bcadd($expense, $row->nominal, 2);
                $categories[$row->kategori_transaksi_id] = bcadd($categories[$row->kategori_transaksi_id] ?? '0.00', $row->nominal, 2);
            }
            if ($row->updated_at && (! $updatedAt || $row->updated_at->gt($updatedAt))) {
                $updatedAt = $row->updated_at;
            }
        }
        $closing = bcsub(bcadd($scope['opening'], $income, 2), $expense, 2);
        $bars = [];
        if ($scope['start']) {
            $start = CarbonImmutable::parse($scope['start'])->startOfMonth();
            $end = CarbonImmutable::parse($scope['end'])->startOfMonth();
            for ($date = $start; $date->lte($end); $date = $date->addMonth()) {
                $bars[] = ['month' => $date->locale('id')->translatedFormat('M Y')] + ($monthly[$date->format('Y-m')] ?? ['income' => '0.00', 'expense' => '0.00']);
            }
        }
        $maximum = '0.00';
        foreach ($bars as $bar) {
            foreach (['income', 'expense'] as $key) {
                if (bccomp($bar[$key], $maximum, 2) > 0) {
                    $maximum = $bar[$key];
                }
            }
        }
        foreach ($bars as &$bar) {
            foreach (['income', 'expense'] as $key) {
                $bar[$key.'_height'] = bccomp($maximum, '0', 2) > 0 ? bcdiv(bcmul($bar[$key], '100', 6), $maximum, 4) : '0';
            }
        }
        unset($bar);
        $categoryNames = KategoriTransaksi::whereIn('id', array_keys($categories))->pluck('nama', 'id');
        $allocations = [];
        foreach ($categories as $id => $nominal) {
            $allocations[] = ['name' => $categoryNames[$id], 'amount' => $nominal, 'percentage' => bccomp($expense, '0', 2) > 0 ? bcdiv(bcmul($nominal, '100', 6), $expense, 4) : '0'];
        }

        $updatedAt = $updatedAt?->toDateTimeString();

        return compact('income', 'expense', 'closing', 'bars', 'allocations', 'count', 'updatedAt') + [
            'opening' => $scope['opening'],
            'snapshotMismatch' => $scope['snapshot'] !== null && bccomp($scope['snapshot'], $closing, 2) !== 0,
        ];
    }

    public function rows(Collection $transactions, bool $public): Collection
    {
        return $transactions->map(fn ($row) => [
            'date' => $row->tanggal->format('d/m/Y'), 'type' => ucfirst($row->jenis),
            'category' => $row->kategoriTransaksi->nama,
            // Keterangan bebas dapat berisi NIM/email/nama. Anggota hanya menerima label organisasi.
            'description' => $public ? ($row->sumber === 'pembayaran_iuran' ? 'Pembayaran iuran anggota' : ucfirst($row->jenis).' kas organisasi') : $row->keterangan,
            'amount' => ($row->jenis === 'pemasukan' ? '+ ' : '- ').self::rupiah($row->nominal),
            'positive' => $row->jenis === 'pemasukan',
        ]);
    }

    public static function rupiah(string $amount): string
    {
        $negative = str_starts_with($amount, '-');
        [$whole, $fraction] = explode('.', bcadd(ltrim($amount, '-'), '0', 2));
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole);

        return ($negative ? '- ' : '').'Rp '.$whole.','.$fraction;
    }
}
