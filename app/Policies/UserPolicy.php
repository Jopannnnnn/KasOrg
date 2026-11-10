<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Support\DemoAccountProtection;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $admin): bool
    {
        return $admin->isAktif() && $admin->isAdmin();
    }

    public function create(User $admin): bool
    {
        return $this->viewAny($admin);
    }

    public function update(User $admin, User $anggota): Response
    {
        if (! $this->viewAny($admin) || $anggota->role?->kode !== Role::ANGGOTA || $anggota->trashed()) {
            return Response::deny('Hanya akun anggota yang boleh dikelola melalui halaman ini.');
        }

        if (DemoAccountProtection::protects($anggota)) {
            return Response::deny('Akun demo utama dilindungi dari perubahan melalui CRUD anggota.');
        }

        return Response::allow();
    }

    public function updateStatus(User $admin, User $anggota): Response
    {
        return $this->update($admin, $anggota);
    }

    public function delete(User $admin, User $anggota): Response
    {
        return $this->update($admin, $anggota);
    }
}
