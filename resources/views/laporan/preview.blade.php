{{-- Fixture UI lokal; bukan laporan dari database. --}}
@php
    $isAdmin = ($role ?? 'anggota') === 'admin';
@endphp
@extends('layouts.app', [
    'title' => 'Laporan Keuangan | KasOrg', 'heading' => 'Laporan', 'role' => $role ?? 'anggota',
    'userName' => $isAdmin ? 'Jovan Wijaya' : 'Kent Rafli', 'userInitials' => $isAdmin ? 'JW' : 'KM',
])

@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Laporan keuangan</h2><p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Rekapitulasi kondisi keuangan organisasi secara lengkap dan transparan.</p></div>
        <div class="flex items-center gap-3 self-start sm:self-auto">
            <div class="relative"><button id="periodeLaporan" type="button" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm" aria-expanded="false"><i data-lucide="calendar" class="h-4 w-4 text-slate-500"></i><span id="periodeLaporanLabel">Maret 2026</span></button><div id="periodeLaporanMenu" class="absolute right-0 z-20 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg"><button type="button" data-periode="mar" data-label="Maret 2026" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Maret 2026</button><button type="button" data-periode="feb" data-label="Februari 2026" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Februari 2026</button><button type="button" data-periode="jan" data-label="Januari 2026" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Januari 2026</button></div></div>
            <button id="unduhLaporan" type="button" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-brand-600/20 transition hover:bg-brand-700 sm:text-sm"><i data-lucide="download" class="h-4 w-4"></i> Unduh PDF</button>
        </div>
    </div>
    <p id="pesanLaporan" class="hidden rounded-xl bg-amber-50 p-4 text-sm text-amber-800" role="status"></p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Saldo awal</span><div data-stat="awal" class="mt-1 text-2xl font-extrabold text-slate-900">Rp 16.655.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600"><i data-lucide="wallet-cards" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Pemasukan</span><div data-stat="masuk" class="mt-1 text-2xl font-extrabold text-slate-900">Rp 4.275.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Pengeluaran</span><div data-stat="keluar" class="mt-1 text-2xl font-extrabold text-slate-900">Rp 2.180.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Saldo akhir</span><div data-stat="akhir" class="mt-1 text-2xl font-extrabold text-slate-900">Rp 18.750.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="trending-up" class="h-5 w-5"></i></div></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2"><h3 class="text-base font-extrabold text-slate-900">Tren saldo kas</h3><p class="mb-6 text-xs text-slate-500">Pergerakan saldo enam bulan terakhir</p><div class="relative flex h-60 flex-col justify-end"><svg class="h-44 w-full overflow-visible" viewBox="0 0 500 150" role="img" aria-label="Grafik tren saldo kas meningkat"><polyline fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" points="20,130 100,105 200,90 300,75 400,60 480,25"/><g fill="#fff" stroke="#2563eb" stroke-width="3"><circle cx="20" cy="130" r="5"/><circle cx="100" cy="105" r="5"/><circle cx="200" cy="90" r="5"/><circle cx="300" cy="75" r="5"/><circle cx="400" cy="60" r="5"/><circle cx="480" cy="25" r="5"/></g></svg><div class="flex justify-between border-t border-slate-100 pt-4 text-xs font-semibold text-slate-400"><span>Okt</span><span>Nov</span><span>Des</span><span>Jan</span><span>Feb</span><span>Mar</span></div></div></section>
        <section class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div><h3 class="text-base font-extrabold text-slate-900">Alokasi pengeluaran</h3><p class="mb-6 text-xs text-slate-500">Berdasarkan kategori bulan ini</p><div class="my-4 flex items-center justify-center"><div class="flex h-36 w-36 flex-col items-center justify-center rounded-full border-[18px] border-brand-600 border-l-blue-200 border-r-blue-400 border-t-blue-300"><span class="text-[11px] font-bold text-slate-400">Total</span><span data-stat="donut" class="text-sm font-extrabold text-slate-800">Rp 2,18 jt</span></div></div></div><div class="space-y-2.5 text-xs font-semibold text-slate-700"><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span>Kegiatan</span><span class="font-bold">38%</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-400"></span>Konsumsi</span><span class="font-bold">27%</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-300"></span>Perlengkapan</span><span class="font-bold">17%</span></div><div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-200"></span>Lainnya</span><span class="font-bold">18%</span></div></div></section>
    </div>
</main>
@endsection

@push('scripts')
<script>
(() => {
    // Angka periode hanya data contoh lokal, bukan hasil query database.
    const data={mar:{awal:16655000,masuk:4275000,keluar:2180000,akhir:18750000},feb:{awal:15350000,masuk:2850000,keluar:1545000,akhir:16655000},jan:{awal:14200000,masuk:2400000,keluar:1250000,akhir:15350000}};
    const format=n=>`Rp ${new Intl.NumberFormat('id-ID').format(n)}`;
    const periodButton=document.getElementById('periodeLaporan'),periodMenu=document.getElementById('periodeLaporanMenu');
    periodButton.addEventListener('click',()=>{const willOpen=periodMenu.classList.contains('hidden');periodMenu.classList.toggle('hidden',!willOpen);periodButton.setAttribute('aria-expanded',String(willOpen));});
    document.querySelectorAll('[data-periode]').forEach(option=>option.addEventListener('click',()=>{const d=data[option.dataset.periode];Object.entries(d).forEach(([key,val])=>{const el=document.querySelector(`[data-stat="${key}"]`);if(el)el.textContent=format(val);});document.querySelector('[data-stat="donut"]').textContent=`Rp ${(d.keluar/1000000).toLocaleString('id-ID',{maximumFractionDigits:2})} jt`;document.getElementById('periodeLaporanLabel').textContent=option.dataset.label;periodMenu.classList.add('hidden');periodButton.setAttribute('aria-expanded','false');}));
    document.getElementById('unduhLaporan').addEventListener('click',()=>{const p=document.getElementById('pesanLaporan');p.textContent='Unduh PDF belum terhubung ke backend. Tidak ada file yang dibuat pada mode preview.';p.classList.remove('hidden');});
})();
</script>
@endpush
