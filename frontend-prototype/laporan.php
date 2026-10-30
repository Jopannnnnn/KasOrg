<?php
// laporan.php
$page_title = "Laporan Keuangan - SakuOrg";
$page_heading = "Laporan";
$user_role = (isset($_GET['view']) && $_GET['view'] === 'anggota') ? 'anggota' : 'admin';

include 'header.php';
include 'sidebar.php';
?>
<div class="flex-1 flex flex-col min-w-0">
  <?php include 'topbar.php'; ?>

  <main class="p-6 md:p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header Halaman & Unduh PDF -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan keuangan</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Rekapitulasi kondisi keuangan organisasi secara lengkap dan transparan.</p>
      </div>
      <div class="flex items-center gap-3 self-start sm:self-auto">
        <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 shadow-sm">
          <i data-lucide="calendar" class="w-4 h-4 text-slate-500"></i>
          <span>Maret 2026</span>
        </div>
        <button type="button" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2 rounded-xl text-xs sm:text-sm shadow-md shadow-brand-600/20 transition">
          <i data-lucide="download" class="w-4 h-4"></i> Unduh PDF
        </button>
      </div>
    </div>

    <!-- 4 Card Ringkasan Neraca Kas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Saldo awal</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">Rp 16.655.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center">
          <i data-lucide="wallet-cards" class="w-5 h-5"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Pemasukan</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">Rp 4.275.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Pengeluaran</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">Rp 2.180.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500">Saldo akhir</span>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">Rp 18.750.000</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-5 h-5"></i>
        </div>
      </div>
    </div>

    <!-- Grafik Tren Saldo Kas & Diagram Donut Alokasi Pengeluaran -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="font-extrabold text-slate-900 text-base">Tren saldo kas</h3>
        <p class="text-xs text-slate-500 mb-6">Pergerakan saldo enam bulan terakhir</p>
        
        <!-- Line Chart Vector Representation -->
        <div class="h-60 relative flex flex-col justify-end">
          <svg class="w-full h-44 overflow-visible" viewBox="0 0 500 150">
            <polyline fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"
                      points="20,130 100,105 200,90 300,75 400,60 480,25" />
            <circle cx="20" cy="130" r="5" fill="#fff" stroke="#2563eb" stroke-width="3" />
            <circle cx="100" cy="105" r="5" fill="#fff" stroke="#2563eb" stroke-width="3" />
            <circle cx="200" cy="90" r="5" fill="#fff" stroke="#2563eb" stroke-width="3" />
            <circle cx="300" cy="75" r="5" fill="#fff" stroke="#2563eb" stroke-width="3" />
            <circle cx="400" cy="60" r="5" fill="#fff" stroke="#2563eb" stroke-width="3" />
            <circle cx="480" cy="25" r="5" fill="#fff" stroke="#2563eb" stroke-width="3" />
          </svg>
          <div class="flex justify-between text-xs text-slate-400 font-semibold pt-4 border-t border-slate-100">
            <span>Okt</span><span>Nov</span><span>Des</span><span>Jan</span><span>Feb</span><span>Mar</span>
          </div>
        </div>
      </div>

      <!-- Alokasi Pengeluaran -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="font-extrabold text-slate-900 text-base">Alokasi pengeluaran</h3>
          <p class="text-xs text-slate-500 mb-6">Berdasarkan kategori bulan ini</p>

          <!-- Donut Mockup -->
          <div class="flex items-center justify-center my-4">
            <div class="w-36 h-36 rounded-full border-[18px] border-brand-600 border-t-blue-300 border-r-blue-400 border-l-blue-200 flex flex-col items-center justify-center">
              <span class="text-[11px] text-slate-400 font-bold">Total</span>
              <span class="text-sm font-extrabold text-slate-800">Rp 2,18 jt</span>
            </div>
          </div>
        </div>

        <div class="space-y-2.5 text-xs font-semibold text-slate-700">
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>Kegiatan</span><span class="font-bold">38%</span></div>
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>Konsumsi</span><span class="font-bold">27%</span></div>
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-300"></span>Perlengkapan</span><span class="font-bold">17%</span></div>
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-200"></span>Lainnya</span><span class="font-bold">18%</span></div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>