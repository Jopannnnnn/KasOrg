# Roadmap 16 Pertemuan — KasOrg

Acuan: `Modul-01-Kontrak-Kuliah.pdf`, halaman 2–3. Milestone adalah rencana pembelajaran, bukan jaminan semua tugas sudah selesai.

| Minggu | Fokus dosen | Bukti KasOrg yang perlu ada |
|---|---|---|
| 1 | Kontrak dan ide | Project Charter; problem statement, target pengguna, scope, GitHub |
| 2 | Requirements + UI/UX | 8–12 FR, NFR, role matrix, MoSCoW, flow, page inventory, low-fi Figma, revisi charter |
| 3 | Backend + Blade | Laravel MVC, route, Blade layout, komponen |
| 4 | Login + role | Auth session, akun dua role, proteksi akses |
| 5 | MySQL + CRUD | Migration, seeder, model/relasi, CRUD inti |
| 6 | API sederhana | API JSON, dokumentasi & Postman |
| 7 | Validation + Integrasi | Validasi bahasa Indonesia, error handling, fitur end-to-end |
| 8 | UTS | MVP Review, laporan, PPT template, tag `mvp-rc`, demo live |
| 9 | Security dasar | Checklist keamanan, otorisasi, CSRF, file upload |
| 10 | Security testing | Laporan uji keamanan |
| 11 | Performance dasar | Pagination, query optimization |
| 12 | Persiapan deploy | Deployment plan |
| 13 | Deployment | Aplikasi online |
| 14 | Testing + bug fix | Test report dan daftar bug |
| 15 | Finalisasi | Release candidate final |
| 16 | UAS | Demo final |

## Urutan kerja saat ini berdasarkan progres yang dilaporkan

1. Validasi visual/interaksi Blade terhadap frontend PHP asli Refan.
2. Pastikan kesesuaian penggunaan **Bootstrap 5**; lakukan migrasi bertahap dan uji regresi UI.
3. Finalisasi kontrak data `database/sakuorg.sql` versus migration Laravel dan identifikasi database yang benar-benar digunakan tim. Jangan jalankan SQL destruktif tanpa izin.
4. Real auth bendahara/anggota dengan 403.
5. CRUD anggota, jenis iuran, tagihan, transaksi; dashboard/laporan dari MySQL.
6. Pembayaran tunai dan transfer manual serta verifikasi bendahara **jika disetujui dalam scope**, tanpa menduplikasi pemasukan.
7. API JSON + Postman, validasi, error pages, feature tests, dokumentasi, demo, evidence Git, release `mvp-rc`.
8. Setelah MVP/UTS, lanjut keamanan, performa, deploy, test, UAS.

**Penting:** pekerjaan UI tidak boleh menyita semua waktu saat login, DB, CRUD, API, dan Postman yang punya bobot UTS besar belum berjalan.
