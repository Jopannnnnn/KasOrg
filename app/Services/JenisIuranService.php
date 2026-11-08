<?php

namespace App\Services;

use App\Models\JenisIuran;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class JenisIuranService
{
    public function save(User $admin, array $data, ?JenisIuran $jenisIuran = null): JenisIuran
    {
        try {
            return DB::transaction(function () use ($admin, $data, $jenisIuran) {
                if ($jenisIuran) {
                    $jenisIuran = JenisIuran::whereKey($jenisIuran->id)->lockForUpdate()->firstOrFail();
                    Gate::forUser($admin)->authorize('update', $jenisIuran);
                } else {
                    Gate::forUser($admin)->authorize('create', JenisIuran::class);
                }
                $nominal = (string) $data['nominal'];
                if (! preg_match('/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', $nominal) || bccomp($nominal, '0', 2) <= 0) {
                    throw ValidationException::withMessages(['nominal' => 'Nominal harus lebih dari nol dengan maksimal dua angka desimal.']);
                }
                $attributes = [
                    'nama' => $data['nama'], 'nominal' => bcadd($nominal, '0', 2),
                    'deskripsi' => $data['deskripsi'] ?? null, 'aktif' => $data['aktif'] ?? true,
                ];
                // Hanya aturan iuran: snapshot tagihan, pembayaran, dan kas tidak disentuh.
                if ($jenisIuran) {
                    $jenisIuran->update($attributes);

                    return $jenisIuran;
                }

                return JenisIuran::create($attributes);
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['nama' => 'Nama jenis iuran sudah digunakan.']);
        }
    }

    public function setStatus(User $admin, JenisIuran $jenisIuran, bool $aktif): void
    {
        DB::transaction(function () use ($admin, $jenisIuran, $aktif) {
            $jenisIuran = JenisIuran::whereKey($jenisIuran->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($admin)->authorize('update', $jenisIuran);
            $jenisIuran->update(['aktif' => $aktif]);
        }, 3);
    }

    public function delete(User $admin, JenisIuran $jenisIuran): void
    {
        DB::transaction(function () use ($admin, $jenisIuran) {
            // Pembuatan tagihan juga mengunci row jenis. FK RESTRICT tetap aktif.
            $jenisIuran = JenisIuran::whereKey($jenisIuran->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($admin)->authorize('delete', $jenisIuran);
            if ($jenisIuran->tagihanIuran()->exists()) {
                throw ValidationException::withMessages(['jenis_iuran' => 'Jenis iuran sudah memiliki tagihan. Nonaktifkan untuk mempertahankan riwayat.']);
            }
            $jenisIuran->delete();
        }, 3);
    }
}
