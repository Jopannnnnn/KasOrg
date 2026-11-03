@extends('layouts.app', [
    'title' => 'Dashboard Anggota - KasOrg',
    'heading' => 'Dashboard',
    'role' => 'anggota',
    'userName' => 'Kent Rafli',
    'userInitials' => 'KM',
])

@php
    // Data statis khusus preview UI. Belum berasal dari database.
    $bars = [
        ['month' => 'Okt', 'income' => 50, 'expense' => 30],
        ['month' => 'Nov', 'income' => 68, 'expense' => 42],
        ['month' => 'Des', 'income' => 62, 'expense' => 32],
        ['month' => 'Jan', 'income' => 82, 'expense' => 50],
        ['month' => 'Feb', 'income' => 74, 'expense' => 46],
        ['month' => 'Mar', 'income' => 95, 'expense' => 58],
    ];

    $transactions = [
        ['date' => '18 Mar 2026', 'type' => 'Pemasukan', 'category' => 'Iuran anggota', 'description' => 'Iuran bulan Maret — Raka Putra', 'amount' => '+ Rp 75.000', 'positive' => true],
        ['date' => '17 Mar 2026', 'type' => 'Pengeluaran', 'category' => 'Konsumsi', 'description' => 'Konsumsi rapat pengurus', 'amount' => '- Rp 425.000', 'positive' => false],
    ];
@endphp

@section('content')
    <main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Selamat datang, Kent!</h2>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Berikut ringkasan kondisi keuangan organisasi bulan ini.</p>
            </div>
            <div class="flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm sm:self-auto">
                <i data-lucide="calendar" class="h-4 w-4 text-slate-500"></i><span>Maret 2026</span><i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-400"></i>
            </div>
        </div>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan keuangan anggota">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Total saldo kas</span><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="wallet" class="h-4 w-4"></i></span></div>
                <p class="text-2xl font-extrabold text-slate-900">Rp 18.750.000</p>
                <p class="mt-2 flex items-center gap-1.5 text-xs font-bold text-emerald-600"><span>+8,4%</span><span class="font-medium text-slate-400">dari bulan lalu</span></p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Pemasukan bulan ini</span><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="h-4 w-4"></i></span></div>
                <p class="text-2xl font-extrabold text-slate-900">Rp 4.275.000</p>
                <p class="mt-2 flex items-center gap-1.5 text-xs font-bold text-emerald-600"><span>+12,5%</span><span class="font-medium text-slate-400">dari bulan lalu</span></p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Pengeluaran bulan ini</span><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></div>
                <p class="text-2xl font-extrabold text-slate-900">Rp 2.180.000</p>
                <p class="mt-2 flex items-center gap-1.5 text-xs font-bold text-emerald-600"><span>-3,2%</span><span class="font-medium text-slate-400">dari bulan lalu</span></p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between"><span class="text-xs font-semibold text-slate-500">Status iuran Maret</span><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i data-lucide="clock" class="h-4 w-4"></i></span></div>
                <p class="text-2xl font-extrabold text-slate-900">Belum lunas</p>
                <p class="mt-2 text-xs font-semibold text-amber-600">Tagihan: Rp 75.000</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="mb-6 flex items-center justify-between">
                    <div><h3 class="text-base font-extrabold text-slate-900">Arus kas organisasi</h3><p class="text-xs text-slate-500">Perbandingan pemasukan dan pengeluaran</p></div>
                    <div class="flex items-center gap-4 text-xs font-bold"><span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span>Pemasukan</span><span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-blue-100"></span>Pengeluaran</span></div>
                </div>
                <div class="flex h-56 items-end justify-between gap-4 border-b border-slate-100 px-2 pt-4">
                    @foreach ($bars as $bar)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                            <div class="flex h-full w-full max-w-[42px] items-end justify-center gap-1.5">
                                <div class="w-3.5 rounded-t-md bg-brand-600" style="height: {{ $bar['income'] }}%"></div>
                                <div class="w-3.5 rounded-t-md bg-blue-100" style="height: {{ $bar['expense'] }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">{{ $bar['month'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="flex flex-col justify-between rounded-2xl border border-amber-200/60 bg-amber-50/50 p-6 shadow-sm">
                <div>
                    <span class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><i data-lucide="clock" class="h-5 w-5"></i></span>
                    <h3 class="text-base font-extrabold text-slate-900">Belum lunas</h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-600">Iuran Maret 2026 sebesar <strong>Rp 75.000</strong> belum tercatat lunas.</p>
                    <div class="mt-4 rounded-xl border border-amber-200/50 bg-white/80 p-3 text-xs text-slate-600">Hubungi bendahara untuk konfirmasi pembayaran via transfer atau tunai.</div>
                </div>
                <p class="mt-6 border-t border-amber-200/50 pt-4 text-[11px] font-semibold text-amber-800">Pembaruan terbaru bulan ini</p>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div><h3 class="text-base font-extrabold text-slate-900">Transaksi terbaru</h3><p class="text-xs text-slate-500">Aktivitas kas paling baru</p></div>
                <a href="{{ route('ui-preview.anggota.transparansi') }}" class="flex items-center gap-1 text-xs font-bold text-brand-600 hover:underline">Lihat semua <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs sm:text-sm">
                    <thead><tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Jenis</th><th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Keterangan</th><th class="px-3 py-3 text-right">Nominal</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-3 py-3.5 font-medium">{{ $transaction['date'] }}</td>
                                <td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $transaction['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $transaction['type'] }}</span></td>
                                <td class="px-3 py-3.5 font-semibold">{{ $transaction['category'] }}</td>
                                <td class="px-3 py-3.5 text-slate-500">{{ $transaction['description'] }}</td>
                                <td class="px-3 py-3.5 text-right font-bold {{ $transaction['positive'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction['amount'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
