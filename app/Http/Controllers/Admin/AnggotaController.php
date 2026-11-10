<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anggota\IndexAnggotaRequest;
use App\Http\Requests\Anggota\SaveAnggotaRequest;
use App\Http\Requests\Anggota\StoreAnggotaRequest;
use App\Http\Requests\Anggota\UpdateAnggotaRequest;
use App\Http\Requests\Anggota\UpdateStatusAnggotaRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    public function index(IndexAnggotaRequest $request): View
    {
        $filters = $request->validated();
        $base = User::query()->whereHas('role', fn ($query) => $query->where('kode', Role::ANGGOTA));
        $stats = [
            'total' => (clone $base)->count(),
            'aktif' => (clone $base)->where('status_akun', User::STATUS_AKTIF)->count(),
            'nonaktif' => (clone $base)->where('status_akun', User::STATUS_NONAKTIF)->count(),
        ];
        $query = (clone $base)->with('role:id,kode,nama');
        $search = $filters['q'] ?? '';
        if ($search !== null && $search !== '') {
            // Pencarian literal, parameter binding, termasuk karakter % dan _.
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
            $query->where(function ($query) use ($pattern): void {
                $query->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("nim LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$pattern]);
            });
        }
        if ($status = $filters['status'] ?? null) {
            $query->where('status_akun', $status);
        }
        $members = $query->orderBy('name')->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 5))->appends($request->safe()->except('page'));

        $formMember = null;
        if (session('anggota_form.mode') === 'edit') {
            $candidate = (clone $base)->find(session('anggota_form.id'));
            if ($candidate && Gate::allows('update', $candidate)) {
                $formMember = $candidate;
            }
        }

        return view('admin.anggota.index', compact('members', 'stats', 'filters', 'formMember'));
    }

    public function store(StoreAnggotaRequest $request): RedirectResponse
    {
        $this->save($request, function () use ($request): void {
            $role = Role::query()->where('kode', Role::ANGGOTA)->firstOrFail();
            User::create([
                ...$request->safe()->only(['name', 'nim', 'email', 'status_akun', 'password']),
                'role_id' => $role->getKey(),
            ]); // Cast hashed pada User; tidak ada password bawaan atau hashing ganda.
        });

        return redirect()->route('admin.anggota')->with('success', 'Anggota berhasil ditambahkan. Sampaikan kata sandi awal melalui saluran privat.');
    }

    public function update(UpdateAnggotaRequest $request, User $anggota): RedirectResponse
    {
        $this->save($request, function () use ($request, $anggota): void {
            $member = User::query()->lockForUpdate()->findOrFail($anggota->getKey());
            Gate::authorize('update', $member);
            $data = $request->safe()->only(['name', 'nim', 'email', 'status_akun']);
            if ($member->email !== $data['email']) {
                $member->email_verified_at = null;
            }
            $member->fill($data)->save();
        });

        return redirect()->route('admin.anggota')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function updateStatus(UpdateStatusAnggotaRequest $request, User $anggota): RedirectResponse
    {
        DB::transaction(function () use ($request, $anggota): void {
            $member = User::query()->lockForUpdate()->findOrFail($anggota->getKey());
            Gate::authorize('updateStatus', $member);
            $member->update($request->safe()->only('status_akun'));
        });

        return redirect()->route('admin.anggota')->with('success', 'Status akun anggota berhasil diperbarui.');
    }

    public function destroy(User $anggota): RedirectResponse
    {
        Gate::authorize('delete', $anggota);
        DB::transaction(function () use ($anggota): void {
            $member = User::query()->lockForUpdate()->findOrFail($anggota->getKey());
            Gate::authorize('delete', $member);
            $member->update(['status_akun' => User::STATUS_NONAKTIF]);
            $member->delete(); // Selalu soft delete; row user dan seluruh FK finansial tetap ada.
        });

        return redirect()->route('admin.anggota')->with('success', 'Anggota diarsipkan dengan soft delete. Riwayat keuangan tidak dihapus.');
    }

    private function save(SaveAnggotaRequest $request, callable $action): void
    {
        try {
            DB::transaction($action);
        } catch (UniqueConstraintViolationException) {
            // Constraint database tetap menangani race setelah validasi form.
            $request->rememberFormContext();
            throw ValidationException::withMessages([
                'nim' => 'NIM atau email sudah digunakan. Periksa kembali data anggota.',
            ])->redirectTo(route('admin.anggota'));
        }
    }
}
