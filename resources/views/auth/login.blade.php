@extends('layouts.guest', ['title' => 'Masuk ke Akun - KasOrg'])

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

            {{-- Preview visual saja: submit sengaja dihentikan sampai autentikasi Laravel diimplementasikan. --}}
            <form class="space-y-5" onsubmit="return false">
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold text-slate-700">Alamat email</label>
                    <input type="email" id="email" name="email" autocomplete="email" value="bendahara@organisasi.id" required
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-bold text-slate-700">Kata sandi</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 pr-10 text-sm text-slate-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <button type="button" id="toggle-password" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-3.5 text-sm font-bold text-white shadow-md shadow-brand-600/30 transition hover:bg-brand-700">
                    Masuk ke sistem <i data-lucide="chevron-right" class="h-4 w-4"></i>
                </button>
            </form>

        </section>
    </main>
@endsection

@push('scripts')
    <script>
        document.getElementById('toggle-password')?.addEventListener('click', (event) => {
            const input = document.getElementById('password');
            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            event.currentTarget.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    </script>
@endpush
