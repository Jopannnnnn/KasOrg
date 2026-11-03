@extends('layouts.app', [
    'title' => 'Transaksi Kas | KasOrg', 'heading' => 'Transaksi Kas', 'role' => 'admin',
    'userName' => 'Jovan Wijaya', 'userInitials' => 'JW',
])

@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Transaksi kas</h2><p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Catat dan kelola seluruh pemasukan serta pengeluaran organisasi.</p></div>
        <button id="tambahTransaksi" type="button" class="inline-flex self-start items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-600/20 transition hover:bg-brand-700 sm:self-auto sm:text-sm"><i data-lucide="plus" class="h-4 w-4"></i> Tambah transaksi</button>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Pemasukan periode ini</span><div class="mt-1 text-2xl font-extrabold text-slate-900">Rp 4.275.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Pengeluaran periode ini</span><div class="mt-1 text-2xl font-extrabold text-slate-900">Rp 2.180.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Selisih arus kas</span><div class="mt-1 text-2xl font-extrabold text-slate-900">+ Rp 2.095.000</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="trending-up" class="h-5 w-5"></i></div></div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col items-center justify-between gap-3 border-b border-slate-100 p-4 sm:flex-row sm:p-5">
            <label class="relative w-full sm:w-80"><span class="sr-only">Cari transaksi</span><i data-lucide="search" class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input id="cariTransaksi" placeholder="Cari transaksi atau keterangan..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-xs transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-600 sm:text-sm"></label>
            <div class="flex w-full items-center gap-2 sm:w-auto"><div class="relative flex-1 sm:flex-none"><button id="filterTransaksi" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50" aria-expanded="false"><i data-lucide="sliders-horizontal" class="h-3.5 w-3.5 text-slate-500"></i><span id="filterTransaksiLabel">Filter</span></button><div id="filterTransaksiMenu" class="absolute right-0 z-20 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg"><button type="button" data-filter-transaksi="semua" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Semua jenis</button><button type="button" data-filter-transaksi="Pemasukan" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Pemasukan</button><button type="button" data-filter-transaksi="Pengeluaran" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Pengeluaran</button></div></div><div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700"><i data-lucide="calendar" class="h-3.5 w-3.5 text-slate-500"></i>Maret 2026</div></div>
        </div>
        <div class="overflow-x-auto"><table class="w-full border-collapse text-left text-xs sm:text-sm"><thead><tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Keterangan</th><th class="px-4 py-3 text-right">Nominal</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead><tbody id="daftarTransaksi" class="divide-y divide-slate-100 text-slate-700"></tbody></table></div>
        <p id="transaksiKosong" class="hidden py-10 text-center text-sm text-slate-500">Tidak ada transaksi yang cocok.</p>
        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 p-4 text-xs text-slate-500 sm:flex-row"><span>Menampilkan 1–5 dari 42 data</span><div class="inline-flex items-center gap-1"><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button><button class="rounded-lg bg-brand-600 px-3 py-1.5 font-bold text-white">1</button><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">2</button><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">Berikutnya</button></div></div>
    </section>
</main>

