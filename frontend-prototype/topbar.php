<?php
// includes/topbar.php
?>
<header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-xs">
  <div>
    <h2 class="text-xl font-bold text-slate-800 leading-tight"><?= htmlspecialchars($page_heading ?? 'Dashboard') ?></h2>
    <p class="text-xs text-slate-500 font-medium"><?= $user_role === 'admin' ? 'Panel administrasi organisasi' : 'Portal informasi anggota' ?></p>
  </div>
  <div class="flex items-center gap-4">
    <button type="button" aria-label="Notifikasi" class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 relative transition">
      <i data-lucide="bell" class="w-5 h-5"></i>
      <span class="w-2 h-2 bg-rose-500 rounded-full absolute top-2 right-2 ring-2 ring-white"></span>
    </button>
    <div class="flex items-center gap-3 pl-2 border-l border-slate-100">
      <div class="w-10 h-10 rounded-full bg-blue-100 text-brand-700 font-extrabold flex items-center justify-center text-sm border border-blue-200 shadow-xs">
        <?= $user_role === 'admin' ? 'JW' : 'KM' ?>
      </div>
      <div class="hidden sm:block text-left">
        <p class="text-sm font-bold text-slate-800 leading-tight"><?= $user_role === 'admin' ? 'Jovan Wijaya' : 'Kent Rafli' ?></p>
        <p class="text-xs text-slate-500"><?= $user_role === 'admin' ? 'Bendahara' : 'Anggota' ?></p>
      </div>
    </div>
  </div>
</header>