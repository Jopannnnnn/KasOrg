<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RingkasanKasController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::get('/dashboard', [RingkasanKasController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('anggota')->name('anggota.')->middleware('role:anggota')->group(function (): void {
        Route::get('/dashboard', [RingkasanKasController::class, 'dashboard'])->name('dashboard');
    });
});

if (app()->environment('local')) {
    Route::prefix('ui-preview')
        ->name('ui-preview.')
        ->middleware('preview.local')
        ->group(function (): void {
            Route::view('/login', 'auth.login')->name('login');
            Route::view('/admin/dashboard', 'admin.dashboard-preview')->name('admin.dashboard');
            Route::view('/admin/anggota', 'admin.anggota.index')->name('admin.anggota');
            Route::view('/admin/iuran', 'admin.iuran.index')->name('admin.iuran');
            Route::view('/admin/verifikasi', 'admin.pembayaran.verifikasi')->name('admin.verifikasi');
            Route::view('/admin/transaksi', 'admin.transaksi.index')->name('admin.transaksi');
            Route::view('/admin/laporan', 'laporan.index', ['role' => 'admin'])->name('admin.laporan');
            Route::view('/admin/transparansi', 'transparansi.index', ['role' => 'admin'])->name('admin.transparansi');

            Route::view('/anggota/dashboard', 'anggota.dashboard-preview')->name('anggota.dashboard');
            Route::view('/anggota/tagihan', 'anggota.tagihan.index')->name('anggota.tagihan');
            Route::view('/anggota/laporan', 'laporan.index', ['role' => 'anggota'])->name('anggota.laporan');
            Route::view('/anggota/transparansi', 'transparansi.index', ['role' => 'anggota'])->name('anggota.transparansi');
        });
}
