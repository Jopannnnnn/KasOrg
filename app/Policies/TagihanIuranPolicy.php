<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\TagihanIuran;
use App\Models\User;

class TagihanIuranPolicy
{
    public function create(User $user): bool
    {
        return $user->isAktif() && $user->isAdmin();
    }

    public function pay(User $user, TagihanIuran $tagihan): bool
    {
        return $user->isAktif() && $user->role?->kode === Role::ANGGOTA && $user->id === $tagihan->user_id;
    }
}
