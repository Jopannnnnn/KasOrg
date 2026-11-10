# API JSON KasOrg — Panduan Postman UTS

Status proyek: **DALAM PENGEMBANGAN — BELUM FINAL**. Panduan ini ditujukan untuk anggota kelompok yang menjalankan KasOrg pada database lokal masing-masing, bukan database integrator.

Paket utama yang perlu dibagikan hanya:

- [KasOrg-UTS.postman_collection.json](KasOrg-UTS.postman_collection.json) — Postman Collection v2.1, berisi 13 request.
- [KasOrg-Local.example.postman_environment.json](KasOrg-Local.example.postman_environment.json) — template environment tanpa kredensial.
- README Postman ini.

Workspace YAML tidak diperlukan dan **bukan bagian paket utama**. Tidak perlu mengunduh resource cloud, memasukkan YAML, atau memakai runner Manual untuk mengimpor dua JSON di atas.

Panduan berlaku pada revisi branch `jovan/integrasi-laravel` yang sudah memuat **seluruh paket API**. Checkpoint dasar `2e001ba` hanya memuat web dan dokumentasi sebelumnya; API belum tersedia pada checkpoint dasar itu. Periksa pendaftaran route pada langkah persiapan, bukan menganggap halaman login yang terbuka membuktikan API sudah terintegrasi.

## Scope dan keamanan

Rangkaian 13 request ini menggunakan **akun Admin**, sesuai target login dan GET `/admin/dashboard`. Endpoint API tetap mendukung role Anggota sesuai backend; sinkronisasi ini tidak mengubah hak akses aplikasi.

Hanya GET data organisasi serta POST login/logout session Laravel. Tidak ada request CRUD, migration, seeder, perubahan status, atau penulisan data keuangan. CSRF dan autentikasi tetap aktif. Session/cookie teknis mengikuti konfigurasi Laravel yang sudah ada.

Jangan membagikan password, cookie, atau token aktif. Isi kredensial hanya pada environment privat/lokal. Penandaan variabel sebagai `secret` hanya menyamarkan tampilan; jangan mengekspor environment berisi rahasia ke repository atau menyinkronkannya ke workspace kelompok.

## Persiapan fresh clone — XAMPP atau Laragon

