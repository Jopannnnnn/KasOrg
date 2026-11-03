@extends('layouts.app', [
    'title' => 'Input Iuran - KasOrg',
    'heading' => 'Input Iuran',
    'role' => 'admin',
    'userName' => 'Jovan Wijaya',
    'userInitials' => 'JW',
])

@php
    $iuran = [
        ['nama' => 'Jovan Putra Wijaya', 'nim' => '25170004', 'periode' => 'Maret 2026', 'nominal' => 'Rp 75.000', 'status' => 'Lunas'],
        ['nama' => 'Juniarti Siadari', 'nim' => '25170001', 'periode' => 'Maret 2026', 'nominal' => 'Rp 75.000', 'status' => 'Lunas'],
        ['nama' => 'Kent Muhammad Rafli', 'nim' => '25170027', 'periode' => 'Maret 2026', 'nominal' => 'Rp 75.000', 'status' => 'Belum Lunas'],
        ['nama' => 'Refan Rhizqi Wintoro', 'nim' => '25170025', 'periode' => 'Maret 2026', 'nominal' => 'Rp 75.000', 'status' => 'Belum Lunas'],
    ];
@endphp

@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Input iuran anggota</h2><p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Catat pembayaran kas yang telah diterima secara tunai atau transfer.</p></div>
        <button id="catat-pembayaran-utama" type="button" class="inline-flex self-start items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-600/20 transition hover:bg-brand-700 sm:self-auto sm:text-sm"><i data-lucide="plus" class="h-4 w-4"></i>Catat pembayaran</button>
    </div>

    <div id="iuran-preview-note" class="hidden rounded-xl border border-blue-100 bg-brand-50 px-4 py-3 text-xs font-semibold text-brand-700" role="status"></div>

    <section class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="wallet" class="h-5 w-5"></i></span>
        <div><h3 class="text-sm font-extrabold text-slate-900">Alur pencatatan sederhana</h3><p class="mt-1 text-xs leading-relaxed text-slate-500">Setelah pembayaran diterima, pilih anggota dan simpan. Sistem akan menandai iuran lunas serta menambah transaksi pemasukan secara otomatis.</p></div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col items-center justify-between gap-3 border-b border-slate-100 p-4 sm:flex-row sm:p-5">
            <label class="relative w-full sm:w-80"><span class="sr-only">Cari anggota</span><i data-lucide="search" class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input id="cari-iuran" type="search" placeholder="Cari anggota yang akan dicatat..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-xs transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-600 sm:text-sm"></label>
            <div class="flex w-full items-center gap-2 sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <button id="filter-status-iuran" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50" aria-expanded="false"><i data-lucide="sliders-horizontal" class="h-3.5 w-3.5 text-slate-500"></i><span id="filter-status-iuran-label">Filter</span></button>
                    <div id="filter-status-iuran-menu" class="absolute right-0 z-20 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg"><button type="button" data-filter-iuran="semua" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Semua status</button><button type="button" data-filter-iuran="Lunas" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Lunas</button><button type="button" data-filter-iuran="Belum Lunas" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Belum Lunas</button></div>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700"><i data-lucide="calendar" class="h-3.5 w-3.5 text-slate-500"></i>Maret 2026</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs sm:text-sm">
                <thead><tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-4 py-3">Anggota</th><th class="px-4 py-3">Periode</th><th class="px-4 py-3">Nominal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach ($iuran as $row)
                    <tr class="iuran-row transition hover:bg-slate-50/80" data-search="{{ strtolower($row['nama'].' '.$row['nim']) }}" data-status="{{ $row['status'] }}">
                        <td class="px-4 py-4"><div class="font-bold text-slate-900">{{ $row['nama'] }}</div><div class="font-mono text-[11px] text-slate-400">{{ $row['nim'] }}</div></td>
                        <td class="px-4 py-4 font-medium text-slate-600">{{ $row['periode'] }}</td><td class="px-4 py-4 font-bold text-slate-800">{{ $row['nominal'] }}</td>
                        <td class="px-4 py-4"><span class="status-iuran rounded-full px-2.5 py-1 text-xs font-bold {{ $row['status'] === 'Lunas' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">{{ $row['status'] }}</span></td>
                        <td class="px-4 py-4 text-right">
                            @if ($row['status'] === 'Lunas')
                                <button type="button" class="detail-iuran inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-700"><i data-lucide="eye" class="h-3.5 w-3.5"></i>Detail</button>
                            @else
                                <button type="button" data-name="{{ $row['nama'] }}" class="catat-iuran inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-brand-700">Catat iuran</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p id="iuran-kosong" class="hidden py-10 text-center text-sm text-slate-500">Tidak ada data iuran yang sesuai.</p>
        </div>
    </section>
