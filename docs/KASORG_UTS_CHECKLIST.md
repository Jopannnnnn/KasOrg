# Checklist UTS Pemrograman Web Lanjut — KasOrg

Sumber: **Ketentuan-UTS-PWL.pdf**, halaman 1–5. Checklist ini adalah kriteria/bukti yang perlu dikumpulkan; tidak otomatis berarti sudah selesai.

## A. Fitur aplikasi dan repository (wajib)

- [ ] Login dan logout berjalan dengan password ter-hash.
- [ ] Minimal 2 role (bendahara & anggota) + akses terlarang menghasilkan 403.
- [ ] MySQL, migrations, seeder, dan minimal 1 relasi model yang dipakai pada CRUD.
- [ ] CRUD data inti lengkap dan menampilkan nama relasi, bukan ID saja.
- [ ] Minimal 1 API endpoint JSON dengan kode HTTP benar.
- [ ] Postman collection + hasil pengujian.
- [ ] Form Request/server validation berbahasa Indonesia.
- [ ] Custom error pages 403 / 404 / 500.
- [ ] Alur end-to-end terintegrasi dan bisa didemokan langsung.
- [ ] `README.md`: instalasi fresh clone, setup environment aman, akun demo per role, daftar fitur.
- [ ] Branch/commit/PR masing-masing anggota terbukti.
- [ ] Integrasi ke branch `main` diuji, lalu tag rilis `mvp-rc`.
- [ ] Kredensial `.env`, password, dan token **tidak** masuk repository.

## B. Laporan UTS

- [ ] PDF **15–30 halaman isi** (di luar sampul/awal/lampiran), A4.
- [ ] Margin kiri **3 cm**, kanan/atas/bawah **2,5 cm**.
- [ ] Times New Roman 12 pt, judul bab 14 pt tebal, spasi 1,5, rata kiri-kanan.
- [ ] Gambar dan tabel bernomor, screenshot **asli aplikasi**.
- [ ] Potongan kode kunci maksimal 20 baris per cuplikan, dijelaskan.
- [ ] Minimal 5 referensi APA.
- [ ] BAB I Pendahuluan; BAB II Analisis & Perancangan (role, FR/NFR, flow, wireframe, MVC, ERD, API, validasi).
- [ ] BAB III Implementasi MVP (**Blade + Bootstrap**, auth, DB & CRUD, API, validasi).
- [ ] BAB IV Pengujian & Integrasi (role, Postman, validasi, smoke, bug list).
- [ ] BAB V Manajemen Proyek & Kolaborasi (milestone M1–M3, kontribusi Git, PR, kendala).
- [ ] BAB VI Penutup (kesimpulan dan rencana pertemuan 9–16).
- [ ] Lampiran: URL/tag repo, akun demo, Postman, lembar kontribusi, pernyataan penggunaan AI, video demo cadangan bila dibuat.

## C. Presentasi

- [ ] PPTX **menggunakan template UTS dosen**, maksimal **16 slide**, font minimal 14 pt.
- [ ] Demo 20 menit per kelompok: **7 menit presentasi + 8 menit demo + 5 menit tanya jawab**.
- [ ] Demo nyata: login kedua role -> fitur inti -> validasi data salah -> uji 403 -> API Postman.
- [ ] Semua anggota hadir, bicara, dan dapat menjelaskan kode.

## D. Submission dan nilai

- [ ] Submission ke LMS paling lambat **H-1 pukul 23.59 WIB** (tanggal jadwal UTS spesifik harus dicek dari kelas).
- [ ] Nama file: `UTS_PWL_Kelompok-XX_NamaProject.pdf` dan `.pptx`.
- [ ] Repository URL + tag `mvp-rc` dicantumkan.
- [ ] Bobot rubrik: kesesuaian & laporan **20**; fungsi MVP **30**; teknis & struktur **20**; database/integrasi **10**; Git/kontribusi **10**; presentasi **10**.
- [ ] Jika ada perubahan scope, mintakan persetujuan dosen; penggunaan AI wajib dilaporkan.

## E. Snapshot pelaksanaan (berdasarkan laporan Codex dari pengguna, bukan verifikasi langsung)

- [x] Laravel 12 terpasang, route preview Blade Admin/Anggota/login ada dan respons HTTP 200.
- [x] PHP Refan sudah disalin/konversi ke halaman Blade dengan navigasi preview.
- [ ] Kesesuaian pixel dan interaksi browser seluruh halaman belum diverifikasi menyeluruh.
- [ ] Bootstrap 5 belum menjadi framework CSS utama (banyak halaman masih Tailwind CDN).
- [ ] Login asli, migration MySQL, CRUD, API, verifikasi pembayaran backend belum dinyatakan selesai.

Perbarui checklist dari bukti hasil testing, bukan dari asumsi.
