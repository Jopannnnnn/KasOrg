@extends('layouts.app', [
    'title' => 'Dashboard Admin - KasOrg',
    'heading' => 'Dashboard',
    'role' => 'admin',
])


@section('content')
    <main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Selamat datang, {{ auth()->user()->name }}!</h2>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Berikut ringkasan kondisi keuangan organisasi pada periode terpilih.</p>
            </div>
            @include('keuangan.periode-filter')
        </div>
        <x-keuangan-feedback />

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan keuangan">
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Total saldo kas</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="wallet" class="h-4 w-4"></i></span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['closing']) }}</p>
                <p class="mt-2 text-xs font-medium text-slate-400">Berdasarkan data database</p>
            </article>
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Pemasukan periode ini</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="h-4 w-4"></i></span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['income']) }}</p>
                <p class="mt-2 text-xs font-medium text-slate-400">Berdasarkan data database</p>
            </article>
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Pengeluaran periode ini</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['expense']) }}</p>
                <p class="mt-2 text-xs font-medium text-slate-400">Berdasarkan data database</p>
            </article>
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Total anggota aktif</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-50 text-purple-600"><i data-lucide="users" class="h-4 w-4"></i></span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ $activeMembers }} anggota</p>
                <p class="mt-2 text-xs font-medium text-slate-400">Berdasarkan data database</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Arus kas organisasi</h3>
                        <p class="text-xs text-slate-500">Perbandingan pemasukan dan pengeluaran</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-bold">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span>Pemasukan</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-blue-100"></span>Pengeluaran</span>
                    </div>
                </div>
                @include('keuangan.arus-kas')
            </article>

            <article class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Perlu perhatian</h3>
                    <p class="mb-5 text-xs text-slate-500">Tagihan berdasarkan periode terpilih</p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-sm font-extrabold text-brand-600">{{ $billStats['unpaid'] }}</span><span class="text-xs font-bold text-slate-700">Tagihan belum lunas</span></div>
                            <i data-lucide="clock" class="h-4 w-4 text-slate-400"></i>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-sm font-extrabold text-brand-600">{{ $billStats['waiting'] }}</span><a href="{{ route('admin.verifikasi') }}" class="text-xs font-bold text-slate-700 hover:underline">Pembayaran menunggu</a></div>
                            <i data-lucide="file-check" class="h-4 w-4 text-slate-400"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route((request()->routeIs('ui-preview.*') ? 'ui-preview.' : '').'admin.iuran') }}" class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-50 py-3 text-xs font-bold text-brand-700 transition hover:bg-brand-100">
                    Kelola Tagihan Anggota <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                </a>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Transaksi terbaru</h3>
                    <p class="text-xs text-slate-500">Aktivitas kas paling baru</p>
                </div>
                <a href="{{ route('admin.transparansi', ['periode_id' => $scope['selected']]) }}" class="flex items-center gap-1 text-xs font-bold text-brand-600 hover:underline">Lihat semua <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Jenis</th><th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Keterangan</th><th class="px-3 py-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-3 py-3.5 font-medium">{{ $transaction['date'] }}</td>
                                <td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $transaction['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $transaction['type'] }}</span></td>
                                <td class="px-3 py-3.5 font-semibold">{{ $transaction['category'] }}</td>
                                <td class="px-3 py-3.5 text-slate-500">{{ $transaction['description'] }}</td>
                                <td class="px-3 py-3.5 text-right font-bold {{ $transaction['positive'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction['amount'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-sm text-slate-500">Belum ada transaksi kas sah pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
