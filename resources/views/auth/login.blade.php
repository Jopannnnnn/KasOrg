@extends('layouts.guest', ['title' => 'Masuk ke Akun - KasOrg'])

@php($isPreview = request()->routeIs('ui-preview.*'))

@section('content')
    <main class="grid w-full max-w-4xl grid-cols-1 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl md:grid-cols-2">
        <section class="relative flex flex-col justify-between overflow-hidden bg-brand-700 p-8 text-white sm:p-12">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>

            <div>
                <div class="mb-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/30 bg-white/20">
                        <i data-lucide="wallet" class="h-5 w-5 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold leading-tight">KasOrg</h1>
                        <p class="text-xs text-blue-100">Sistem Kas Organisasi</p>
                    </div>
                </div>

                <span class="mb-4 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-blue-50">Keuangan lebih tertata</span>
                <h2 class="mb-3 text-2xl font-extrabold leading-snug sm:text-3xl">Kelola kas organisasi dengan mudah dan transparan.</h2>
                <p class="text-sm leading-relaxed text-blue-100">Satu tempat untuk mencatat iuran, memantau transaksi, dan menyusun laporan keuangan organisasi.</p>
            </div>

            <div class="mt-8">
                <div class="mb-8 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-3">
                        <span class="block text-xl font-bold">42</span>
                        <span class="text-[11px] text-blue-100">Anggota aktif</span>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-3">
                        <span class="block text-xl font-bold">98%</span>
                        <span class="text-[11px] text-blue-100">Iuran tercatat</span>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-3">
                        <span class="block text-xl font-bold">24/7</span>
                        <span class="text-[11px] text-blue-100">Data tersedia</span>
                    </div>
                </div>
                <p class="text-[11px] text-blue-200">Kelompok 4 • Pemrograman Web Lanjut</p>
            </div>
        </section>

        <section class="flex flex-col justify-center p-8 sm:p-12">
            <div class="mb-8">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Selamat datang kembali</span>
                <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Masuk ke akun Anda</h2>
                <p class="mt-1 text-xs text-slate-500">Gunakan email dan kata sandi yang sudah terdaftar.</p>
            </div>

            @if ($isPreview)
                <p class="mb-4 text-xs text-slate-500" role="note">Preview lokal: formulir ini tidak melakukan autentikasi.</p>
            @endif

            <form class="space-y-5" method="POST" action="{{ route($isPreview ? 'ui-preview.login' : 'login.store') }}" @if ($isPreview) onsubmit="return false" @endif>
                @unless ($isPreview)
                    @csrf
                @endunless
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold text-slate-700">Alamat email</label>
                    <input type="email" id="email" name="email" autocomplete="email" value="{{ $isPreview ? 'bendahara@organisasi.id' : old('email') }}" placeholder="nama@organisasi.id" required
                           @if (! $isPreview && $errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-600">
                    @unless ($isPreview)
                        @error('email')
                            <p id="email-error" class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    @endunless
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-bold text-slate-700">Kata sandi</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required
                               @if (! $isPreview && $errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 pr-10 text-sm text-slate-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <button type="button" id="toggle-password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi" class="absolute right-1 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg p-2 text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-600">
                            <i data-lucide="eye-off" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                        </button>
                    </div>
                    @unless ($isPreview)
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    @endunless
                </div>

                <button type="{{ $isPreview ? 'button' : 'submit' }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-3.5 text-sm font-bold text-white shadow-md shadow-brand-600/30 transition hover:bg-brand-700">
                    Masuk ke sistem <i data-lucide="chevron-right" class="h-4 w-4"></i>
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
                icon.className = 'h-4 w-4 shrink-0';
                icon.setAttribute('aria-hidden', 'true');
                button.replaceChildren(icon);
                // Listener tetap pada tombol; hanya SVG di dalamnya yang diganti.
                window.lucide?.createIcons({ root: button });
            });
        })();
    </script>
@endpush
