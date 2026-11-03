<?php
/** SakuOrg — Halaman Tagihan Anggota (FRONTEND DEMO, tanpa database) */
if (session_status() === PHP_SESSION_NONE) session_start();
$user_role = $user_role ?? ($_SESSION['role'] ?? 'anggota'); // role ini hanya fallback demo, bukan proteksi akses
$standalone = !isset($SAKUORG_LAYOUT_LOADED);
?>
<?php if ($standalone): ?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tagihan | SakuOrg</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{brand:{50:'#F0F4FA',100:'#DBE5F3',200:'#B9CCE5',500:'#345A9C',600:'#234578',700:'#172554',800:'#111D42',900:'#0B1530'}}}}}</script>
<script src="https://unpkg.com/lucide@latest"></script>
</head><body class="bg-slate-50 text-slate-900"><div class="min-h-screen flex flex-col md:flex-row">
<?php
$sidebar = file_exists(__DIR__.'/includes/sidebar.php') ? __DIR__.'/includes/sidebar.php' : (file_exists(__DIR__.'/sidebar.php') ? __DIR__.'/sidebar.php' : null);
if ($sidebar) include $sidebar;
?>
<div class="flex min-w-0 flex-1 flex-col">
<?php
$topbar = file_exists(__DIR__.'/includes/topbar.php') ? __DIR__.'/includes/topbar.php' : (file_exists(__DIR__.'/topbar.php') ? __DIR__.'/topbar.php' : null);
if ($topbar) include $topbar;
?>
<?php endif; ?>
<main class="flex-1 p-5 md:p-8 lg:p-10" id="tagihan-anggota-page">
  <div class="mx-auto max-w-7xl">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div><p class="mb-1 text-sm font-medium text-brand-700">Keuangan Anggota</p><h1 class="text-3xl font-bold tracking-tight text-slate-900">Tagihan Saya</h1><p class="mt-2 text-slate-500">Pantau iuran organisasi dan kirim bukti pembayaran untuk tagihan yang belum lunas.</p></div>
      <span class="rounded-full border border-brand-100 bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700">Periode 2026</span>
    </div>
    <div class="mt-7 grid gap-4 md:grid-cols-3">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between text-sm text-slate-500"><span>Total belum lunas</span><i data-lucide="wallet" class="h-5 w-5 text-brand-700"></i></div><p id="jumlahBelumLunas" class="mt-3 text-3xl font-bold text-slate-900">Rp 0</p><p class="mt-2 text-xs text-slate-500">Tagihan yang belum dikirim pembayarannya</p></div>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between text-sm text-slate-500"><span>Menunggu verifikasi</span><i data-lucide="clock-3" class="h-5 w-5 text-amber-600"></i></div><p id="jumlahMenunggu" class="mt-3 text-3xl font-bold text-amber-600">0</p><p class="mt-2 text-xs text-slate-500">Pembayaran sedang ditinjau bendahara</p></div>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between text-sm text-slate-500"><span>Tagihan lunas</span><i data-lucide="check-circle-2" class="h-5 w-5 text-emerald-600"></i></div><p id="jumlahLunas" class="mt-3 text-3xl font-bold text-emerald-600">0</p><p class="mt-2 text-xs text-slate-500">Pembayaran sudah disetujui bendahara</p></div>
    </div>
    <div class="mt-6 rounded-xl bg-brand-50 p-5 text-sm leading-relaxed text-brand-700"><div class="flex gap-3"><i data-lucide="info" class="mt-0.5 h-5 w-5 shrink-0"></i><p>Pembayaran transfer perlu diverifikasi bendahara. Mengirim bukti transfer <strong>belum berarti lunas</strong>; status menjadi <strong>Lunas</strong> setelah disetujui.</p></div></div>
    <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-col gap-4 p-5 md:flex-row"><label class="relative flex-1"><span class="sr-only">Cari tagihan</span><i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input id="cariTagihan" type="search" placeholder="Cari jenis iuran atau periode..." class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100"></label><select id="filterTagihan" aria-label="Filter status tagihan" class="rounded-xl border border-slate-300 bg-white px-4 py-3 md:w-52"><option value="semua">Semua status</option><option value="Belum Bayar">Belum Bayar</option><option value="Menunggu">Menunggu Verifikasi</option><option value="Lunas">Lunas</option><option value="Ditolak">Ditolak</option></select></div>
      <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-6 py-4">Tagihan</th><th class="px-6 py-4">Jatuh Tempo</th><th class="px-6 py-4">Nominal</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Aksi</th></tr></thead><tbody id="daftarTagihan" class="divide-y divide-slate-100"></tbody></table></div>
      <div id="kosongTagihan" class="hidden p-12 text-center text-slate-500">Tidak ada tagihan yang cocok dengan filter.</div>
    </section>
    <footer class="mt-8 flex flex-wrap justify-between gap-3 text-xs text-slate-500"><span>© 2026 SakuOrg · Sistem Kas Organisasi</span><span>Kelola kas dengan mudah, transparan, dan terpercaya.</span></footer>
  </div>
