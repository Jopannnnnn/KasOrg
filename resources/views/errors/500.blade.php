@extends('layouts.guest', ['title' => 'Gangguan Sementara - KasOrg'])

@section('content')
    <main class="w-100 ko-max-w-md ko-radius-login border ko-border-slate-100 bg-white ko-p-6 text-center ko-shadow-login ko-sm-p-8" aria-labelledby="error-title">
        <p class="ko-text-sm fw-bold ko-text-brand-600">KasOrg · 500</p>
        <h1 id="error-title" class="ko-mt-3 ko-text-2xl ko-fw-heavy ko-text-slate-900">Terjadi gangguan sementara</h1>
        <p class="ko-mt-3 ko-text-sm ko-text-slate-500">Maaf, halaman belum dapat ditampilkan. Silakan coba lagi beberapa saat atau kembali ke dashboard. Jika kendala berlanjut, hubungi Admin KasOrg.</p>
        <a href="{{ route('dashboard') }}" class="ko-mt-6 d-inline-flex align-items-center justify-content-center ko-radius-control ko-bg-brand-600 ko-px-5 ko-py-3 ko-text-sm fw-bold text-white ko-hover-bg-brand-700 ko-focus-visible-outline ko-focus-visible-outline-2 ko-focus-visible-outline-offset-2 ko-focus-visible-outline-brand-600">Kembali ke dashboard</a>
    </main>
@endsection
