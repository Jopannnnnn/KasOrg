<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$role_param = isset($_GET['preview_role']) ? '&preview_role='.$_GET['preview_role'] : '';
?>
<aside class="w-full md:w-64 bg-brand-700 md:min-h-screen text-white flex flex-col justify-between p-5 select-none shrink-0 shadow-xl">
  <div>
    <!-- Logo Brand SakuOrg -->
    <div class="flex items-center gap-3 px-2 py-4 mb-6">
      <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20 shadow-inner">
        <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
      </div>
      <div>
        <h1 class="font-bold text-lg leading-tight tracking-tight">SakuOrg</h1>
        <p class="text-xs text-blue-200">Sistem Kas Organisasi</p>
      </div>
    </div>

    <!-- Navigasi Menu Utama -->
    <div class="text-[11px] font-bold text-blue-200/70 uppercase tracking-wider px-3 mb-2">Menu Utama</div>
    <nav class="space-y-1">
      <?php if ($user_role === 'admin'): ?>
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'dashboard.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="layout-grid" class="w-4 h-4"></i> Dashboard
        </a>
        <a href="data_anggota.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'data_anggota.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="users" class="w-4 h-4"></i> Data Anggota
        </a>
        <a href="input_iuran.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'input_iuran.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="banknote" class="w-4 h-4"></i> Input Iuran
        </a>
        <a href="transaksi_kas.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'transaksi_kas.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="credit-card" class="w-4 h-4"></i> Transaksi Kas
        </a>
        <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'laporan.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="file-text" class="w-4 h-4"></i> Laporan
        </a>
        <a href="transparansi_kas.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'transparansi_kas.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="eye" class="w-4 h-4"></i> Transparansi Kas
        </a>
      <?php else: ?>
        <a href="dashboard_anggota.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= $current_page == 'dashboard_anggota.php' ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="layout-grid" class="w-4 h-4"></i> Dashboard
        </a>
        <a href="laporan.php?view=anggota" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= ($current_page == 'laporan.php' && isset($_GET['view'])) ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="file-text" class="w-4 h-4"></i> Laporan
        </a>
        <a href="transparansi_kas.php?view=anggota" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition <?= ($current_page == 'transparansi_kas.php' && isset($_GET['view'])) ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' ?>">
          <i data-lucide="eye" class="w-4 h-4"></i> Transparansi Kas
        </a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Bottom: Preview Switcher & Tombol Logout -->
  <div class="pt-6 border-t border-white/15 space-y-4">
    <div class="bg-black/20 p-2 rounded-xl backdrop-blur-sm">
      <div class="text-[11px] font-bold text-blue-200 uppercase tracking-wider mb-2 text-center">Mode Preview</div>
      <div class="grid grid-cols-2 gap-1 bg-brand-900/50 p-1 rounded-lg">
        <a href="dashboard.php" class="text-center py-1.5 rounded-md text-xs font-bold transition <?= $user_role === 'admin' ? 'bg-white text-brand-700 shadow-sm' : 'text-white/80 hover:text-white' ?>">Admin</a>
        <a href="dashboard_anggota.php" class="text-center py-1.5 rounded-md text-xs font-bold transition <?= $user_role === 'anggota' ? 'bg-white text-brand-700 shadow-sm' : 'text-white/80 hover:text-white' ?>">Anggota</a>
      </div>
    </div>

    <a href="login.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-blue-100 hover:bg-white/10 transition">
      <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
    </a>
  </div>
</aside>