</main>

<div id="modal-iuran" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal-iuran">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><div><h2 id="judul-modal-iuran" class="text-xl font-bold">Catat pembayaran iuran</h2><p id="nama-pembayar" class="mt-1 text-xs text-slate-500">Pilih anggota</p></div><button id="tutup-modal-iuran" type="button" class="rounded-lg border border-slate-200 p-2" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button></div>
        <form id="form-iuran" class="space-y-4 p-6">
            <label class="block text-sm font-semibold">Metode pembayaran<select id="metode-iuran" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option>Tunai</option><option>Transfer manual</option></select></label>
            <label class="block text-sm font-semibold">Tanggal diterima<input type="date" value="2026-03-18" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></label>
            <label class="block text-sm font-semibold">Nominal<input value="75000" inputmode="numeric" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></label>
            <p class="rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-700">Preview frontend saja. Tidak ada saldo, tagihan, atau transaksi database yang berubah.</p>
            <div class="flex justify-end gap-3"><button id="batal-modal-iuran" type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white">Terapkan preview</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('modal-iuran');
    const note = document.getElementById('iuran-preview-note');
    const search = document.getElementById('cari-iuran');
    const filter = document.getElementById('filter-status-iuran');
    const filterMenu = document.getElementById('filter-status-iuran-menu');
    let activeFilter = 'semua';
    let activeRow = null;
    const open = (row = null, name = 'Pilih anggota') => { activeRow = row; document.getElementById('nama-pembayar').textContent = name; modal.classList.replace('hidden', 'flex'); };
    const close = () => modal.classList.replace('flex', 'hidden');
    const render = () => { let count = 0; document.querySelectorAll('.iuran-row').forEach(row => { const show = row.dataset.search.includes(search.value.toLowerCase()) && (activeFilter === 'semua' || row.dataset.status === activeFilter); row.classList.toggle('hidden', !show); if (show) count++; }); document.getElementById('iuran-kosong').classList.toggle('hidden', count > 0); };
    document.getElementById('catat-pembayaran-utama').addEventListener('click', () => open());
    document.querySelectorAll('.catat-iuran').forEach(button => button.addEventListener('click', () => open(button.closest('tr'), button.dataset.name)));
    document.querySelectorAll('.detail-iuran').forEach(button => button.addEventListener('click', () => { note.textContent = 'Detail ini menggunakan data contoh dan belum terhubung ke transaksi.'; note.classList.remove('hidden'); }));
    [document.getElementById('tutup-modal-iuran'), document.getElementById('batal-modal-iuran')].forEach(button => button.addEventListener('click', close));
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    search.addEventListener('input', render);
    filter.addEventListener('click', () => { const willOpen = filterMenu.classList.contains('hidden'); filterMenu.classList.toggle('hidden', !willOpen); filter.setAttribute('aria-expanded', String(willOpen)); });
    document.querySelectorAll('[data-filter-iuran]').forEach(option => option.addEventListener('click', () => { activeFilter = option.dataset.filterIuran; document.getElementById('filter-status-iuran-label').textContent = activeFilter === 'semua' ? 'Filter' : activeFilter; filterMenu.classList.add('hidden'); filter.setAttribute('aria-expanded', 'false'); render(); }));
    document.getElementById('form-iuran').addEventListener('submit', event => { event.preventDefault(); if (activeRow) { const badge = activeRow.querySelector('.status-iuran'); badge.textContent = 'Preview Dicatat'; badge.className = 'status-iuran rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-brand-600'; } note.textContent = 'Pencatatan diterapkan pada tampilan preview saja; tidak ada data atau saldo yang disimpan.'; note.classList.remove('hidden'); close(); });
})();
</script>
@endpush
