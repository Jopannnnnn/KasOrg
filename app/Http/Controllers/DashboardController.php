<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $route = match ($request->user()->role?->kode) {
            Role::ADMIN => 'admin.dashboard',
            Role::ANGGOTA => 'anggota.dashboard',
            default => null,
        };

        abort_if($route === null, 403, 'Role akun tidak diizinkan mengakses aplikasi.');

        return redirect()->route($route);
    }
}
