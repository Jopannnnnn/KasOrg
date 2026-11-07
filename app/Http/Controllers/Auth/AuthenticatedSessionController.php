<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $dashboard = match ($request->user()->role?->kode) {
            Role::ADMIN => 'admin.dashboard',
            Role::ANGGOTA => 'anggota.dashboard',
            default => null,
        };

        if ($dashboard === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Role akun tidak diizinkan mengakses aplikasi.');
        }

        // Jangan mengikuti intended URL lintas-role atau kembali ke preview.
        $request->session()->forget('url.intended');

        return redirect()->route($dashboard);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