<div id="modalTransaksi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="judulTransaksi">
    <form id="formTransaksi" class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><h2 id="judulTransaksi" class="text-xl font-bold">Tambah transaksi</h2><button id="tutupTransaksi" type="button" class="rounded-lg border border-slate-200 p-2" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button></div>
        <div class="space-y-4 p-6">
            <p class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800">Form preview: data tidak disimpan ke server atau database.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><label class="text-sm font-semibold">Jenis<select id="jenisTransaksi" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal"><option>Pemasukan</option><option>Pengeluaran</option></select></label><label class="text-sm font-semibold">Tanggal<input id="tanggalTransaksi" type="date" value="2026-03-19" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal"></label></div>
            <label class="block text-sm font-semibold">Kategori<input id="kategoriTransaksi" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" placeholder="Contoh: Iuran anggota"></label>
            <label class="block text-sm font-semibold">Nominal<input id="nominalTransaksi" type="number" min="1" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" placeholder="50000"></label>
            <label class="block text-sm font-semibold">Keterangan<textarea id="keteranganTransaksi" rows="3" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" placeholder="Keterangan transaksi"></textarea></label>
        </div>
        <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4"><button id="batalTransaksi" type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 font-semibold">Batal</button><button type="submit" class="rounded-xl bg-brand-700 px-5 py-2.5 font-semibold text-white">Simpan preview</button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const rows = [
        {id:1,tgl:'18 Mar 2026',jenis:'Pemasukan',kat:'Iuran anggota',ket:'Iuran bulan Maret — Raka Putra',nilai:75000},
        {id:2,tgl:'17 Mar 2026',jenis:'Pengeluaran',kat:'Konsumsi',ket:'Konsumsi rapat pengurus',nilai:425000},
        {id:3,tgl:'15 Mar 2026',jenis:'Pemasukan',kat:'Donasi',ket:'Donasi alumni organisasi',nilai:1500000},
        {id:4,tgl:'12 Mar 2026',jenis:'Pengeluaran',kat:'Perlengkapan',ket:'Cetak banner kegiatan',nilai:680000},
        {id:5,tgl:'10 Mar 2026',jenis:'Pemasukan',kat:'Iuran anggota',ket:'Iuran kolektif bulan Maret',nilai:1125000}
    ];
    const $=id=>document.getElementById(id), rupiah=n=>new Intl.NumberFormat('id-ID').format(n); let editing=null, activeFilter='semua';
    function render(){const q=$('cariTransaksi').value.toLowerCase(); const filtered=rows.filter(r=>(activeFilter==='semua'||r.jenis===activeFilter)&&`${r.tgl} ${r.jenis} ${r.kat} ${r.ket}`.toLowerCase().includes(q)); $('daftarTransaksi').innerHTML=filtered.map(r=>{const masuk=r.jenis==='Pemasukan', color=masuk?'text-emerald-600':'text-rose-600', bg=masuk?'bg-emerald-50':'bg-rose-50'; return `<tr class="transition hover:bg-slate-50/80"><td class="px-4 py-4 font-medium text-slate-600">${r.tgl}</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold ${bg} ${color}">${r.jenis}</span></td><td class="px-4 py-4 font-semibold text-slate-900">${r.kat}</td><td class="px-4 py-4 text-slate-500">${r.ket}</td><td class="px-4 py-4 text-right font-bold ${color}">${masuk?'+':'−'} Rp ${rupiah(r.nilai)}</td><td class="px-4 py-4 text-right"><button data-edit="${r.id}" class="mr-3 text-slate-400 hover:text-slate-600" aria-label="Edit"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button><button data-delete="${r.id}" class="text-slate-400 hover:text-rose-600" aria-label="Hapus"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button></td></tr>`}).join(''); $('transaksiKosong').classList.toggle('hidden',filtered.length>0); document.querySelectorAll('[data-edit]').forEach(b=>b.addEventListener('click',()=>open(Number(b.dataset.edit)))); document.querySelectorAll('[data-delete]').forEach(b=>b.addEventListener('click',()=>alert('Preview saja: transaksi tidak dihapus.'))); if(window.lucide)lucide.createIcons();}
    function open(id=null){editing=id; const r=rows.find(x=>x.id===id); $('judulTransaksi').textContent=r?'Edit transaksi':'Tambah transaksi'; $('jenisTransaksi').value=r?.jenis||'Pemasukan'; $('kategoriTransaksi').value=r?.kat||''; $('nominalTransaksi').value=r?.nilai||''; $('keteranganTransaksi').value=r?.ket||''; $('modalTransaksi').classList.replace('hidden','flex'); document.body.style.overflow='hidden';}
    function close(){ $('modalTransaksi').classList.replace('flex','hidden'); document.body.style.overflow=''; editing=null; $('formTransaksi').reset(); $('tanggalTransaksi').value='2026-03-19'; }
    $('formTransaksi').addEventListener('submit',e=>{e.preventDefault(); const r=rows.find(x=>x.id===editing); if(r){r.jenis=$('jenisTransaksi').value;r.kat=$('kategoriTransaksi').value;r.nilai=Number($('nominalTransaksi').value);r.ket=$('keteranganTransaksi').value;} else {rows.unshift({id:Date.now(),tgl:'19 Mar 2026',jenis:$('jenisTransaksi').value,kat:$('kategoriTransaksi').value,ket:$('keteranganTransaksi').value,nilai:Number($('nominalTransaksi').value)});} render(); close();});
    $('tambahTransaksi').addEventListener('click',()=>open()); $('tutupTransaksi').addEventListener('click',close); $('batalTransaksi').addEventListener('click',close); $('cariTransaksi').addEventListener('input',render); $('filterTransaksi').addEventListener('click',()=>{const menu=$('filterTransaksiMenu'),willOpen=menu.classList.contains('hidden');menu.classList.toggle('hidden',!willOpen);$('filterTransaksi').setAttribute('aria-expanded',String(willOpen));}); document.querySelectorAll('[data-filter-transaksi]').forEach(option=>option.addEventListener('click',()=>{activeFilter=option.dataset.filterTransaksi;$('filterTransaksiLabel').textContent=activeFilter==='semua'?'Filter':activeFilter;$('filterTransaksiMenu').classList.add('hidden');$('filterTransaksi').setAttribute('aria-expanded','false');render();})); $('modalTransaksi').addEventListener('click',e=>{if(e.target===$('modalTransaksi'))close();}); render();
})();
</script>
@endpush
