<?php
// transaksi_kas.php
$page_title = "Transaksi Kas - SakuOrg";
$page_heading = "Transaksi Kas";
$user_role = "admin";

include 'header.php';
include 'sidebar.php';

$transaksi = [
  ['tgl'=>'18 Mar 2026', 'jenis'=>'Pemasukan', 'kat'=>'Iuran anggota', 'ket'=>'Iuran bulan Maret — Raka Putra', 'nominal'=>'+ Rp 75.000', 'color'=>'text-emerald-600', 'bg'=>'bg-emerald-50'],
  ['tgl'=>'17 Mar 2026', 'jenis'=>'Pengeluaran', 'kat'=>'Konsumsi', 'ket'=>'Konsumsi rapat pengurus', 'nominal'=>'- Rp 425.000', 'color'=>'text-rose-600', 'bg'=>'bg-rose-50'],
  ['tgl'=>'15 Mar 2026', 'jenis'=>'Pemasukan', 'kat'=>'Donasi', 'ket'=>'Donasi alumni organisasi', 'nominal'=>'+ Rp 1.500.000', 'color'=>'text-emerald-600', 'bg'=>'bg-emerald-50'],
  ['tgl'=>'12 Mar 2026', 'jenis'=>'Pengeluaran', 'kat'=>'Perlengkapan', 'ket'=>'Cetak banner kegiatan', 'nominal'=>'- Rp 680.000', 'color'=>'text-rose-600', 'bg'=>'bg-rose-50'],
  ['tgl'=>'10 Mar 2026', 'jenis'=>'Pemasukan', 'kat'=>'Iuran anggota', 'ket'=>'Iuran kolektif bulan Maret', 'nominal'=>'+ Rp 1.125.000', 'color'=>'text-emerald-600', 'bg'=>'bg-emerald-50'],
];
?>
<div class="flex-1 flex flex-col min-w-0">
  <?php include 'topbar.php'; ?>

  <main class="p-6 md:p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header Halaman & Tambah Transaksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Transaksi kas</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catat dan kelola seluruh pemasukan serta pengeluaran organisasi.</p>
      </div>
      <button type="button" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-md shadow-brand-600/20 transition self-start sm:self-auto">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah transaksi
      </button>
    </div>

    <!-- 3 Card Ringkasan Arus Kas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Pemasukan periode ini</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">Rp 4.275.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Pengeluaran periode ini</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">Rp 2.180.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Selisih arus kas</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">+ Rp 2.095.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <!-- Tabel Data Transaksi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Filter Toolbar -->
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input type="text" placeholder="Cari transaksi atau keterangan..." 
                 class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:bg-white transition">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button type="button" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-slate-500"></i> Filter
          </button>
          <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-500"></i> Maret 2026
          </div>
        </div>
      </div>

      <!-- Table Body -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase text-[11px] tracking-wider">
              <th class="py-3 px-4">Tanggal</th>
              <th class="py-3 px-4">Jenis</th>
              <th class="py-3 px-4">Kategori</th>
              <th class="py-3 px-4">Keterangan</th>
              <th class="py-3 px-4 text-right">Nominal</th>
              <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php foreach($transaksi as $item): ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="py-4 px-4 font-medium text-slate-600"><?= $item['tgl'] ?></td>
              <td class="py-4 px-4"><span class="px-2.5 py-1 <?= $item['bg'] ?> <?= $item['color'] ?> font-bold rounded-full text-xs"><?= $item['jenis'] ?></span></td>
              <td class="py-4 px-4 font-semibold text-slate-900"><?= $item['kat'] ?></td>
              <td class="py-4 px-4 text-slate-500"><?= $item['ket'] ?></td>
              <td class="py-4 px-4 text-right font-bold <?= $item['color'] ?>"><?= $item['nominal'] ?></td>
              <td class="py-4 px-4 text-right">
                <div class="inline-flex items-center gap-2">
                  <button type="button" aria-label="Edit" class="text-slate-400 hover:text-slate-600"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                  <button type="button" aria-label="Hapus" class="text-slate-400 hover:text-rose-600"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <span>Menampilkan 1–5 dari 42 data</span>
        <div class="inline-flex items-center gap-1">
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button>
          <button class="px-3 py-1.5 rounded-lg bg-brand-600 text-white font-bold">1</button>
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">2</button>
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Berikutnya</button>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>