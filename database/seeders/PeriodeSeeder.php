<?php

namespace Database\Seeders;

use App\Models\Periode;
use Illuminate\Database\Seeder;
use RuntimeException;

class PeriodeSeeder extends Seeder
{
    /**
     * Seed one open period without inventing transactions or balances.
     */
    public function run(): void
    {
        if (Periode::query()->where('status', Periode::TERBUKA)->exists()) {
            return;
        }

        $start = now()->startOfMonth();
        $existingPeriod = Periode::query()
            ->where('tahun', $start->year)
            ->where('bulan', $start->month)
            ->first();

        if ($existingPeriod !== null) {
            throw new RuntimeException('Periode bulan berjalan sudah tersedia tetapi tidak terbuka. Data tidak diubah.');
        }

        Periode::query()->create([
            'tahun' => $start->year,
            'bulan' => $start->month,
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $start->copy()->endOfMonth()->toDateString(),
            'saldo_awal' => '0.00',
            'saldo_akhir_snapshot' => null,
            'status' => Periode::TERBUKA,
            'ditutup_oleh' => null,
            'ditutup_at' => null,
        ]);
    }
}
