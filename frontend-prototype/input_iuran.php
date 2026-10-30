<?php
// input_iuran.php
$page_title = "Input Iuran - SakuOrg";
$page_heading = "Input Iuran";
$user_role = "admin";

include 'header.php';
include 'sidebar.php';

$iuran_data = [
  ['nama'=>'Jovan Putra Wijaya', 'nim'=>'25170004', 'periode'=>'Maret 2026', 'nominal'=>'Rp 75.000', 'status'=>'Lunas'],
  ['nama'=>'Juniarti Siadari', 'nim'=>'25170001', 'periode'=>'Maret 2026', 'nominal'=>'Rp 75.000', 'status'=>'Lunas'],
  ['nama'=>'Kent Muhammad Rafli', 'nim'=>'25170027', 'periode'=>'Maret 2026', 'nominal'=>'Rp 75.000', 'status'=>'Belum Lunas'],
  ['nama'=>'Refan Rhizqi Wintoro', 'nim'=>'25170025', 'periode'=>'Maret 2026', 'nominal'=>'Rp 75.000', 'status'=>'Belum Lunas'],
];
?>
<div class="flex-1 flex flex-col min-w-0">
  <?php include 'topbar.php'; ?>

  <main class="p-6 md:p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header Halaman & CTA Catat Pembayaran -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Input iuran anggota</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catat pembayaran kas yang telah diterima secara tunai atau transfer.</p>
      </div>
      <button type="button" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-md shadow-brand-600/20 transition self-start sm:self-auto">
        <i data-lucide="plus" class="w-4 h-4"></i> Catat pembayaran
      </button>
    </div>

    <!-- Alert / Card Alur Pencatatan -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4">
      <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center shrink-0">
        <i data-lucide="wallet" class="w-5 h-5"></i>
      </div>
      <div>
        <h3 class="font-extrabold text-slate-900 text-sm">Alur pencatatan sederhana</h3>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
          Setelah pembayaran diterima, pilih anggota dan simpan. Sistem akan menandai iuran lunas serta menambah transaksi pemasukan secara otomatis.
        </p>
      </div>
    </div>

    <!-- Tabel Input Iuran -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Toolbar -->
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input type="text" placeholder="Cari anggota yang akan dicatat..." 
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
              <th class="py-3 px-4">Anggota</th>
              <th class="py-3 px-4">Periode</th>
              <th class="py-3 px-4">Nominal</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php foreach($iuran_data as $row): ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="py-4 px-4">
                <div class="font-bold text-slate-900"><?= $row['nama'] ?></div>
                <div class="text-[11px] text-slate-400 font-mono"><?= $row['nim'] ?></div>
              </td>
              <td class="py-4 px-4 font-medium text-slate-600"><?= $row['periode'] ?></td>
              <td class="py-4 px-4 font-bold text-slate-800"><?= $row['nominal'] ?></td>
              <td class="py-4 px-4">
                <?php if ($row['status'] === 'Lunas'): ?>
                  <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 font-bold rounded-full text-xs">Lunas</span>
                <?php else: ?>
                  <span class="px-2.5 py-1 bg-amber-50 text-amber-600 font-bold rounded-full text-xs">Belum Lunas</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-4 text-right">
                <?php if ($row['status'] === 'Lunas'): ?>
                  <a href="#" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-700">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                  </a>
                <?php else: ?>
                  <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold shadow-sm transition">
                    Catat iuran
                  </button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>