Ikuti [tutorial XAMPP](../README.md#xampp) atau [tutorial Laragon](../README.md#laragon) pada README utama untuk detail pemasangan alat dan penanganan error. Pilih satu lingkungan; jangan membuat proyek Laravel baru atau menyalin `.env` integrator. Perintah berikut adalah **panduan untuk dijalankan pada instalasi sendiri**, bukan perintah yang dieksekusi saat memperbarui dokumen ini.

1. **Siapkan source dan dependency.** Clone branch `jovan/integrasi-laravel` ke folder baru, misalnya `C:\xampp\htdocs\KasOrg` atau `C:\laragon\www\KasOrg`. Buka folder melalui VS Code, pilih **Terminal → New Terminal → PowerShell**, lalu pastikan terminal berada di root yang berisi `artisan`, bukan di folder `postman/`. PHP harus memenuhi Composer lockfile, termasuk ekstensi `pdo_mysql` dan BCMath untuk KasOrg. Node/npm dipakai untuk build Vite, bukan menjalankan database. Jalankan berurutan; berhenti jika ada error:

   ```powershell
   composer install
   composer check-platform-reqs
   ```

2. **Buat konfigurasi privat hanya jika instalasi baru.** Salin template tanpa menimpa file yang sudah ada:

   ```powershell
   if (Test-Path -LiteralPath '.env') { throw '.env sudah ada; tinjau alur instalasi existing pada README utama.' }
   Copy-Item -LiteralPath '.env.example' -Destination '.env'
   ```

3. **Nyalakan MySQL/MariaDB lokal dan buat database baru kosong**, misalnya `kasorg_local`, melalui pengelola database sendiri. XAMPP dapat memakai phpMyAdmin dengan Apache aktif; Laragon memakai pengelola yang benar-benar tersedia pada instalasinya. Konfirmasi host/port/akun server, jangan mengasumsikan root tanpa password. Jika target sudah berisi tabel/data, **STOP**; jangan hapus atau reset. Jangan menggunakan `kasorg_dev` integrator, mengimpor SQL historis, atau menyalin database anggota lain.

4. **Isi `.env` lokal secara privat.** `APP_ENV=local`, `APP_DEBUG=false`, `APP_URL=http://127.0.0.1:8000`, `DB_CONNECTION=mysql`, dan `DB_URL` kosong. Isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, serta `DB_PASSWORD` sesuai server sendiri; nama contoh database adalah `kasorg_local`. Gunakan `SESSION_DRIVER=file`, `CACHE_STORE=file`, dan `QUEUE_CONNECTION=sync` sesuai template. Isi **kedua** variabel `KASORG_DEMO_ADMIN_PASSWORD` dan `KASORG_DEMO_ANGGOTA_PASSWORD` dengan nilai privat yang berbeda, masing-masing minimal 8 karakter. Tidak ada password demo publik/bawaan; jangan menuliskan nilainya pada JSON contoh atau argumen command line.

5. **Buat key, tabel, lalu akun demo hanya pada instalasi baru yang targetnya sudah diverifikasi.** `APP_KEY` awalnya kosong dan dibuat sekali. Jalankan satu per satu; jangan lanjut seed bila migration gagal:

   ```powershell
   php artisan key:generate
   php artisan config:clear
   php artisan migrate --database=mysql
   php artisan db:seed --database=mysql
   ```

   Migration dan seeder menulis database. Jangan menjalankannya pada database existing hanya untuk mencoba Postman. Seeder membuat Admin `admin@kasorg.test` dan Anggota `anggota@kasorg.test`; password berasal dari konfigurasi privat saat akun dibuat. Mengganti variabel password kemudian tidak mereset akun existing. Jika seed gagal/parsial, tinjau penyebab bersama kelompok, bukan menjalankan reset atau seed ulang tanpa review.

6. **Bangun aset dan jalankan Laravel.** Setelah dependency npm berhasil, jalankan build; kemudian biarkan terminal server tetap terbuka:

   ```powershell
   npm.cmd ci
   npm.cmd run build
   php artisan serve --host=127.0.0.1 --port=8000
   ```

   Buka `http://127.0.0.1:8000/login` untuk memastikan aplikasi dapat diakses. MySQL tetap menyala; Apache/Nginx tidak wajib untuk server Artisan. Port dapat disesuaikan bersama `APP_URL` dan `base_url` Postman. Login browser **tidak** otomatis membuat sesi login di Postman.

7. **Periksa API pada terminal kedua di root proyek:**

   ```powershell
   php artisan route:list --path=api
   ```

   Harus ada `GET|HEAD api/kategori-transaksi` dan `GET|HEAD api/transparansi/ringkasan`. Keduanya memakai session Laravel, `auth:web`, akun aktif dan role Admin/Anggota; bukan endpoint publik atau Bearer Token/Sanctum. Jika belum terdaftar, periksa revisi dan kelengkapan paket tujuh path API; jangan membuat route dummy atau menjalankan migration untuk mengatasi source yang belum terintegrasi.

**Instalasi existing:** pertahankan `.env`, APP_KEY, akun dan data yang sudah benar. Cukup jalankan layanan database/server yang tepat dan periksa route; tidak perlu migration/seeder/key baru untuk menjalankan collection. Lihat [alur existing](../README.md#existing). Angka nol pada database baru valid: jangan menambahkan transaksi palsu agar pengujian saldo terlihat berisi.

## Impor ulang dan jalankan

1. Pastikan persiapan di atas selesai: Laravel dan server MySQL/MariaDB lokal aktif, database milik penguji sudah disiapkan, serta kedua endpoint terdaftar. Nama database boleh berbeda antarlaptop; `base_url` menunjuk server Laravel, **bukan** nama database atau URL phpMyAdmin. Menjalankan collection tidak memerlukan migration/seeder ulang.
2. Bila server belum berjalan, jalankan `php artisan serve --host=127.0.0.1 --port=8000`.
3. Impor file collection JSON yang sudah diperbarui dan environment contoh. Bila tersedia pilihan **Replace/Update**, perbarui collection lama; jangan menjalankan salinan lama yang masih memakai script sebelum sinkronisasi. Pastikan tidak ada duplikat collection yang membingungkan.
4. Pilih environment **KasOrg Local — contoh tanpa kredensial**. Isi `email` dan `password` akun **Admin** pada nilai lokal/privat saja.
5. Isi `base_url` sesuai server, misalnya `http://127.0.0.1:8000`, tanpa slash terakhir. Jangan mencampur `localhost` dengan `127.0.0.1`, karena cookie mengikuti host.
6. `periode_id` boleh kosong (periode default), ID periode yang tersedia, atau `semua`. Test 07 dan 08 menggunakan nilai yang sama.
7. Bila run sebelumnya berhenti setelah login, hapus cookie **hanya host development tersebut** melalui Cookies Postman sebelum memulai kembali. Test 01–02 sengaja menonaktifkan cookie jar per-request, tetapi tidak menghapus sesi lama yang mungkin masih tersimpan untuk Test 03. GET login yang menghasilkan 302 menandakan sesi lama masih aktif.
8. Jalankan **seluruh collection berurutan 01–13**, satu iteration, melalui Collection Runner. Jangan memakai iteration data CSV/JSON untuk menimpa variabel environment.
9. Pertahankan **Automatically follow redirects = OFF**. Jangan mengubah expected login/logout menjadi 200. Cookie jar harus aktif pada Test 03–13; terutama Test 13 harus memakai cookie hasil logout, bukan sengaja mengabaikan cookie agar terlihat 401.
10. Target run Admin saat ini: **13 request, 51 assertions, 0 gagal**. Jumlah assertions bertambah dari 50 karena token CSRF sesi aktif pada Dashboard kini diuji secara eksplisit.

Pengaturan redirect/cookie sudah tersimpan di collection. Bila GUI menimpanya, periksa Settings request. Panduan resmi: [redirect](https://learning.postman.com/docs/getting-started/installation/settings/general-settings), [cookie jar](https://learning.postman.com/docs/use/send-requests/response-data/cookies).

## Urutan 13 request

| Nomor | Request | Harapan |
|---|---|---|
| 01 | GET kategori tanpa cookie | 401 JSON; success false |
| 02 | GET ringkasan tanpa cookie | 401 JSON |
| 03 | GET login, ambil CSRF dari form HTML | 200 |
| 04 | POST login, form-urlencoded email/password/CSRF | 302 ke `/admin/dashboard` |
| 05 | GET kategori asli | 200; ID, nama, jenis |
| 06 | GET kategori dengan `jenis=pengeluaran` | 200; hanya pengeluaran |
| 07 | GET ringkasan dengan `periode_id` terpilih | 200; rumus desimal dan total grafik benar |
| 08 | GET Dashboard Admin dengan periode yang sama | 200; saldo sama dengan API; ambil CSRF sesi aktif |
| 09 | GET kategori dengan `jenis=tidak-valid` | 422; error jenis |
| 10 | GET ringkasan dengan `periode_id=abc` | 422; error periode |
| 11 | GET endpoint tidak ditemukan | 404 JSON |
| 12 | POST logout dengan CSRF sesi aktif | 302 ke `/login` |
| 13 | GET kategori setelah logout | 401; success false, pesan login, data null |

Setiap respons API juga diuji Content-Type JSON, envelope konsisten, dan ketiadaan field privat. Test 13 memiliki 4 hasil tes termasuk 3 tes collection bersama; pemeriksaan status, success, pesan login, dan data null berada pada assertion sesi berakhir.

## Alur CSRF, saldo, dan scope variabel

| Variabel environment | Sumber / penggunaan |
|---|---|
| `base_url` | Diisi lokal; semua URL request memakai variabel ini |
| `email`, `password` | Diisi privat; hanya dikirim pada POST login |
| `periode_id` | Diisi lokal; dipakai bersama pada Test 07 dan 08 |
| `csrf_token` | Test 03 membaca form login; Test 08 menggantinya dengan token form logout/meta pada sesi aktif; Test 12 mengirim lalu menghapus nilai runtime |
| `api_saldo_akhir` | Test 07 menyimpan string desimal respons API; Test 08 membacanya untuk perbandingan |

Token dibaca memakai parser HTML `cheerio` bawaan sandbox Postman, bukan regex dengan urutan atribut tetap. Token tidak dibuat sendiri. Bila tidak ditemukan, assertion gagal dengan pesan yang jelas dan token lama dibuang.

Saldo Dashboard diambil dari kartu berlabel **Total saldo kas**, sesuai Blade aktual. Rupiah dinormalisasi menjadi sen memakai `BigInt`: mendukung nol, pemisah ribuan, spasi biasa/nonbreaking, dan nominal negatif. Tidak memakai float, angka mock, atau sekadar memeriksa HTTP 200. Rumus dan assertion grafik asli Test 07 tetap dipertahankan.

Semua nilai lintas-request ditulis/dibaca menggunakan `pm.environment`. Pre-request collection membuang sisa local override dari collection lama dan menolak konflik scope, termasuk iteration data. Tidak ada `dashboard_url` dinamis atau `pm.variables.set()` yang dapat menutupi token/saldo terbaru. Lihat [prioritas scope resmi Postman](https://learning.postman.com/latest-v-12/docs/tests-and-scripts/write-scripts/postman-sandbox-reference/pm-variables).

Environment JSON contoh yang dibagikan tetap mengosongkan email, password, CSRF, dan saldo hasil run. Jangan menyalin nilai runtime ke file contoh. Email/password hanya dikirim oleh request login; setelah itu Postman memakai cookie sesi Laravel. Token 03 berasal dari sesi sebelum login, sedangkan token 08 adalah token sesi aktif untuk logout. CSRF tidak boleh dinonaktifkan untuk membuat collection lulus.

### Diagnosis singkat

| Gejala | Pemeriksaan yang aman |
| --- | --- |
| Connection refused | Server Artisan/port belum benar; periksa `base_url` dan terminal server. |
| API 404 | Periksa revisi dan `route:list --path=api`; endpoint mungkin belum terintegrasi. Request 11 memang menguji 404. |
| Login 302 langsung saat request 03 | Ada sesi lama pada cookie jar host tersebut; selesaikan logout atau bersihkan cookie host pengujian itu saja. |
| Login gagal | Periksa akun demo pada database sendiri dan password privat saat seed pertama. Jangan reset database/seed ulang sebagai tebakan. |
| 401 setelah seharusnya login | Periksa cookie jar, host/protokol dan keberhasilan request 04; browser login tidak berbagi sesi dengan Postman. |
| 403 | Periksa role/status akun. Collection 13 request memakai Admin; role Anggota tidak boleh mengakses Dashboard Admin. |
| Logout 419 | Jalankan request 08 untuk memperbarui token sesi aktif, lalu 12; pertahankan cookie jar dan CSRF. |
| 422 | Baca field `errors`; kasus 09–10 memang mengharapkan parameter ditolak. |
| Saldo nol | Bisa benar pada database baru. Bandingkan periode yang sama pada request 07–08, bukan mengisi data mock. |
| 500 | Baca log privat pada mesin sendiri bersama pengelola; jangan membagikan stack trace, SQL, kredensial atau mengaktifkan debug pada demo terbuka. |

## Opsi CLI

Jika Postman CLI tersedia, gunakan environment privat **di luar repository**:

```powershell
postman collection run postman/KasOrg-UTS.postman_collection.json --environment C:\lokasi-privat\kasorg-local.json --ignore-redirects --no-report-events
```

Jangan memasukkan password pada argumen command line. Tidak perlu memasang dependency aplikasi baru. [Dokumentasi CLI resmi](https://learning.postman.com/docs/postman-cli/postman-cli-collections).

Tidak perlu menjalankan `tests/Manual/postman-api-smoke.php`, runner concurrency, atau mode `KASORG_*_MARIADB` untuk mengikuti panduan ini. Runner historis terikat database/alat integrator; runner Postman lama juga menargetkan dua role, sedangkan collection sekarang memerlukan Dashboard Admin. File-file itu bukan bagian paket utama dan tidak menjadi langkah setup anggota. Gunakan impor JSON melalui GUI atau CLI resmi di atas dengan environment privat.

## Bukti sinkronisasi — 7 November 2026

- Pengujian manual GUI atas perbaikan sebelumnya: **dilaporkan oleh pengguna**, bukan diulang atau diasumsikan sebagai hasil GUI pada tahap ini.
- File JSON utama benar-benar dijalankan memakai CLI bawaan Postman desktop 12.35.5 terhadap HTTP Laravel dan MariaDB asli: **13 request / 51 assertions / 0 gagal**, exit code 0.
- Pengujian menggunakan server lokal sementara port 8017 dengan koneksi MariaDB dipaksa **READ ONLY**; seluruh middleware, CSRF, Auth, dan data nyata tetap dipakai. Fingerprint **16 tabel database identik** sebelum/sesudah.
- Kredensial diambil dari konfigurasi demo lokal yang sudah ada, diverifikasi terhadap hash akun Admin, tidak dicetak, dan file environment privat sementara dibersihkan.
- JSON valid; semua 18 blok script berhasil dikompilasi sebagai JavaScript. Uji parser uang: **23 kasus** termasuk nol, ribuan, negatif, nominal besar, format tidak valid, dan beda satu sen.
- CLI tanpa autentikasi cloud menampilkan pemberitahuan bahwa publikasi hasil memerlukan `postman login`. Pengujian Laravel tetap berhasil; tidak dilakukan login atau publikasi ke cloud.
- GUI Postman tidak dibuka/dijalankan ulang pada tahap sinkronisasi ini. Bukti screenshot Runner GUI tetap perlu diambil manual tanpa memperlihatkan rahasia.

Catatan 7 November adalah **bukti historis pada lingkungan integrator**, bukan bukti fresh clone MySQL milik anggota sudah lulus. Panduan instalasi sekarang memakai database lokal masing-masing; jangan mengulang pengujian historis pada database integrator.

## Bukti paket API terisolasi — 9 November 2026

Pengujian berikut telah dilakukan pada salinan terpisah berisi checkpoint dasar `2e001ba` dan paket API/Postman lokal, menggunakan **SQLite baru**, bukan MySQL/MariaDB aktif. Source API dan kedua JSON yang diuji tetap sama pada pembaruan panduan ini. Test API/helper disalin khusus ke lingkungan pengujian, **bukan ditambahkan ke paket distribusi sepuluh path**.

| Pemeriksaan sebelumnya | Hasil dan batas bukti |
| --- | --- |
| Composer install | Berhasil dari lockfile: 118 instalasi, tanpa update dependency. |
| Migration/seeder | 11 migration asli dan seeder dua akun demo berhasil pada SQLite terisolasi. |
| Test API terpilih | 15 tes / 148 assertions lulus pada SQLite RAM; meliputi validasi, session/CSRF, role, privasi, error dan konsistensi saldo desimal nonnol. Bukan seluruh suite test lama. |
| Collection JSON melalui CLI | **13 request / 51 assertions / 0 gagal**, exit 0, melalui HTTP Laravel dan SQLite hasil migration/seeder. Saldo awal nol tetap diuji sebagai nol. |
| HTTP tambahan | 20/20 lulus untuk Admin/Anggota: login, CSRF, dashboard, akses Anggota ke Admin 403, API, validasi periode dan logout. |
| Integritas data | Fingerprint delapan tabel domain identik sebelum/sesudah pengujian HTTP; tidak ada pelanggaran foreign key SQLite. |

Percobaan CLI pertama terhenti karena direktori kerja server pengujian keliru, bukan karena assertion dihapus atau source API diperbaiki. Setelah konfigurasi proses server diperbaiki, pengujian ulang lengkap di atas lulus. Pemberitahuan CLI tentang `postman login` berkaitan dengan publikasi hasil ke cloud, bukan login session Laravel; tidak dilakukan login/publikasi cloud.

**Belum dinyatakan lulus:** fresh clone pada laptop anggota, migration/perilaku MySQL/MariaDB pada laptop itu, Postman GUI terbaru di laptop anggota, serta seluruh alur modul keuangan. Keberhasilan SQLite tidak membuktikan perilaku DECIMAL/ENUM/locking MySQL. Anggota perlu menjalankan 13 request sendiri, mencatat revisi dan hasil Runner, lalu membagikan bukti tersensor tanpa password/cookie/token. HTTP 200 saja bukan bukti seluruh aplikasi siap UTS.

Pembaruan panduan ini hanya mengedit `postman/README.md`; tidak menjalankan ulang instalasi, build, migration/seeder, Postman, browser atau test database. Tidak ada perubahan source API, JSON collection/environment, README utama, SQL, `.env` aktif, database integrator, staging, commit atau push.
