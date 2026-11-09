<?php

use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\JenisIuranController;
use App\Http\Controllers\Admin\TransaksiKasController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IuranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\RingkasanKasController;
use Illuminate\Http\Request;
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

    Route::get('/pembayaran/{pembayaran}/bukti', [PembayaranController::class, 'proof'])->name('pembayaran.bukti');

    // CRUD anggota, tagihan, pengajuan dan verifikasi menggunakan database.
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::get('/dashboard', [RingkasanKasController::class, 'dashboard'])->name('dashboard');
        Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota');
        Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
        Route::put('/anggota/{anggota}', [AnggotaController::class, 'update'])->name('anggota.update');
        Route::patch('/anggota/{anggota}/status', [AnggotaController::class, 'updateStatus'])->name('anggota.status');
        Route::delete('/anggota/{anggota}', [AnggotaController::class, 'destroy'])->name('anggota.destroy');
        Route::get('/iuran', [IuranController::class, 'index'])->name('iuran');
        Route::post('/iuran/tagihan', [IuranController::class, 'store'])->name('iuran.store');
        Route::post('/iuran/jenis', [IuranController::class, 'storeJenis'])->name('iuran.jenis.store');
        Route::get('/iuran/jenis', [JenisIuranController::class, 'index'])->name('iuran.jenis.index');
        Route::put('/iuran/jenis/{jenisIuran}', [JenisIuranController::class, 'update'])->name('iuran.jenis.update');
        Route::patch('/iuran/jenis/{jenisIuran}/status', [JenisIuranController::class, 'status'])->name('iuran.jenis.status');
        Route::delete('/iuran/jenis/{jenisIuran}', [JenisIuranController::class, 'destroy'])
            ->missing(fn (Request $request) => app(JenisIuranController::class)->missing($request))
            ->name('iuran.jenis.destroy');
        Route::get('/verifikasi', [PembayaranController::class, 'index'])->name('verifikasi');
        Route::post('/verifikasi/{pembayaran}', [PembayaranController::class, 'verify'])->name('verifikasi.update');
        Route::get('/transaksi', [RingkasanKasController::class, 'transactions'])->name('transaksi');
        Route::post('/transaksi', [TransaksiKasController::class, 'store'])->name('transaksi.store');
        Route::put('/transaksi/{transaksi}', [TransaksiKasController::class, 'update'])->name('transaksi.update');
        Route::delete('/transaksi/{transaksi}', [TransaksiKasController::class, 'destroy'])->name('transaksi.destroy');
        Route::get('/laporan', LaporanController::class)->name('laporan');
        Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');
        Route::get('/transparansi', [RingkasanKasController::class, 'transparency'])->name('transparansi');
    });

    Route::prefix('anggota')->name('anggota.')->middleware('role:anggota')->group(function (): void {
        Route::get('/dashboard', [RingkasanKasController::class, 'dashboard'])->name('dashboard');
        Route::get('/tagihan', [IuranController::class, 'index'])->name('tagihan');
        Route::post('/tagihan/{tagihan}/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
        Route::get('/laporan', LaporanController::class)->name('laporan');
        Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');
        Route::get('/transparansi', [RingkasanKasController::class, 'transparency'])->name('transparansi');
    });
});

// Preview hanya membaca fixture Blade dan tidak menyediakan aksi login/CRUD.
// Tetap memakai seluruh middleware web, termasuk session dan CSRF.
if (app()->environment('local')) {
    Route::prefix('ui-preview')
        ->name('ui-preview.')
        ->middleware('preview.local')
        ->group(function (): void {
            Route::view('/login', 'auth.login')->name('login');
            Route::view('/admin/dashboard', 'admin.dashboard-preview')->name('admin.dashboard');
            Route::view('/admin/anggota', 'admin.anggota.preview')->name('admin.anggota');
            Route::view('/admin/iuran', 'admin.iuran.preview')->name('admin.iuran');
            Route::view('/admin/verifikasi', 'admin.pembayaran.preview')->name('admin.verifikasi');
            Route::view('/admin/transaksi', 'admin.transaksi.preview')->name('admin.transaksi');
            Route::view('/admin/laporan', 'laporan.preview', ['role' => 'admin'])->name('admin.laporan');
            Route::view('/admin/transparansi', 'transparansi.preview', ['role' => 'admin'])->name('admin.transparansi');

            Route::view('/anggota/dashboard', 'anggota.dashboard-preview')->name('anggota.dashboard');
            Route::view('/anggota/tagihan', 'anggota.tagihan.preview')->name('anggota.tagihan');
            Route::view('/anggota/laporan', 'laporan.preview', ['role' => 'anggota'])->name('anggota.laporan');
            Route::view('/anggota/transparansi', 'transparansi.preview', ['role' => 'anggota'])->name('anggota.transparansi');
        });
}
