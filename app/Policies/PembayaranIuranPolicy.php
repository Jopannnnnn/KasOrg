<?php

namespace App\Policies;

use App\Models\PembayaranIuran;
use App\Models\Role;
use App\Models\User;

class PembayaranIuranPolicy
{
    public function view(User $user, PembayaranIuran $pembayaran): bool
    {
        return $user->isAktif() && ($user->isAdmin() || ($user->role?->kode === Role::ANGGOTA && $pembayaran->tagihanIuran->user_id === $user->id));
    }

    public function verify(User $user, PembayaranIuran $pembayaran): bool
    {
        return $user->isAktif() && $user->isAdmin();
    }
}
