@extends('layouts.guest', ['title' => 'Akses Ditolak - KasOrg'])

@section('content')
    <main class="w-100 ko-max-w-md ko-radius-login border ko-border-slate-100 bg-white ko-p-8 text-center ko-shadow-login">
        <p class="ko-text-sm fw-bold ko-text-brand-600">KasOrg · 403</p>
        <h1 class="ko-mt-3 ko-text-2xl ko-fw-heavy ko-text-slate-900">Akses ditolak</h1>
        <p class="ko-mt-3 ko-text-sm ko-text-slate-500">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="{{ route('dashboard') }}" class="ko-mt-6 d-inline-flex ko-radius-control ko-bg-brand-600 ko-px-5 ko-py-3 ko-text-sm fw-bold text-white ko-hover-bg-brand-700">Kembali ke dashboard</a>
    </main>
@endsection
