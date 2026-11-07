@extends('layouts.guest', ['title' => 'Akses Ditolak - KasOrg'])

@section('content')
    <main class="w-full max-w-md rounded-3xl border border-slate-100 bg-white p-8 text-center shadow-2xl">
        <p class="text-sm font-bold text-brand-600">KasOrg · 403</p>
        <h1 class="mt-3 text-2xl font-extrabold text-slate-900">Akses ditolak</h1>
        <p class="mt-3 text-sm text-slate-500">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-flex rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white hover:bg-brand-700">Kembali ke dashboard</a>
    </main>
@endsection
