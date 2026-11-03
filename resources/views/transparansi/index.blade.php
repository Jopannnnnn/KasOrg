@php $isAdmin = ($role ?? 'anggota') === 'admin'; @endphp
@extends('layouts.app', [
    'title' => 'Transparansi Kas | KasOrg', 'heading' => 'Transparansi Kas', 'role' => $role ?? 'anggota',
    'userName' => $isAdmin ? 'Jovan Wijaya' : 'Kent Rafli', 'userInitials' => $isAdmin ? 'JW' : 'KM',
])

@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <section class="flex flex-col justify-between gap-6 rounded-3xl bg-brand-600 p-6 text-white shadow-lg shadow-brand-600/20 sm:p-8 md:flex-row md:items-center">
        <div><span class="text-xs font-semibold text-blue-100">Saldo kas terkini</span><div class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">Rp 18.750.000</div><div class="mt-2 flex items-center gap-1.5 text-xs text-blue-100"><i data-lucide="clock" class="h-3.5 w-3.5"></i>Diperbarui 18 Maret 2026, 14.30 WIB</div></div>
        <div class="flex items-center gap-6 border-t border-white/20 pt-4 md:border-l md:border-t-0 md:pl-8 md:pt-0"><div><span class="text-xs font-medium text-blue-100">Pemasukan</span><div class="mt-0.5 text-lg font-extrabold text-white">+ Rp 4,27 jt</div></div><div><span class="text-xs font-medium text-blue-100">Pengeluaran</span><div class="mt-0.5 text-lg font-extrabold text-white">- Rp 2,18 jt</div></div></div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div><h3 class="text-base font-extrabold text-slate-900">Penggunaan dana</h3><p class="mb-6 text-xs text-slate-500">Alokasi pengeluaran bulan ini</p><div class="my-4 flex items-center justify-center"><div class="flex h-40 w-40 flex-col items-center justify-center rounded-full border-[20px] border-brand-600 border-l-blue-300 border-r-blue-400 border-t-blue-200"><span class="text-[11px] font-bold text-slate-400">Total</span><span class="text-base font-extrabold text-slate-800">Rp 2,18 jt</span></div></div></div><div class="space-y-3 text-xs font-semibold text-slate-700"><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span>Kegiatan</span><span class="font-bold">38%</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-400"></span>Konsumsi</span><span class="font-bold">27%</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-300"></span>Perlengkapan</span><span class="font-bold">17%</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-200"></span>Lainnya</span><span class="font-bold">18%</span></div></div></section>

        <section class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div><div class="mb-4 flex items-center justify-between"><div><h3 class="text-base font-extrabold text-slate-900">Riwayat kas organisasi</h3><p class="text-xs text-slate-500">Data hanya dapat diubah oleh bendahara</p></div></div><div class="overflow-x-auto"><table class="w-full border-collapse text-left text-xs sm:text-sm"><thead><tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Jenis</th><th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Keterangan</th><th class="px-3 py-3 text-right">Nominal</th></tr></thead><tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach ([
                    ['18 Mar 2026','Pemasukan','Iuran anggota','Iuran bulan Maret — Raka Putra','+ Rp 75.000'],
                    ['17 Mar 2026','Pengeluaran','Konsumsi','Konsumsi rapat pengurus','- Rp 425.000'],
                    ['15 Mar 2026','Pemasukan','Donasi','Donasi alumni organisasi','+ Rp 1.500.000'],
                    ['12 Mar 2026','Pengeluaran','Perlengkapan','Cetak banner kegiatan','- Rp 680.000'],
                    ['10 Mar 2026','Pemasukan','Iuran anggota','Iuran kolektif bulan Maret','+ Rp 1.125.000'],
                ] as $item)
                    @php $income = $item[1] === 'Pemasukan'; @endphp
                    <tr class="transition hover:bg-slate-50/70"><td class="px-3 py-3.5 font-medium">{{ $item[0] }}</td><td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $income ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $item[1] }}</span></td><td class="px-3 py-3.5 font-semibold">{{ $item[2] }}</td><td class="px-3 py-3.5 text-slate-500">{{ $item[3] }}</td><td class="px-3 py-3.5 text-right font-bold {{ $income ? 'text-emerald-600' : 'text-rose-600' }}">{{ $item[4] }}</td></tr>
                @endforeach
            </tbody></table></div></div>
        </section>
    </div>
</main>
@endsection
