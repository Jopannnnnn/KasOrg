<?php
// dashboard_anggota.php
$page_title = "Dashboard Anggota - SakuOrg";
$page_heading = "Dashboard";
$user_role = "anggota";

include 'header.php';
include 'sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
  <?php include 'topbar.php'; ?>
  
  <main class="p-6 md:p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header Welcome & Filter Periode -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Selamat datang, Kent!</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Berikut ringkasan kondisi keuangan organisasi bulan ini.</p>
      </div>
      <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm text-xs font-bold text-slate-700 self-start sm:self-auto">
        <i data-lucide="calendar" class="w-4 h-4 text-slate-500"></i>
        <span>Maret 2026</span>
        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
      </div>
    </div>

    <!-- 4 Kartu Metrik Anggota -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500">Total saldo kas</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center">
            <i data-lucide="wallet" class="w-4 h-4"></i>
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900">Rp 18.750.000</div>
        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 mt-2">
          <span>+8,4%</span> <span class="text-slate-400 font-medium">dari bulan lalu</span>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500">Pemasukan bulan ini</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900">Rp 4.275.000</div>
        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 mt-2">
          <span>+12,5%</span> <span class="text-slate-400 font-medium">dari bulan lalu</span>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500">Pengeluaran bulan ini</span>
          <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
            <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900">Rp 2.180.000</div>
        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 mt-2">
          <span>-3,2%</span> <span class="text-slate-400 font-medium">dari bulan lalu</span>
        </div>
      </div>

      <!-- Status Iuran Personal -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500">Status iuran Maret</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <i data-lucide="clock" class="w-4 h-4"></i>
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900">Belum lunas</div>
        <div class="text-xs font-semibold text-amber-600 mt-2">
          Tagihan: Rp 75.000
        </div>
      </div>
    </div>

    <!-- Grafik Arus Kas & Card Status Iuran Anda -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="font-extrabold text-slate-900 text-base">Arus kas organisasi</h3>
            <p class="text-xs text-slate-500">Perbandingan pemasukan dan pengeluaran</p>
          </div>
          <div class="flex items-center gap-4 text-xs font-bold">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span> Pemasukan</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-100"></span> Pengeluaran</span>
          </div>
        </div>
        <div class="h-56 flex items-end justify-between gap-4 px-2 pt-4 border-b border-slate-100">
          <?php 
          $bars = [
            ['m'=>'Okt', 'in'=>50, 'out'=>30],
            ['m'=>'Nov', 'in'=>68, 'out'=>42],
            ['m'=>'Des', 'in'=>62, 'out'=>32],
            ['m'=>'Jan', 'in'=>82, 'out'=>50],
            ['m'=>'Feb', 'in'=>74, 'out'=>46],
            ['m'=>'Mar', 'in'=>95, 'out'=>58],
          ];
          foreach($bars as $b): ?>
            <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
              <div class="w-full max-w-[42px] flex items-end gap-1.5 h-full justify-center">
                <div class="w-3.5 bg-brand-600 rounded-t-md" style="height: <?= $b['in'] ?>%;"></div>
                <div class="w-3.5 bg-blue-100 rounded-t-md" style="height: <?= $b['out'] ?>%;"></div>
              </div>
              <span class="text-xs font-semibold text-slate-400"><?= $b['m'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Status Iuran Personal Detail Card -->
      <div class="bg-amber-50/50 p-6 rounded-2xl border border-amber-200/60 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-4">
            <i data-lucide="clock" class="w-5 h-5"></i>
          </div>
          <h3 class="font-extrabold text-slate-900 text-base">Belum lunas</h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Iuran Maret 2026 sebesar <strong>Rp 75.000</strong> belum tercatat lunas.
          </p>
          <div class="mt-4 p-3 bg-white/80 rounded-xl border border-amber-200/50 text-xs text-slate-600">
            Hubungi bendahara untuk konfirmasi pembayaran via transfer atau tunai.
          </div>
        </div>
        <div class="mt-6 pt-4 border-t border-amber-200/50 text-[11px] text-amber-800 font-semibold">
          Pembaruan terbaru bulan ini
        </div>
      </div>
    </div>

    <!-- Tabel Transaksi Terbaru (Portal Anggota) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="font-extrabold text-slate-900 text-base">Transaksi terbaru</h3>
          <p class="text-xs text-slate-500">Aktivitas kas paling baru</p>
        </div>
        <a href="transparansi_kas.php?view=anggota" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
          Lihat semua <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[11px] tracking-wider">
              <th class="py-3 px-3">Tanggal</th>
              <th class="py-3 px-3">Jenis</th>
              <th class="py-3 px-3">Kategori</th>
              <th class="py-3 px-3">Keterangan</th>
              <th class="py-3 px-3 text-right">Nominal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr>
              <td class="py-3.5 px-3 font-medium">18 Mar 2026</td>
              <td class="py-3.5 px-3"><span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 font-bold rounded-full text-xs">Pemasukan</span></td>
              <td class="py-3.5 px-3 font-semibold">Iuran anggota</td>
              <td class="py-3.5 px-3 text-slate-500">Iuran bulan Maret — Raka Putra</td>
              <td class="py-3.5 px-3 text-right font-bold text-emerald-600">+ Rp 75.000</td>
            </tr>
            <tr>
              <td class="py-3.5 px-3 font-medium">17 Mar 2026</td>
              <td class="py-3.5 px-3"><span class="px-2.5 py-1 bg-rose-50 text-rose-600 font-bold rounded-full text-xs">Pengeluaran</span></td>
              <td class="py-3.5 px-3 font-semibold">Konsumsi</td>
              <td class="py-3.5 px-3 text-slate-500">Konsumsi rapat pengurus</td>
              <td class="py-3.5 px-3 text-right font-bold text-rose-600">- Rp 425.000</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>