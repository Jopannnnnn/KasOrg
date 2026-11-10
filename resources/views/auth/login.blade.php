@extends('layouts.guest', ['title' => 'Masuk ke Akun - KasOrg'])

@php($isPreview = request()->routeIs('ui-preview.*'))

@section('body-class', 'ko-login-page')
@push('styles')
    <link href="{{ asset('css/kasorg-login.css') }}" rel="stylesheet">
@endpush

@section('content')
    <main class="row g-0 w-100 ko-login-card ko-radius-login overflow-hidden border bg-white ko-shadow-login">
        <section class="col-12 col-md-6 position-relative d-flex flex-column justify-content-between overflow-hidden ko-bg-brand-700 ko-p-8 text-white ko-sm-p-12">
            <div class="pe-none position-absolute ko--right-16 ko--top-16 ko-h-64 ko-w-64 rounded-pill ko-bg-white-10 ko-blur"></div>

            <div>
                <div class="ko-mb-10 d-flex align-items-center ko-gap-3">
                    <div class="d-flex ko-h-10 ko-w-10 align-items-center justify-content-center ko-radius-control border ko-border-white-30 ko-bg-white-20">
                        <i data-lucide="wallet" class="ko-h-5 ko-w-5 text-white"></i>
                    </div>
                    <div>
                        <h1 class="ko-text-lg fw-bold ko-leading-tight">KasOrg</h1>
                        <p class="ko-text-xs ko-text-blue-100">Sistem Kas Organisasi</p>
                    </div>
                </div>

                <span class="ko-mb-4 d-inline-block rounded-pill ko-bg-white-20 ko-px-3 ko-py-1 ko-text-xs fw-semibold ko-text-blue-50">Keuangan lebih tertata</span>
                <h2 class="ko-mb-3 ko-text-2xl ko-fw-heavy ko-leading-snug ko-sm-text-3xl">Kelola kas organisasi dengan mudah dan transparan.</h2>
                <p class="ko-text-sm ko-leading-relaxed ko-text-blue-100">Satu tempat untuk mencatat iuran, memantau transaksi, dan menyusun laporan keuangan organisasi.</p>
            </div>

            <div class="ko-mt-8">
                <div class="ko-mb-8 ko-grid ko-grid-cols-3 ko-gap-3">
                    <div class="ko-radius-card border ko-border-white-15 ko-bg-white-10 ko-p-3">
                        <span class="d-block ko-text-xl fw-bold">42</span>
                        <span class="ko-text--11px- ko-text-blue-100">Anggota aktif</span>
                    </div>
                    <div class="ko-radius-card border ko-border-white-15 ko-bg-white-10 ko-p-3">
                        <span class="d-block ko-text-xl fw-bold">98%</span>
                        <span class="ko-text--11px- ko-text-blue-100">Iuran tercatat</span>
                    </div>
                    <div class="ko-radius-card border ko-border-white-15 ko-bg-white-10 ko-p-3">
                        <span class="d-block ko-text-xl fw-bold">24/7</span>
                        <span class="ko-text--11px- ko-text-blue-100">Data tersedia</span>
                    </div>
                </div>
                <p class="ko-text--11px- ko-text-blue-200">Kelompok 4 • Pemrograman Web Lanjut</p>
            </div>
        </section>

        <section class="col-12 col-md-6 d-flex flex-column justify-content-center ko-p-8 ko-sm-p-12">
            <div class="ko-mb-8">
                <span class="ko-text-xs fw-bold text-uppercase ko-tracking-wide ko-text-brand-600">Selamat datang kembali</span>
                <h2 class="ko-mt-1 ko-text-2xl ko-fw-heavy ko-text-slate-900">Masuk ke akun Anda</h2>
                <p class="ko-mt-1 ko-text-xs ko-text-slate-500">Gunakan email dan kata sandi yang sudah terdaftar.</p>
            </div>

            @if ($isPreview)
                <p class="ko-mb-4 ko-text-xs ko-text-slate-500" role="note">Preview lokal: formulir ini tidak melakukan autentikasi.</p>
            @endif

            <form class="ko-space-y-5" method="POST" action="{{ route($isPreview ? 'ui-preview.login' : 'login.store') }}" @if ($isPreview) onsubmit="return false" @endif>
                @unless ($isPreview)
                    @csrf
                @endunless
                <div>
                    <label for="email" class="form-label ko-mb-1-5 d-block ko-text-xs fw-bold ko-text-slate-700">Alamat email</label>
                    <input type="email" id="email" name="email" autocomplete="email" value="{{ $isPreview ? 'bendahara@organisasi.id' : old('email') }}" placeholder="nama@organisasi.id" required
                           @if (! $isPreview && $errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                           class="form-control w-100 ko-radius-control border ko-border-slate-200 ko-px-4 ko-py-3 ko-text-sm ko-text-slate-800 ko-placeholder-slate-400 ko-transition ko-focus-border-transparent ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-600">
                    @unless ($isPreview)
                        @error('email')
                            <p id="email-error" class="alert alert-danger ko-mt-1-5 ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    @endunless
                </div>

                <div>
                    <label for="password" class="form-label ko-mb-1-5 d-block ko-text-xs fw-bold ko-text-slate-700">Kata sandi</label>
                    <div class="position-relative">
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required
                               @if (! $isPreview && $errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                               class="form-control w-100 ko-radius-control border ko-border-slate-200 ko-px-4 ko-py-3 ko-pr-10 ko-text-sm ko-text-slate-800 ko-transition ko-focus-border-transparent ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-600">
                        <button type="button" id="toggle-password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi" class="btn ko-button position-absolute ko-right-1 top-50 d-inline-flex ko-h-8 ko-w-8 translate-middle-y align-items-center justify-content-center ko-radius-small ko-p-2 ko-text-slate-400 ko-hover-text-slate-600 ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-600">
                            <i data-lucide="eye-off" class="ko-h-4 ko-w-4 flex-shrink-0" aria-hidden="true"></i>
                        </button>
                    </div>
                    @unless ($isPreview)
                        @error('password')
                            <p id="password-error" class="alert alert-danger ko-mt-1-5 ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    @endunless
                </div>

                <button type="{{ $isPreview ? 'button' : 'submit' }}" class="btn ko-button d-flex w-100 align-items-center justify-content-center ko-gap-2 ko-radius-control ko-bg-brand-600 ko-py-3-5 ko-text-sm fw-bold text-white shadow ko-shadow-brand-600-30 ko-transition ko-hover-bg-brand-700">
                    Masuk ke sistem <i data-lucide="chevron-right" class="ko-h-4 ko-w-4"></i>
                </button>
            </form>

        </section>
    </main>
@endsection

@push('scripts')
    <script>
        (() => {
            const button = document.getElementById('toggle-password');
            const input = document.getElementById('password');
            if (!button || !input) return;

            button.addEventListener('click', () => {
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(visible));
                button.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');

                const icon = document.createElement('i');
                icon.dataset.lucide = visible ? 'eye' : 'eye-off';
                icon.className = 'ko-h-4 ko-w-4 flex-shrink-0';
                icon.setAttribute('aria-hidden', 'true');
                button.replaceChildren(icon);
                // Listener tetap pada tombol; hanya SVG di dalamnya yang diganti.
                window.lucide?.createIcons({ root: button });
            });
        })();
    </script>
@endpush
