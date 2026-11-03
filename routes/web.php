<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', function () {
    if (app()->environment('local')) {
        return view('admin.dashboard');
    }

    return view('welcome');
});

if (app()->environment('local')) {
    Route::prefix('ui-preview')
        ->name('ui-preview.')
        ->withoutMiddleware([
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
        ])
        ->group(function (): void {
            Route::view('/login', 'auth.login')->name('login');
            Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
            Route::view('/admin/anggota', 'admin.anggota.index')->name('admin.anggota');
            Route::view('/admin/iuran', 'admin.iuran.index')->name('admin.iuran');
            Route::view('/admin/verifikasi', 'admin.pembayaran.verifikasi')->name('admin.verifikasi');
            Route::view('/admin/transaksi', 'admin.transaksi.index')->name('admin.transaksi');
            Route::view('/admin/laporan', 'laporan.index', ['role' => 'admin'])->name('admin.laporan');
            Route::view('/admin/transparansi', 'transparansi.index', ['role' => 'admin'])->name('admin.transparansi');

            Route::view('/anggota/dashboard', 'anggota.dashboard')->name('anggota.dashboard');
            Route::view('/anggota/tagihan', 'anggota.tagihan.index')->name('anggota.tagihan');
            Route::view('/anggota/laporan', 'laporan.index', ['role' => 'anggota'])->name('anggota.laporan');
            Route::view('/anggota/transparansi', 'transparansi.index', ['role' => 'anggota'])->name('anggota.transparansi');
        });
}
