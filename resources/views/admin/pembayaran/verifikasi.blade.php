@extends('layouts.app', [
    'title' => 'Verifikasi Pembayaran | KasOrg',
    'heading' => 'Verifikasi Pembayaran',
    'role' => 'admin',
    'userName' => 'Jovan Wijaya',
    'userInitials' => 'JW',
])

@section('content')
<main class="flex-1 p-5 md:p-8 lg:p-10" id="verifikasi-page">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Verifikasi Pembayaran</h1>
        <p class="mt-2 text-slate-500">Periksa bukti transfer dan cocokkan dengan mutasi rekening.</p>

        <div class="mt-8 rounded-xl bg-brand-50 px-5 py-5 text-sm text-brand-700">
            Transfer manual tidak terverifikasi otomatis. Saldo kas bertambah hanya setelah bendahara menyetujui pembayaran.
        </div>

        <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-3 p-5 md:flex-row">
                <label class="relative flex-1">
                    <span class="sr-only">Cari pembayaran</span>
                    <i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input id="cariPembayaran" class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100" placeholder="Cari nama atau keterangan...">
                </label>
                <select id="filterStatus" aria-label="Filter status pembayaran" class="rounded-xl border border-slate-300 bg-white px-4 py-3 md:w-40">
                    <option value="semua">Semua status</option><option>Menunggu</option><option>Disetujui</option><option>Ditolak</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Anggota</th><th class="px-6 py-4">Tagihan</th><th class="px-6 py-4">Metode</th><th class="px-6 py-4">Nominal</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Aksi</th></tr></thead>
                    <tbody id="daftarPembayaran" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
            <p id="hasilKosong" class="hidden py-12 text-center text-slate-500">Tidak ada pembayaran yang sesuai pencarian.</p>
        </section>
        <footer class="mt-8 flex flex-wrap justify-between gap-3 text-xs text-slate-500"><span>© 2026 KasOrg · Sistem Kas Organisasi</span><span>Kelola kas dengan mudah, transparan, dan terpercaya.</span></footer>
    </div>
</main>

