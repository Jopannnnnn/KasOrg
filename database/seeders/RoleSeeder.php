<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the two supported KasOrg roles without overwriting existing data.
     */
    public function run(): void
    {
        foreach ([
            Role::ADMIN => 'Admin',
            Role::ANGGOTA => 'Anggota',
        ] as $kode => $nama) {
            Role::query()->firstOrCreate(
                ['kode' => $kode],
                ['nama' => $nama],
            );
        }
    }
}
