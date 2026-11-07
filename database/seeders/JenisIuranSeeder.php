<?php

namespace Database\Seeders;

use App\Models\JenisIuran;
use Illuminate\Database\Seeder;

class JenisIuranSeeder extends Seeder
{
    /**
     * Seed one minimal contribution type for UTS demonstrations.
     */
    public function run(): void
    {
        JenisIuran::query()->firstOrCreate(
            ['nama' => 'Iuran Bulanan'],
            [
                'nominal' => '75000.00',
                'deskripsi' => 'Iuran rutin bulanan anggota organisasi.',
                'aktif' => true,
            ],
        );
    }
}