<div id="modalVerifikasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="modalJudul">
    <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><h2 id="modalJudul" class="text-xl font-bold">Verifikasi pembayaran</h2><button type="button" id="tutupModal" class="rounded-lg border border-slate-200 p-2.5 hover:bg-slate-50" aria-label="Tutup modal"><i data-lucide="x" class="h-5 w-5"></i></button></div>
        <div class="overflow-y-auto p-6">
            <p id="infoNama" class="font-semibold"></p><p id="infoDetail" class="mt-4 text-sm text-slate-700"></p><div id="statusDetail" class="mt-4"></div><p id="infoPengirim" class="mt-5 text-sm"></p>
            <div class="mt-5 flex min-h-40 flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center"><strong>BUKTI TRANSFER SIMULASI</strong><p class="mt-4" id="nominalBukti"></p><p class="mt-4 text-sm text-slate-600">Contoh data, bukan bukti bank asli.</p></div>
            <label class="mt-6 flex items-start gap-3 text-sm leading-relaxed"><input id="cocokMutasi" type="checkbox" class="mt-1 h-4 w-4 accent-[#172554]"><span>Saya telah mencocokkan nominal, tanggal, pengirim, dan dana masuk pada mutasi rekening (simulasi).</span></label>
            <label for="alasanPenolakan" class="mt-6 block text-sm font-semibold">Alasan penolakan (wajib jika menolak)</label>
            <textarea id="alasanPenolakan" rows="3" class="mt-2 w-full resize-y rounded-xl border border-slate-300 p-3 text-sm outline-none focus:border-brand-600" placeholder="Jelaskan hal yang perlu diperbaiki anggota"></textarea>
            <p id="pesanValidasi" class="mt-2 hidden text-sm text-red-600" role="alert"></p><p id="statusFinal" class="mt-4 hidden rounded-lg bg-slate-50 p-3 text-sm text-slate-600"></p>
        </div>
        <div id="aksiModal" class="flex flex-wrap justify-end gap-3 border-t border-slate-100 bg-white px-6 py-4"><button type="button" id="tolakPembayaran" class="rounded-xl border border-red-200 px-5 py-2.5 font-semibold text-red-600 hover:bg-red-50">Tolak pembayaran</button><button type="button" id="setujuiPembayaran" class="rounded-xl bg-brand-700 px-5 py-2.5 font-semibold text-white hover:bg-brand-800">Setujui pembayaran</button></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    // Data contoh lokal. Tidak dibaca dari atau ditulis ke database.
    const pembayaran = [
        {id:1,nama:'Aditya Pratama',tanggal:'2026-10-28',tagihan:'Iuran Bulanan',metode:'Transfer',nominal:50000,status:'Ditolak',alasan:'Bukti transfer tidak sesuai'},
        {id:2,nama:'Salsabila Zahra',tanggal:'2026-10-20',tagihan:'Iuran Bulanan',metode:'Tunai',nominal:50000,status:'Disetujui',alasan:''},
        {id:3,nama:'Rizky Ramadhan',tanggal:'2026-10-30',tagihan:'Iuran Bulanan',metode:'Transfer',nominal:50000,status:'Menunggu',alasan:''},
        {id:4,nama:'Nadia Putri',tanggal:'2026-10-29',tagihan:'Iuran Bulanan',metode:'Transfer',nominal:50000,status:'Menunggu',alasan:''}
    ];
    const $ = id => document.getElementById(id);
    const rupiah = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n);
    const badge = s => `<span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-semibold ${s==='Menunggu'?'bg-amber-50 text-amber-700':s==='Disetujui'?'bg-green-50 text-green-700':'bg-red-50 text-red-600'}">${s}</span>`;
    let selected = null;
    function render() {
        const q = $('cariPembayaran').value.trim().toLowerCase(), f = $('filterStatus').value;
        const filtered = pembayaran.filter(p => (f === 'semua' || p.status === f) && [p.nama,p.tagihan,p.metode,p.tanggal,p.status,p.alasan].join(' ').toLowerCase().includes(q));
        $('daftarPembayaran').innerHTML = filtered.map(p => `<tr><td class="px-6 py-5"><div class="font-bold text-slate-900">${p.nama}</div><div class="mt-1 text-xs text-slate-500">${p.tanggal}</div></td><td class="px-6 py-5">${p.tagihan}</td><td class="px-6 py-5">${p.metode}</td><td class="px-6 py-5">${rupiah(p.nominal)}</td><td class="px-6 py-5">${badge(p.status)}</td><td class="px-6 py-5"><button data-id="${p.id}" class="detail-btn rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50">${p.status==='Menunggu'?'Tinjau':'Detail'}</button></td></tr>`).join('');
        $('hasilKosong').classList.toggle('hidden', filtered.length > 0);
        document.querySelectorAll('.detail-btn').forEach(btn => btn.addEventListener('click', () => openModal(Number(btn.dataset.id))));
    }
    function openModal(id) {
        selected = pembayaran.find(p => p.id === id); if (!selected) return;
        $('infoNama').textContent = `${selected.nama} · ${selected.tagihan}`; $('infoDetail').textContent = `${rupiah(selected.nominal)} · ${selected.metode} · ${selected.tanggal}`;
        $('statusDetail').innerHTML = badge(selected.status); $('infoPengirim').textContent = `Pengirim: ${selected.nama}`; $('nominalBukti').textContent = rupiah(selected.nominal);
        $('cocokMutasi').checked = false; $('alasanPenolakan').value = selected.alasan || ''; $('pesanValidasi').classList.add('hidden');
        $('aksiModal').classList.toggle('hidden', selected.status !== 'Menunggu'); $('statusFinal').classList.toggle('hidden', selected.status === 'Menunggu');
        $('statusFinal').textContent = selected.status === 'Ditolak' ? `Alasan penolakan: ${selected.alasan}` : `Status pembayaran: ${selected.status}`;
        $('modalVerifikasi').classList.replace('hidden','flex'); document.body.style.overflow='hidden'; $('tutupModal').focus();
    }
    function closeModal(){ $('modalVerifikasi').classList.replace('flex','hidden'); document.body.style.overflow=''; selected=null; }
    function error(message){ $('pesanValidasi').textContent=message; $('pesanValidasi').classList.remove('hidden'); }
    $('setujuiPembayaran').addEventListener('click', () => { if(!selected || selected.status!=='Menunggu') return; if(!$('cocokMutasi').checked){ error('Centang konfirmasi pencocokan mutasi terlebih dahulu.'); return; } selected.status='Disetujui'; render(); closeModal(); });
    $('tolakPembayaran').addEventListener('click', () => { if(!selected || selected.status!=='Menunggu') return; const alasan=$('alasanPenolakan').value.trim(); if(!alasan){ error('Alasan penolakan wajib diisi.'); return; } selected.status='Ditolak'; selected.alasan=alasan; render(); closeModal(); });
    $('tutupModal').addEventListener('click',closeModal); $('modalVerifikasi').addEventListener('click',e=>{if(e.target===$('modalVerifikasi'))closeModal();});
    $('cariPembayaran').addEventListener('input',render); $('filterStatus').addEventListener('change',render); document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!$('modalVerifikasi').classList.contains('hidden'))closeModal();}); render();
})();
</script>
@endpush
