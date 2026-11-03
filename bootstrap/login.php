<?php
// login.php
$page_title = "Masuk ke Akun - SakuOrg";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
          colors: { brand: { 50: '#F0F4FA', 100: '#DBE5F3', 200: '#B9CCE5', 500: '#345A9C', 600: '#234578', 700: '#172554', 800: '#111D42', 900: '#0B1530', } }
        }
      }
    }
  </script>
</head>
<body class="bg-slate-100 font-sans min-h-screen flex items-center justify-center p-4 sm:p-6">
  <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 border border-slate-100">
    
    <!-- Hero / Info Banner Sisi Kiri -->    
  <div class="bg-brand-700 p-8 sm:p-12 text-white flex flex-col justify-between relative overflow-hidden">
      <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      
      <div>
        <div class="flex items-center gap-3 mb-10">
          <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center border border-white/30">
            <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h1 class="font-bold text-lg leading-tight">SakuOrg</h1>
            <p class="text-xs text-blue-100">Sistem Kas Organisasi</p>
          </div>
        </div>

        <div class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-semibold text-blue-50 mb-4">
          Keuangan lebih tertata
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold leading-snug mb-3">
          Kelola kas organisasi dengan mudah dan transparan.
        </h2>
        <p class="text-sm text-blue-100 leading-relaxed">
          Satu tempat untuk mencatat iuran, memantau transaksi, dan menyusun laporan keuangan organisasi.
        </p>
      </div>

      <div class="mt-8">
        <div class="grid grid-cols-3 gap-3 mb-8">
          <div class="bg-white/10 p-3 rounded-2xl border border-white/15">
            <span class="block text-xl font-bold">42</span>
            <span class="text-[11px] text-blue-100">Anggota aktif</span>
          </div>
          <div class="bg-white/10 p-3 rounded-2xl border border-white/15">
            <span class="block text-xl font-bold">98%</span>
            <span class="text-[11px] text-blue-100">Iuran tercatat</span>
          </div>
          <div class="bg-white/10 p-3 rounded-2xl border border-white/15">
            <span class="block text-xl font-bold">24/7</span>
            <span class="text-[11px] text-blue-100">Data tersedia</span>
          </div>
        </div>
        <p class="text-[11px] text-blue-200">Kelompok 4 • Pemrograman Web Lanjut</p>
      </div>
    </div>

    <!-- Form Login Sisi Kanan (Murni Login, Tanpa Register Link) -->
    <div class="p-8 sm:p-12 flex flex-col justify-center">
      <div class="mb-8">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Selamat datang kembali</span>
        <h3 class="text-2xl font-extrabold text-slate-900 mt-1">Masuk ke akun Anda</h3>
        <p class="text-xs text-slate-500 mt-1">Gunakan email dan kata sandi yang sudah terdaftar.</p>
      </div>

      <form action="proses_login.php" method="POST" class="space-y-5">
        <div>
          <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat email</label>
          <input type="email" id="email" name="email" value="bendahara@organisasi.id" required
                 class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-transparent transition text-slate-800 placeholder-slate-400">
        </div>

        <div>
          <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata sandi</label>
          <div class="relative">
            <input type="password" id="password" name="password" value="secret1234" required
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-transparent transition text-slate-800 pr-10">
            <button type="button" aria-label="Toggle Password" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
              <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-sm flex items-center justify-center gap-2 transition shadow-md shadow-brand-600/30">
          Masuk ke sistem <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>
      </form>

  </div>
  <script>lucide.createIcons();</script>
</body>
</html>