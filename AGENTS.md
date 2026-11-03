# AGENTS.md — Pedoman Codex untuk KasOrg

> BERLAKU untuk pekerjaan di repository **KasOrg — Sistem Kas Organisasi**. Baca dokumen dosen dan cek keadaan kode nyata sebelum memutuskan implementasi. Instruksi ini adalah panduan proyek, bukan bukti bahwa fitur sudah selesai.

## Sumber acuan dan urutan prioritas

1. `docs/referensi-dosen/Ketentuan-UTS-PWL.pdf` — **syarat penilaian dan deliverable UTS**.
2. `docs/referensi-dosen/Modul-01-Kontrak-Kuliah.pdf` — **roadmap 16 pertemuan, teknologi dan pembagian kerja**.
3. `docs/referensi-dosen/Modul-02-Requirements-UIUX.pdf` — **FR/NFR, role, scope MoSCoW, user flow, page inventory, wireframe**.
4. `docs/referensi-dosen/Requirement-KasOrg-Kelompok4-DRAFT.pdf` — **rancangan khusus KasOrg, masih DRAF/harus direkonsiliasi dengan kesepakatan kelompok dan dosen**.
5. Kode aktual repository dan keputusan terbaru yang disetujui kelompok.

**Jika konflik:** jangan diam-diam memilih; jelaskan konflik, tunjukkan sumber dan minta keputusan tim/dosen jika mengubah scope. Soal UTS lebih tinggi daripada draf kebutuhan KasOrg. Judul/ruang lingkup tidak boleh diganti tanpa persetujuan dosen.

## Konteks teknis

- Proyek Laravel 12 / PHP >= 8.2, arsitektur MVC, Blade, MySQL/MariaDB.
- Modul dosen menyebut **Bootstrap** dan laporan UTS menilai **layout Blade + Bootstrap**. Kode frontend saat ini masih banyak menggunakan Tailwind; rencanakan migrasi Bootstrap 5 + CSS custom **bertahap setelah audit visual**; jangan ubah desain tanpa pembandingan.
- **Acuan visual utama UI**: file frontend PHP Refan di `bootstrap/*.php` (bukan `bootstrap/cache`, yang merupakan cache internal Laravel). Jangan hapus/ubah file sumber Refan tanpa persetujuan.
- Halaman frontend Laravel hasil konversi berada di `resources/views/` dengan route preview `ui-preview.*` **hanya untuk local**. Status HTTP 200 / data dummy **tidak sama dengan** backend siap produksi.
- Branding final **KasOrg**; dua role sistem yang direncanakan **bendahara** dan **anggota**. Sebutan UI Admin/Bendahara adalah nama tampilan untuk role bendahara, bukan role ketiga.
- Git repository kelompok; riwayat branch/commit/PR per anggota adalah bukti penilaian.

## Minimum wajib UTS — jangan dinyatakan selesai tanpa bukti

- Login, logout, **minimal dua role**, pembatasan akses yang tidak berhak mengembalikan **403**.
- **MySQL dengan migration, seeder dan minimal satu relasi** antartabel.
- **CRUD inti** yang memperlihatkan data relasi yang manusiawi (nama, bukan ID mentah).
- **Minimal satu endpoint API JSON** dengan status code benar dan **collection Postman** yang diuji.
- **Validasi server-side** + error berbahasa Indonesia, halaman **403, 404, 500 kustom**.
- Alur inti berjalan end-to-end di **main**; tag rilis **`mvp-rc`**; README instalasi dari nol + akun demo tiap role + fitur.
- Dokumentasikan bukti kontribusi **commit, branch, Pull Request** dari setiap anggota.
- Laporan UTS PDF 15–30 halaman isi; slide maksimal 16, wajib template dosen. Demo aplikasi **langsung berjalan** dari tag `mvp-rc`; screenshot bukan pengganti.
- Dilarang commit `.env`, password, token atau data rahasia; penggunaan AI dicantumkan pada lampiran laporan dan kode harus bisa dijelaskan oleh anggota.

