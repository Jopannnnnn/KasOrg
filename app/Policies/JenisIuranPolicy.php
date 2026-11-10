<?php

namespace App\Policies;

use App\Models\JenisIuran;
use App\Models\User;

class JenisIuranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAktif() && $user->isAdmin() && ! $user->trashed();
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, JenisIuran $jenisIuran): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, JenisIuran $jenisIuran): bool
    {
        return $this->viewAny($user);
    }
}
