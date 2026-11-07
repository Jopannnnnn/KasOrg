<?php

namespace Database\Seeders;

use App\Models\KategoriTransaksi;
use Illuminate\Database\Seeder;

class KategoriTransaksiSeeder extends Seeder
{
    /**
     * Seed the minimal transaction categories needed by the KasOrg use case.
     */
    public function run(): void
    {
        $categories = [
            ['nama' => 'Iuran anggota', 'jenis' => KategoriTransaksi::PEMASUKAN, 'is_sistem' => true],
            ['nama' => 'Pendapatan lainnya', 'jenis' => KategoriTransaksi::PEMASUKAN, 'is_sistem' => false],
            ['nama' => 'Kegiatan', 'jenis' => KategoriTransaksi::PENGELUARAN, 'is_sistem' => false],
        ];

        foreach ($categories as $category) {
            KategoriTransaksi::query()->firstOrCreate(
                [
                    'nama' => $category['nama'],
                    'jenis' => $category['jenis'],
                ],
                ['is_sistem' => $category['is_sistem']],
            );
        }
    }
}
