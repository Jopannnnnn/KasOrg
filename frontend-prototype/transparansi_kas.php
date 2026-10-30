<?php
// transparansi_kas.php
$page_title = "Transparansi Kas - SakuOrg";
$page_heading = "Transparansi Kas";
$user_role = (isset($_GET['view']) && $_GET['view'] === 'anggota') ? 'anggota' : 'admin';

include 'header.php';
include 'sidebar.php';

$riwayat_kas = [
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
    <!-- Banner Saldo Kas Terkini -->
    <div class="bg-brand-600 rounded-3xl p-6 sm:p-8 text-white flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-lg shadow-brand-600/20">
      <div>
        <span class="text-xs font-semibold text-blue-100">Saldo kas terkini</span>
        <div class="text-3xl sm:text-4xl font-extrabold mt-1 tracking-tight">Rp 18.750.000</div>
        <div class="flex items-center gap-1.5 text-xs text-blue-100 mt-2">
          <i data-lucide="clock" class="w-3.5 h-3.5"></i> Diperbarui 18 Maret 2026, 14.30 WIB
        </div>
      </div>
      <div class="flex items-center gap-6 border-t md:border-t-0 md:border-l border-white/20 pt-4 md:pt-0 md:pl-8">
        <div>
          <span class="text-xs text-blue-100 font-medium">Pemasukan</span>
          <div class="text-lg font-extrabold text-white mt-0.5">+ Rp 4,27 jt</div>
        </div>
        <div>
          <span class="text-xs text-blue-100 font-medium">Pengeluaran</span>
          <div class="text-lg font-extrabold text-white mt-0.5">- Rp 2,18 jt</div>
        </div>
      </div>
    </div>

    <!-- Grid Penggunaan Dana & Riwayat Kas Organisasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Donut Penggunaan Dana -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="font-extrabold text-slate-900 text-base">Penggunaan dana</h3>
          <p class="text-xs text-slate-500 mb-6">Alokasi pengeluaran bulan ini</p>

          <div class="flex items-center justify-center my-4">
            <div class="w-40 h-40 rounded-full border-[20px] border-brand-600 border-t-blue-200 border-r-blue-400 border-l-blue-300 flex flex-col items-center justify-center">
              <span class="text-[11px] text-slate-400 font-bold">Total</span>
              <span class="text-base font-extrabold text-slate-800">Rp 2,18 jt</span>
            </div>
          </div>
        </div>

        <div class="space-y-3 text-xs font-semibold text-slate-700">
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>Kegiatan</span><span class="font-bold">38%</span></div>
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>Konsumsi</span><span class="font-bold">27%</span></div>
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-300"></span>Perlengkapan</span><span class="font-bold">17%</span></div>
          <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-200"></span>Lainnya</span><span class="font-bold">18%</span></div>
        </div>
      </div>

      <!-- Riwayat Kas Organisasi -->
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="font-extrabold text-slate-900 text-base">Riwayat kas organisasi</h3>
              <p class="text-xs text-slate-500">Data hanya dapat diubah oleh bendahara</p>
            </div>
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
                <?php foreach($riwayat_kas as $r): ?>
                <tr class="hover:bg-slate-50/70 transition">
                  <td class="py-3.5 px-3 font-medium"><?= $r['tgl'] ?></td>
                  <td class="py-3.5 px-3"><span class="px-2.5 py-1 <?= $r['bg'] ?> <?= $r['color'] ?> font-bold rounded-full text-xs"><?= $r['jenis'] ?></span></td>
                  <td class="py-3.5 px-3 font-semibold"><?= $r['kat'] ?></td>
                  <td class="py-3.5 px-3 text-slate-500"><?= $r['ket'] ?></td>
                  <td class="py-3.5 px-3 text-right font-bold <?= $r['color'] ?>"><?= $r['nominal'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>