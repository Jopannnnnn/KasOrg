<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LogicException;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nim' => fake()->unique()->numerify('25######'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::password(32)),
            'role_id' => fn (): int => $this->roleId(Role::ANGGOTA),
            'status_akun' => User::STATUS_AKTIF,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $this->roleId(Role::ADMIN),
        ]);
    }

    /**
     * Indicate that the user is an organization member.
     */
    public function anggota(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $this->roleId(Role::ANGGOTA),
        ]);
    }

    /**
     * Indicate that the user account is inactive.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => User::STATUS_NONAKTIF,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function roleId(string $kode): int
    {
        $roleId = Role::query()->where('kode', $kode)->value('id');

        if ($roleId === null) {
            throw new LogicException("Role {$kode} belum tersedia. Jalankan RoleSeeder sebelum menggunakan UserFactory.");
        }

        return (int) $roleId;
    }
}