Lihat `docs/KASORG_UTS_CHECKLIST.md` dan `docs/KASORG_ROADMAP_16_MINGGU.md` untuk rincian dan fase.

## Fitur KasOrg dan aturan keuangan

- Modul inti: akun anggota, jenis iuran, tagihan, pembayaran, transaksi pemasukan/pengeluaran, dashboard, laporan, transparansi.
- **Fitur pembayaran dua metode** adalah kebutuhan yang dijelaskan pemilik proyek, tetapi belum masuk ke FR draf Kelompok 4 secara lengkap: **minta konfirmasi kelompok/dosen jika menjadi perubahan scope MVP** dan revisi dokumen kebutuhan sebelum klaim final.
- Tunai: anggota mengajukan / bendahara mencatat penerimaan nyata. Transfer: anggota mengunggah bukti, bendahara mencocokkan dengan mutasi rekening **secara manual**.
- Pengajuan `pending` dan `rejected` **tidak menambah saldo**. Hanya pembayaran `verified` yang membuat **satu** transaksi pemasukan terhubung; lakukan secara atomik dan idempoten dengan constraint unik dan/atau row locking. Jangan ada API bank otomatis atau payment gateway.
- Screenshot bukti transfer disimpan **privat**, terotorisasi untuk pemilik dan bendahara. Validasi server-side file, nominal, status, kepemilikan.
- Pisahkan konsep `jenis_iuran` (aturan), `tagihan_iuran` (kewajiban), `pembayaran_iuran` (pengajuan/verifikasi), `transaksi` (kas sah). Evaluasi skema nyata sebelum migrasi.

## Aturan kerja aman bagi Codex

1. Awali tugas dengan membaca file referensi yang relevan dan melihat kode aktual (`git status`, route, model, Blade, migration). Jangan menyimpulkan dari chat terdahulu saja.
2. Untuk setiap pekerjaan: beri rencana singkat, daftar file yang akan disentuh, batasan, lalu verifikasi perubahan dan laporkan apa yang **sudah/belum diuji**.
3. **Jangan** membuat proyek Laravel baru, mengganti branch, reset hard, commit/push/merge otomatis, menimpa frontend Refan, atau mengubah data tim tanpa permintaan jelas.
4. **Jangan** menjalankan `migrate`, `migrate:fresh`, `db:seed`, SQL import (`sakuorg.sql` mengandung DDL destruktif), maupun `composer run setup` tanpa persetujuan dan backup/schema review. Jangan tampilkan/rotasi `APP_KEY`.
5. `bootstrap/cache/packages.php` & `services.php` adalah cache Laravel auto-generated; bukan tempat mendesain UI.
6. Jangan memalsukan auth/otorisasi dengan URL `?role=` atau `?view=`. Preview lokal boleh memakai mock, tapi labelnya jelas bukan autentikasi final.
7. Jangan menghapus Tailwind dari halaman yang belum dikonversi sampai Bootstrap + CSS custom terbukti meng-cover visualnya. Periksa komponen lintas-layout dan responsif.
8. Uji sesuai scope: `route:list`, `view:cache`, lint, tests, interaksi browser bila bisa. HTTP 200 tidak membuktikan visual, CRUD, keamanan, atau database berhasil.
9. Jika kekurangan Node.js/browser/MySQL/akses repo: jelaskan blocker, jangan klaim lulus test yang tidak dijalankan.
10. Saat user meminta fitur baru, petakan ke FR/NFR, role, acceptance criteria, skema, route, test, bukti laporan; tempatkan di MUST/SHOULD/COULD/OUT sesuai persetujuan.

## Cara memulai setiap sesi Codex

Baca `AGENTS.md`, keempat PDF `docs/referensi-dosen/`, dan `docs/KASORG_UTS_CHECKLIST.md`. Kemudian cek `git status`, identifikasi fase saat ini, dan kerjakan **hanya fase yang diminta pengguna**. Jika belum ada instruksi implementasi, laporkan rencana saja.