</main>
<div id="modalTagihan" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="judulModalTagihan">
  <div class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
    <header class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><h2 id="judulModalTagihan" class="text-xl font-bold">Detail Tagihan</h2><button type="button" id="tutupTagihan" class="rounded-lg border border-slate-200 p-2 hover:bg-slate-50" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button></header>
    <div class="overflow-y-auto p-6"><p id="detailNamaTagihan" class="font-semibold text-lg"></p><div class="mt-4 grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-sm"><div><p class="text-slate-500">Periode</p><p id="detailPeriode" class="mt-1 font-semibold"></p></div><div><p class="text-slate-500">Jatuh tempo</p><p id="detailJatuhTempo" class="mt-1 font-semibold"></p></div><div><p class="text-slate-500">Nominal</p><p id="detailNominal" class="mt-1 font-semibold"></p></div><div><p class="text-slate-500">Status</p><div id="detailStatus" class="mt-1"></div></div></div>
      <div id="formPembayaran" class="mt-6"><label for="metodePembayaran" class="block text-sm font-semibold">Metode pembayaran</label><select id="metodePembayaran" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option value="Transfer">Transfer bank</option><option value="Tunai">Tunai (konfirmasi ke bendahara)</option></select><div id="areaUpload" class="mt-5"><label for="buktiTransfer" class="block text-sm font-semibold">Unggah bukti transfer <span class="text-red-500">*</span></label><input id="buktiTransfer" type="file" accept="image/png,image/jpeg,image/webp,application/pdf" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700"><p class="mt-2 text-xs text-slate-500">JPG, PNG, WebP, atau PDF, maksimal 5 MB. File hanya ditampilkan pada demo ini, tidak diunggah ke server.</p><p id="namaFileBukti" class="mt-2 text-sm text-brand-700"></p></div><label for="catatanPembayaran" class="mt-5 block text-sm font-semibold">Catatan (opsional)</label><textarea id="catatanPembayaran" rows="2" placeholder="Contoh: Transfer dari rekening atas nama saya" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></textarea><p id="pesanTagihan" class="mt-3 hidden text-sm text-red-600" role="alert"></p></div>
      <div id="infoPembayaran" class="mt-6 hidden rounded-xl bg-slate-50 p-4 text-sm text-slate-600"></div>
    </div>
    <footer class="flex justify-end gap-3 border-t border-slate-100 p-5"><button type="button" id="batalTagihan" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Tutup</button><button type="button" id="kirimTagihan" class="rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-800">Kirim Pembayaran</button></footer>
  </div>
