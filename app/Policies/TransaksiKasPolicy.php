<?php

namespace App\Policies;

use App\Models\TransaksiKas;
use App\Models\User;

class TransaksiKasPolicy
{
    public function create(User $user): bool
    {
        return ! $user->trashed() && $user->isAktif() && $user->isAdmin();
    }

    public function update(User $user, TransaksiKas $transaksi): bool
    {
        return $this->create($user) && ! $transaksi->trashed()
            && $transaksi->sumber === TransaksiKas::SUMBER_MANUAL
            && $transaksi->tagihan_iuran_id === null && $transaksi->pembayaran_iuran_id === null;
    }

    public function delete(User $user, TransaksiKas $transaksi): bool
    {
        return $this->update($user, $transaksi);
    }
}
