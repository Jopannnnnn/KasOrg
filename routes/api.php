<?php

use App\Http\Controllers\Api\KasController;
use App\Http\Middleware\EnsureApiJson;
use Illuminate\Support\Facades\Route;

// API session resmi Laravel: web tetap aktif (cookie, session, CSRF), tanpa Sanctum/token baru.
Route::middleware([EnsureApiJson::class, 'web', 'auth:web', 'active', 'role:admin,anggota'])
    ->name('api.')->group(function (): void {
        Route::get('/kategori-transaksi', [KasController::class, 'categories'])->name('kategori-transaksi');
        Route::get('/transparansi/ringkasan', [KasController::class, 'summary'])->name('transparansi.ringkasan');
    });