</div>
<script>
(() => {
  // DATA DUMMY. Nantinya ganti dengan data tagihan anggota yang sedang login dari backend.
  const bills = [
    {id:101,nama:'Iuran Bulanan',periode:'November 2026',tanggal:'2026-11-15',nominal:50000,status:'Belum Bayar'},
    {id:102,nama:'Iuran Kegiatan',periode:'Oktober 2026',tanggal:'2026-10-31',nominal:75000,status:'Ditolak',alasan:'Bukti transfer belum jelas'},
    {id:103,nama:'Iuran Bulanan',periode:'Oktober 2026',tanggal:'2026-10-15',nominal:50000,status:'Menunggu'},
    {id:104,nama:'Iuran Bulanan',periode:'September 2026',tanggal:'2026-09-15',nominal:50000,status:'Lunas'}
  ];
  const $=id=>document.getElementById(id);
  const rupiah=n=>new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n);
  const date=s=>new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short',year:'numeric',timeZone:'UTC'}).format(new Date(s+'T12:00:00Z'));
  const badge=s=>`<span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-semibold ${s==='Lunas'?'bg-emerald-50 text-emerald-700':s==='Menunggu'?'bg-amber-50 text-amber-700':s==='Ditolak'?'bg-red-50 text-red-600':'bg-red-50 text-red-600'}">${s==='Menunggu'?'Menunggu Verifikasi':s}</span>`;
  let active=null,lastFocus=null;
  function render(){const q=$('cariTagihan').value.trim().toLowerCase(),f=$('filterTagihan').value;const rows=bills.filter(b=>(f==='semua'||b.status===f)&&`${b.nama} ${b.periode}`.toLowerCase().includes(q));$('daftarTagihan').innerHTML=rows.map(b=>`<tr><td class="px-6 py-5"><p class="font-bold text-slate-900">${b.nama}</p><p class="mt-1 text-xs text-slate-500">${b.periode}</p></td><td class="px-6 py-5">${date(b.tanggal)}</td><td class="px-6 py-5 font-semibold">${rupiah(b.nominal)}</td><td class="px-6 py-5">${badge(b.status)}</td><td class="px-6 py-5"><button type="button" data-bill="${b.id}" class="rounded-lg border border-slate-200 px-4 py-2 font-medium hover:bg-slate-50">${b.status==='Belum Bayar'||b.status==='Ditolak'?'Bayar Sekarang':'Detail'}</button></td></tr>`).join('');$('kosongTagihan').classList.toggle('hidden',rows.length>0);$('jumlahBelumLunas').textContent=rupiah(bills.filter(b=>['Belum Bayar','Ditolak'].includes(b.status)).reduce((a,b)=>a+b.nominal,0));$('jumlahMenunggu').textContent=bills.filter(b=>b.status==='Menunggu').length;$('jumlahLunas').textContent=bills.filter(b=>b.status==='Lunas').length;document.querySelectorAll('[data-bill]').forEach(btn=>btn.addEventListener('click',()=>open(Number(btn.dataset.bill),btn)));}
  function open(id,trigger){active=bills.find(b=>b.id===id);if(!active)return;lastFocus=trigger;$('detailNamaTagihan').textContent=active.nama;$('detailPeriode').textContent=active.periode;$('detailJatuhTempo').textContent=date(active.tanggal);$('detailNominal').textContent=rupiah(active.nominal);$('detailStatus').innerHTML=badge(active.status);const canPay=['Belum Bayar','Ditolak'].includes(active.status);$('formPembayaran').classList.toggle('hidden',!canPay);$('kirimTagihan').classList.toggle('hidden',!canPay);$('infoPembayaran').classList.toggle('hidden',canPay);$('infoPembayaran').textContent=active.status==='Lunas'?'Pembayaran telah disetujui bendahara.':active.status==='Menunggu'?'Bukti pembayaran sudah dikirim dan sedang menunggu pemeriksaan bendahara.':active.alasan?'Alasan penolakan: '+active.alasan:'';$('metodePembayaran').value='Transfer';$('buktiTransfer').value='';$('catatanPembayaran').value='';$('namaFileBukti').textContent='';$('pesanTagihan').classList.add('hidden');$('areaUpload').classList.remove('hidden');$('modalTagihan').classList.replace('hidden','flex');document.body.style.overflow='hidden';$('tutupTagihan').focus();}
  function close(){$('modalTagihan').classList.replace('flex','hidden');document.body.style.overflow='';active=null;lastFocus?.focus();}
  $('metodePembayaran').addEventListener('change',()=>{$('areaUpload').classList.toggle('hidden',$('metodePembayaran').value!=='Transfer');});
  $('buktiTransfer').addEventListener('change',()=>{$('namaFileBukti').textContent=$('buktiTransfer').files[0]?.name||'';});
  $('kirimTagihan').addEventListener('click',()=>{if(!active||!['Belum Bayar','Ditolak'].includes(active.status))return;const method=$('metodePembayaran').value,file=$('buktiTransfer').files[0];if(method==='Transfer'&&(!file||!['image/jpeg','image/png','image/webp','application/pdf'].includes(file.type)||file.size>5*1024*1024)){$('pesanTagihan').textContent='Pilih bukti transfer JPG, PNG, WebP atau PDF maksimal 5 MB.';$('pesanTagihan').classList.remove('hidden');return;}active.status='Menunggu';active.alasan='';active.metode=method;render();close();});
  $('batalTagihan').addEventListener('click',close);$('tutupTagihan').addEventListener('click',close);$('modalTagihan').addEventListener('click',e=>{if(e.target===$('modalTagihan'))close();});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!$('modalTagihan').classList.contains('hidden'))close();});$('cariTagihan').addEventListener('input',render);$('filterTagihan').addEventListener('change',render);
  render();if(window.lucide)lucide.createIcons();
})();
</script>
<?php if ($standalone): ?></div></div></body></html><?php endif; ?>
