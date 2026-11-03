# Prompt pertama untuk Codex sesudah konteks dipasang

Tempel pesan ini ke chat Codex yang membuka workspace `C:\xampp\htdocs\KasOrg`:

> Baca `AGENTS.md` terlebih dahulu, lalu baca empat PDF dalam `docs/referensi-dosen/`, `docs/KASORG_UTS_CHECKLIST.md`, `docs/KASORG_ROADMAP_16_MINGGU.md`, dan `docs/KASORG_SCOPE_PEMBAYARAN.md`.

Lakukan pengecekan read-only pada `git status`, struktur Laravel, `resources/views`, frontend asli Refan di `bootstrap/*.php` (kecuali folder `bootstrap/cache`), dan `database/sakuorg.sql`.

Tuliskan ringkasan: (1) aturan wajib UTS, (2) scope yang masih perlu disepakati, (3) status project saat ini berdasarkan bukti aktual, (4) prioritas task berikutnya beserta alasan, dan (5) potensi konflik Bootstrap/Tailwind serta SQL/migration.

Jangan mengubah file, database, branch, `.env`, atau menjalankan migrate/seeder/import SQL; jangan commit/push sebelum instruksi implementasi berikutnya. Jangan menyatakan fitur selesai tanpa test nyata.

Jika Codex tidak dapat membaca PDF, minta ia membaca dokumen ringkasan Markdown ini, lalu lampirkan atau beri akses ke PDF. Jangan menebak isi dokumen yang tidak terbaca.
