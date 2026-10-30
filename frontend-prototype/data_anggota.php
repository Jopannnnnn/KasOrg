<?php
// data_anggota.php
$page_title = "Data Anggota - SakuOrg";
$page_heading = "Data Anggota";
$user_role = "admin";

include 'header.php';
include 'sidebar.php';

$anggota_list = [
  ['nim'=>'25170004', 'nama'=>'Jovan Putra Wijaya', 'email'=>'jovan@kampus.ac.id', 'status'=>'Lunas'],
  ['nim'=>'25170001', 'nama'=>'Juniarti Siadari', 'email'=>'juniarti@kampus.ac.id', 'status'=>'Lunas'],
  ['nim'=>'25170027', 'nama'=>'Kent Muhammad Rafli', 'email'=>'kent@kampus.ac.id', 'status'=>'Belum Lunas'],
  ['nim'=>'25170025', 'nama'=>'Refan Rhizqi Wintoro', 'email'=>'refan@kampus.ac.id', 'status'=>'Belum Lunas'],
  ['nim'=>'25170031', 'nama'=>'Nadia Maharani', 'email'=>'nadia@kampus.ac.id', 'status'=>'Lunas'],
];
?>
<div class="flex-1 flex flex-col min-w-0">
  <?php include 'topbar.php'; ?>

  <main class="p-6 md:p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header Halaman & Tombol Tambah Anggota -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola data anggota</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola profil anggota aktif dan pantau status iuran bulan ini.</p>
      </div>
      <button type="button" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-md shadow-brand-600/20 transition self-start sm:self-auto">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah anggota
      </button>
    </div>

    <!-- Ringkasan Statistik Anggota -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Total anggota</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">42</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center">
          <i data-lucide="users" class="w-4 h-4"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Sudah lunas</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">30</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="check" class="w-4 h-4"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Belum lunas</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">12</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <i data-lucide="clock" class="w-4 h-4"></i>
        </div>
      </div>
    </div>

    <!-- Tabel Data Anggota Lengkap -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Toolbar Pencarian & Filter -->
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input type="text" placeholder="Cari nama, NIM, atau email..." 
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

      <!-- Data Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase text-[11px] tracking-wider">
              <th class="py-3 px-4">NIM / ID</th>
              <th class="py-3 px-4">Nama Anggota</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Iuran Maret</th>
              <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php foreach($anggota_list as $row): ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="py-4 px-4 font-mono font-medium text-slate-500"><?= $row['nim'] ?></td>
              <td class="py-4 px-4 font-bold text-slate-900"><?= $row['nama'] ?></td>
              <td class="py-4 px-4 text-slate-500"><?= $row['email'] ?></td>
              <td class="py-4 px-4">
                <?php if ($row['status'] === 'Lunas'): ?>
                  <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 font-bold rounded-full text-xs">Lunas</span>
                <?php else: ?>
                  <span class="px-2.5 py-1 bg-amber-50 text-amber-600 font-bold rounded-full text-xs">Belum Lunas</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-4 text-right">
                <div class="inline-flex items-center gap-3">
                  <a href="input_iuran.php?nim=<?= $row['nim'] ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-brand-600 transition">
                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i> Input iuran
                  </a>
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