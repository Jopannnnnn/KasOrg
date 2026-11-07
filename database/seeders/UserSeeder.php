<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Seed development-only demo accounts.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Akun demo KasOrg hanya boleh dibuat pada environment local atau testing.');
        }

        // Pengecualian hanya untuk dua akun demo tetap pada local/testing.
        // Bukan aturan validasi password akun nyata atau production.
        $passwords = [
            Role::ADMIN => config('demo.passwords.admin'),
            Role::ANGGOTA => config('demo.passwords.anggota'),
        ];

        foreach ($passwords as $password) {
            if (! is_string($password) || strlen($password) < 8) {
                throw new RuntimeException('Konfigurasi password tiap akun demo wajib tersedia dan minimal 8 karakter untuk local/testing.');
            }
        }

        if (hash_equals($passwords[Role::ADMIN], $passwords[Role::ANGGOTA])) {
            throw new RuntimeException('Password akun demo Admin dan Anggota harus berbeda.');
        }

        $roles = Role::query()
            ->whereIn('kode', [Role::ADMIN, Role::ANGGOTA])
            ->get()
            ->keyBy('kode');

        foreach ([Role::ADMIN, Role::ANGGOTA] as $kode) {
            if (! $roles->has($kode)) {
                throw new RuntimeException("Role {$kode} belum tersedia. RoleSeeder harus dijalankan lebih dahulu.");
            }
        }

        $accounts = [
            [
                'name' => 'Admin KasOrg',
                'nim' => '24170001',
                'email' => 'admin@kasorg.test',
                'role_id' => $roles->get(Role::ADMIN)->getKey(),
                'password' => $passwords[Role::ADMIN],
            ],
            [
                'name' => 'Anggota Demo',
                'nim' => '24170002',
                'email' => 'anggota@kasorg.test',
                'role_id' => $roles->get(Role::ANGGOTA)->getKey(),
                'password' => $passwords[Role::ANGGOTA],
            ],
        ];

        foreach ($accounts as $account) {
            $existing = User::withTrashed()
                ->where(function ($query) use ($account): void {
                    $query
                        ->where('nim', $account['nim'])
                        ->orWhere('email', $account['email']);
                })
                ->first();

            if ($existing !== null) {
                $isExactDemoAccount = $existing->nim === $account['nim']
                    && $existing->email === $account['email']
                    && $existing->name === $account['name']
                    && (int) $existing->role_id === (int) $account['role_id']
                    && $existing->status_akun === User::STATUS_AKTIF
                    && ! $existing->trashed();

                if (! $isExactDemoAccount) {
                    throw new RuntimeException('Identitas akun demo bertabrakan dengan data pengguna yang sudah ada. Data tidak diubah.');
                }

                continue;
            }

            User::query()->create([
                ...$account,
                'status_akun' => User::STATUS_AKTIF,
            ]);
        }
    }
}
