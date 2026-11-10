# KasOrg — Sistem Kas Organisasi Mahasiswa

KasOrg membantu organisasi mahasiswa mengelola anggota, iuran, pengajuan pembayaran, verifikasi manual, dan pemasukan/pengeluaran hingga laporan keuangan. Proyek Kelompok 4 mata kuliah Pemrograman Web Lanjut ini menggunakan Laravel 12. Status: **DALAM PENGEMBANGAN — BELUM FINAL**, bukan rilis production.

KasOrg mengurangi pencatatan anggota/iuran yang tersebar, kesulitan memeriksa pembayaran, dan perbedaan informasi saldo organisasi. **Admin** mengelola data serta menjalankan tanggung jawab bendahara; **Anggota** melihat kewajiban sendiri dan informasi keuangan yang diizinkan. Arsitektur MVC memisahkan pengolahan request, aturan/data, dan tampilan agar mudah diuji serta dijelaskan saat UTS.

**Atribusi:** Refan merancang dan mengembangkan prototype frontend PHP native; Kent merancang database awal; Jovan mengintegrasikan frontend, database, dan backend ke Laravel. Prototype di `bootstrap/*.php` tetap menjadi acuan desain. Adaptasi Laravel/Bootstrap tidak menghapus hak desain atau menjadikannya desain baru integrator. Kontribusi anggota lain mengikuti pekerjaan serta bukti Git kelompok yang sebenarnya, bukan diasumsikan oleh README ini.

> **Checkpoint 9 November 2026:** branch lokal dan GitHub `jovan/integrasi-laravel` berada di **`ad03cc52e21ee8d2a0a7fa0f70071d36024b91fc`**. Paket portal 34 path pada `15e32bc`, pembaruan README pada `2e001ba`, dan paket API/Postman **10 path (9 A, 1 M)** pada `ad03cc5` sudah dipush. Fresh clone branch ini memperoleh source web dan API, collection JSON, environment contoh serta panduan Postman. Bukti pengujian portal dan API memakai SQLite terisolasi, **bukan** fresh clone MySQL/MariaDB atau Postman GUI di laptop teman. Status tetap **DALAM PENGEMBANGAN — BELUM FINAL**.

<a id="mulai-cepat"></a>

## MULAI CEPAT — JALANKAN KASORG DARI CLONE

Pilih satu jalur. Panduan ini untuk **dibaca lalu dilakukan secara manual di komputer sendiri**, bukan perintah yang dijalankan saat penyusunan README.

| Kondisi saya | Mulai dari sini |
| --- | --- |
| Memakai XAMPP, belum ada instalasi/database | [Tutorial XAMPP dari clone sampai login](#xampp). |
| Memakai Laragon, belum ada instalasi/database | [Tutorial Laragon dari clone sampai login](#laragon). |
| Sudah punya database KasOrg dan konfigurasi lokal | [Menjalankan instalasi existing](#existing); jangan generate key atau seed ulang. |
| Ingin mencoba API JSON | Setelah instalasi lokal siap, impor dua JSON dari repository dan ikuti [tutorial API/Postman](#postman) serta [panduan Runner](postman/README.md). |
| Ingin memahami kode untuk presentasi | [Peta file](#peta-file) → [empat alur MVC](#alur-kode) → [cara belajar UTS](#belajar-uts). |

Urutan instalasi baru: **buka VS Code → Terminal PowerShell → pilih folder XAMPP/Laragon → clone branch → buka folder KasOrg → Composer → konfigurasi privat → database MySQL baru kosong → APP_KEY baru → migration/seeder → npm ci + build Vite → server → login dua role → uji CRUD Anggota → impor collection dan environment Postman → jalankan 13 request**. Tutorial XAMPP/Laragon menjelaskan tujuan, hasil yang diharapkan dan tindakan saat gagal; perintahnya tidak dijalankan dalam pembaruan dokumentasi ini.

**Batas bukti:** paket portal lulus instalasi dependency/build, 11 migration, seeder dan **45/45 pemeriksaan HTTP/database** dengan SQLite terisolasi. Paket API lulus **15 tes / 148 assertions**, **13 request Postman CLI / 51 assertions**, dan **20/20 pemeriksaan HTTP tambahan**, juga dengan SQLite terisolasi. Kedua tahap ini berbeda; angkanya tidak digabung menjadi hasil regresi penuh. **Fresh clone MySQL/MariaDB, Postman GUI laptop anggota dan seluruh regresi belum dinyatakan lulus**; ikuti [skenario manual](#uji-crud), [panduan Postman](postman/README.md) dan catat hasil sendiri. Modul keuangan masih pengembangan/testing; paket terbaru belum digabung ke main.

## Daftar isi

- [1. Status, teknologi, dan cakupan UTS](#status)
- [2. Persyaratan komputer](#persyaratan)
- [3. Tutorial XAMPP](#xampp), [Laragon](#laragon), dan [perbandingannya](#perbandingan-lokal)
- [4. Menjalankan database yang sudah tersedia](#existing)
- [5. Konfigurasi instalasi baru dan hasil seeder](#instalasi-baru)
- [6. Akun demo dan hak akses](#akun-demo)
- [7. Bootstrap, Tailwind legacy, dan Vite](#aset)
- [8. Struktur folder](#struktur), [peta file](#peta-file), dan [alur kode](#alur-kode)
- [9. Database, relasi, dan arsip Kent](#database)
- [10. API JSON dan Postman 13 request](#postman)
- [11. Hasil pengujian](#hasil-uji), [16 skenario CRUD manual](#uji-crud), [checklist laptop anggota](#checklist-laptop), dan [belajar/demo UTS](#belajar-uts)
- [12. Troubleshooting](#troubleshooting)
- [13. Status GitHub dan kerja kelompok](#status-github)
- [Distribusi paket portal 34 path, API/Postman 10 path, dan file yang masih lokal](#distribusi-checkpoint)
- [14. Batas fitur dan dokumentasi lanjutan](#batas)

<a id="status"></a>

## 1. Status, teknologi, dan cakupan UTS

### Teknologi yang benar-benar digunakan

| Bagian | Kondisi checkpoint branch dan catatan lokal |
| --- | --- |
| Backend | PHP `^8.2`, Laravel `^12.0` (composer.lock: **v12.70.1**), arsitektur MVC dan Eloquent ORM. |
| Template | Blade di `resources/views/`; bukan membuka PHP native langsung melalui browser. |
| Frontend MVP | **Bootstrap 5.3.8** dan CSS khusus KasOrg: Login, error 403/404/500, Dashboard Admin/Anggota, Data Anggota, dan Kelola Jenis Iuran. |
| Frontend tambahan | Tailwind CSS legacy pada halaman yang belum dikonversi; jalur aset dipisahkan dari Bootstrap. |
| Identitas visual | Biru gelap, Plus Jakarta Sans, ikon Lucide, serta komposisi UI Refan. Font dan Lucide masih dari layanan eksternal. |
| Database | MySQL/MariaDB; komputer integrasi memakai **`kasorg_dev` pada MariaDB 10.4 XAMPP**. |
| Aset | Vite, plugin Laravel, Node.js/npm; package-lock.json kini tersedia. Vite terkunci **7.3.7**. |
| PDF | `barryvdh/laravel-dompdf` **v3.1.2** pada composer.lock untuk unduhan laporan keuangan. |
| Pengujian | PHPUnit **11.5.57** tersedia sebagai dependency. Portal dan API diuji terpisah memakai SQLite terisolasi. Collection JSON, environment contoh dan README Postman sudah ikut clone; suite regresi lengkap/helper/runner frontend belum ikut. |

Versi tersebut dibaca dari source/lockfile lokal, bukan jaminan setiap versi server atau komputer teman telah diuji. **Blade adalah template engine, Bootstrap adalah framework CSS/JS antarmuka, dan PHP tetap digunakan untuk Controller/Model/Request/Migration/Test.** Migrasi CSS tidak mengubah bahasa backend.

### Fokus demo MVP UTS Pertemuan 1–7

**Prioritas utama demo adalah CRUD Data Anggota**, didukung login, Dashboard sesuai role, validasi, proteksi akses, API JSON/Postman dan logout. Dashboard keuangan, Jenis Iuran, Tagihan, Pembayaran, Verifikasi, Transaksi, Transparansi dan Laporan/PDF tetap dipertahankan sebagai **DALAM PENGEMBANGAN/TESTING**, bukan fitur final. Daftar berikut tetap mengikuti cakupan penilaian UTS, bukan klaim semua integrasi telah lulus.

- Requirement dan UI/UX, Laravel/Blade/Bootstrap serta pemisahan MVC.
- Login/logout, dua role **Admin dan Anggota**, session dan akses terlarang HTTP **403**.
- MySQL, migration, seeder, foreign key dan relasi Eloquent yang dipakai aplikasi.
- CRUD Data Anggota dengan validasi server-side bahasa Indonesia; Jenis Iuran sebagai pendukung KasOrg.
- Minimal satu API JSON dengan status HTTP tepat dan bukti pengujian Postman.
- Error handling 403/404/500 dan integrasi alur inti.

Acuan penilaian adalah [ketentuan resmi UTS](docs/referensi-dosen/Ketentuan-UTS-PWL.pdf), bukan jumlah fitur atau commit. Requirement KasOrg masih memiliki bagian draf yang perlu diselaraskan dengan kelompok/dosen.

### Status fitur

CRUD Data Anggota adalah **fitur utama demo UTS**. “Tersedia” berarti source/route operasional ada pada branch pengembangan, bukan fitur final atau sudah lolos MySQL. Hasil SQLite di bawah merujuk [pengujian paket](#hasil-uji), bukan pengujian baru saat menulis README.

| Fitur | Admin | Anggota | Implementasi | Status Pengujian |
| --- | --- | --- | --- | --- |
| Login/logout | Login Admin dan logout POST | Login Anggota dan logout POST | Auth Laravel, hash password, CSRF, regenerasi session dan middleware role. | Login/logout, password salah, CSRF dan akses terlarang lulus HTTP SQLite; browser dua role diuji. |
| Dashboard Admin | Ringkasan kas, anggota aktif, tagihan, periode dan transaksi terbaru | Dilarang membuka portal Admin | Controller/Service data asli dan Bootstrap, bukan angka mock. | Render/navigasi lulus SQLite dengan data kas awal kosong; variasi finansial MySQL belum diuji. |
| Dashboard Anggota | Portal terpisah sesuai role | Ringkasan organisasi/pribadi, tagihan dan aktivitas yang diizinkan | Blade Bootstrap dan Service bersama; data pribadi dibatasi pengguna login. | Render, login dan smoke desktop/mobile diuji SQLite; bukan regresi seluruh keuangan. |
| **CRUD Data Anggota** | Create; daftar/cari/filter/pagination; edit; aktif/nonaktif; arsip | GET/POST Admin ditolak 403 | User/Role, Form Request, UserPolicy, CSRF, route model binding dan soft delete. | Penyimpanan, edit, status, arsip, validasi, pencarian dan pagination 5+1 lulus SQLite. Form tambah/edit/hapus diuji browser. |
| Validasi / perlindungan demo | NIM/email wajib unik; password kuat; demo utama dilindungi | Tidak mengelola anggota | Unique termasuk akun arsip; role ditetapkan server, bukan input bebas. | Input invalid/duplikat dan penolakan hapus demo diuji SQLite; belum semua edge case MySQL. |
| Jenis Iuran | Daftar, tambah, edit, status; hapus hanya jika belum digunakan | Tidak mengelola master | Bootstrap; nominal baru tidak mengubah tagihan lama; riwayat dilindungi. | Halaman dibuka pada SQLite dan ada bukti fixture terdahulu; CRUD penuh snapshot/MySQL belum diuji ulang. |
| Tagihan | Terbitkan individual untuk anggota aktif | Melihat tagihan sendiri | Relasi jenis/periode/anggota dan unique mencegah duplikat; terbit tagihan bukan pemasukan. | Halaman/empty state dibuka; alur penuh masih pengembangan/testing. |
| Pembayaran / upload | Memeriksa pengajuan sesuai izin | Tunai/transfer, gambar bukti privat, pengajuan ulang | Controller/Service, validasi file dan Policy bukti. | Source ada; upload/retry dan seluruh transisi belum diuji ulang pada paket ini. |
| Verifikasi Pembayaran | Setuju/tolak setelah pemeriksaan manual | Melihat hasil pada tagihan | Approval atomik membuat satu pemasukan; menunggu/ditolak bukan kas. | Halaman dibuka; alur finansial penuh/locking MySQL belum diuji. |
| Transaksi Kas Manual | Tambah/edit/soft delete manual pada periode yang diizinkan | Tidak mengelola ledger | Transaksi iuran otomatis dilindungi; kategori sesuai jenis. | Halaman dibuka; CRUD keuangan dan berbagai skenario saldo belum diuji ulang. |
| Transparansi | Melihat informasi organisasi | Melihat informasi organisasi yang diizinkan | Service bersama, filter, alokasi dan tabel tanpa bukti transfer pribadi. | Halaman kedua role dibuka; pengujian finansial lengkap belum dilakukan. |
| Laporan / PDF | Lihat/unduh sesuai periode | Lihat/unduh sesuai izin | Service bersama dan Dompdf, bukan notifikasi unduh palsu. | Halaman dibuka; render/unduh PDF aktual belum termasuk pengujian paket. |
| Error 403/404/500 | Pesan Indonesia | Sama | Blade kustom mempertahankan status HTTP asli. | 403 dan 404 nyata diuji; error 500 aktual tidak sengaja dipicu. |
| API JSON / Postman | Dua GET melalui session; collection memakai Admin | Dua GET sesuai role, tanpa akses Dashboard Admin | **Sudah committed/dipush pada ad03cc5**: 7 path API dan 3 file Postman; tersedia pada clone branch ini. | SQLite terisolasi: 15 tes/148 assertions; CLI 13 request/51 assertions; HTTP tambahan 20/20. Bukan bagian 45/45 portal atau bukti MySQL/GUI laptop anggota lulus. |

Modul keuangan tetap **DALAM PENGEMBANGAN / TESTING**, bukan dihapus atau diganti placeholder. `/ui-preview/*` hanya aktif pada environment local, memakai mock dan tidak menyediakan autentikasi/CRUD sungguhan. Status iuran Data Anggota, badge sidebar dan notifikasi tertentu masih simulasi/belum terhubung.

### Cara membaca bukti

| Istilah | Makna | Bukan berarti |
| --- | --- | --- |
| Committed/dipush | Source web dan API/Postman tercatat sampai ad03cc5 pada branch integrasi. | Sudah masuk main/tag rilis atau production-ready. |
| Working tree lokal | Ada suite test/helper, runner, workspace YAML Postman, dokumentasi dan arsip pindahan yang belum dipush. | Tambahan itu otomatis ikut clone atau wajib untuk menjalankan API. |
| Fixture frontend | Geometri/interaksi pada HTML/data buatan terisolasi. | Login HTTP atau MySQL telah diuji. |
| Paket SQLite terisolasi | Dependency, migration asli, seeder, HTTP dan browser pada salinan index dengan database baru. | Fresh clone laptop teman atau perilaku ENUM/DECIMAL/locking MySQL telah lulus. |

<a id="persyaratan"></a>

## 2. Persyaratan komputer

| Kebutuhan | Versi/kondisi dan fungsi |
| --- | --- |
| Sistem operasi | Windows 10/11 untuk tutorial PowerShell ini; mesin lain perlu penyesuaian path. |
| Git | Clone branch dan memeriksa revisi; tidak menyalin database/rahasia. |
| Editor | VS Code atau editor lain; terminal pada root berisi artisan. |
| PHP CLI | **>=8.2** dan memenuhi composer.lock; paket terisolasi diuji pada PHP 8.2.4, bukan kewajiban memakai patch lama itu. |
| Composer | Versi 2; install sesuai composer.lock. |
| Server database | XAMPP atau Laragon dengan MySQL/MariaDB; buat database lokal baru kosong. |
| Node.js + npm | Node **`^20.19.0` atau `>=22.12.0`** untuk Vite 7.3.7; pengujian memakai Node 22.23.4/npm 10.9.9. |
| Browser | Chrome/Edge/Firefox modern; Chrome headless dipakai pada uji paket. |
| Postman | Untuk menguji dua endpoint yang sudah ikut branch melalui collection JSON 13 request; bukan syarat membuka CRUD. |
| Internet / izin | Dependency, font/ikon/CDN; PHP dapat menulis storage dan bootstrap/cache instalasinya sendiri. |

- Ekstensi PHP: **BCMath**, PDO dan **pdo_mysql**, Ctype, cURL, DOM, Fileinfo, Filter, Hash, Iconv, JSON, Libxml, Mbstring, OpenSSL, PCRE, Session, Tokenizer dan XML. Sebagian merupakan bawaan PHP. Untuk pengujian SQLite diperlukan **pdo_sqlite**; ekstensi SQLite3 terpisah hanya bila runner memerlukannya. XMLWriter diperlukan dependency test. GD disarankan untuk gambar PDF; ZIP/alat ekstraksi membantu Composer.
- **BCMath wajib untuk perhitungan uang KasOrg**, meskipun belum menjadi `ext-bcmath` eksplisit pada composer.json. Lolos Composer saja belum membuktikan BCMath aktif.
- MySQL/MariaDB dengan InnoDB dan `utf8mb4_unicode_ci`; lingkungan integrasi yang dipakai adalah MariaDB 10.4 XAMPP. Versi/server lain perlu verifikasi instalasi tersendiri.
- Node.js yang memenuhi Vite 7.3.7: **`^20.19.0 || >=22.12.0`**, beserta npm. Setup integrator sebelumnya berhasil memakai **Node v22.23.4 / npm 10.9.9**. Node digunakan untuk build aset, bukan menggantikan PHP atau MariaDB.
- Postman Desktop untuk demo API; CLI opsional. Internet diperlukan untuk instalasi dependency, font/ikon, serta fallback CDN bila build tidak ada.
- `storage/` dan `bootstrap/cache/` dapat ditulis proses PHP; jangan memberi akses global berlebihan atau membagikan file runtime privat.

Periksa dari terminal yang akan digunakan:

```powershell
php --version
php --ini
php -m
composer --version
git --version
where.exe node
node --version
npm.cmd --version
```

`php --ini` menunjukkan php.ini **CLI**; jangan hanya memeriksa PHP Apache. Bila berbeda, jalankan `where.exe php` dan `Get-Command php`. Contoh XAMPP: `& 'C:\xampp\php\php.exe' -v` dan `& 'C:\xampp\php\php.exe' --ini`. Pada Laragon pilih versi PHP yang sesuai melalui panel, buka terminal baru dan periksa kembali. Pastikan launcher Composer memakai PHP yang sama. Aktifkan ekstensi pada php.ini yang benar bersama pemilik, lalu restart proses terkait. README ini tidak mengubah konfigurasi mesin.

<a id="clone"></a>

## 3. Panduan instalasi Windows — pilih XAMPP atau Laragon

Kedua tutorial berikut hanya untuk **instalasi baru dan database baru kosong yang disetujui pemilik komputer**. Semua perintah PowerShell dijalankan satu per satu; berhenti saat error, jangan lanjut menebak. Jika folder proyek atau database sudah berisi pekerjaan/data, gunakan [alur existing](#existing).

**Mulai dari VS Code, sebelum menjalankan perintah:**

1. Buka **Visual Studio Code** melalui Start Windows.
2. Pilih **Terminal → New Terminal**. Pada menu dropdown profil terminal, pilih **PowerShell**; bila perlu pilih **Select Default Profile → PowerShell**, lalu buka terminal baru. Ini memilih shell, bukan memasang PHP/Node atau mengubah PATH Windows.
3. Pilih **satu** tutorial: XAMPP memakai folder induk `C:\xampp\htdocs`; Laragon memakai `C:\laragon\www`. Pastikan folder induk itu ada, sedangkan folder tujuan `KasOrg` belum berisi pekerjaan lain. Jalankan perintah clone di langkah 2 tutorial yang dipilih, bukan kedua tutorial sekaligus.
4. Setelah clone, buka folder `KasOrg` melalui **File → Open Folder** atau `code .`. Buka terminal PowerShell di jendela proyek tersebut; `Get-Location` harus menunjuk root KasOrg dan `Test-Path -LiteralPath 'artisan'` harus menghasilkan `True`. Terminal untuk `php artisan serve` dibiarkan berjalan; gunakan terminal kedua jika perlu perintah lain.

Jika terminal belum mengenali PHP, Composer, Git atau Node, selesaikan pemeriksaan alat di langkah 1 dahulu. Jangan menempelkan seluruh tutorial sekaligus atau melanjutkan migration setelah perintah sebelumnya gagal.

<a id="xampp"></a>

### A. INSTALASI KASORG MENGGUNAKAN XAMPP

1. **Buka XAMPP dan periksa alat.** Tujuan: memastikan PHP yang menjalankan Laravel sesuai [persyaratan](#persyaratan). Buka XAMPP Control Panel dari instalasi Windows; jangan membuat proyek Laravel baru. Di PowerShell/terminal VS Code jalankan:

   ~~~powershell
   where.exe php
   php --version
   php --ini
   php -m
   composer --version
   git --version
   node --version
   npm.cmd --version
   ~~~

   **Hasil:** PHP >=8.2, Composer/Git/Node/npm terbaca, BCMath dan pdo_mysql aktif. **Jika gagal:** periksa PATH dan php.ini CLI. Bila perlu, contoh pemeriksaan PHP XAMPP langsung: & 'C:\xampp\php\php.exe' --version. PHP terminal bisa berbeda dari PHP Apache; jangan hanya melihat panel XAMPP. Node dari Laragon boleh dipakai tanpa memindahkan PHP/MySQL XAMPP, lihat langkah 10.

2. **Clone branch kelompok.** Tujuan: mendapatkan source GitHub pada folder baru, bukan menimpa pekerjaan lama.

   ~~~powershell
   Set-Location 'C:\xampp\htdocs'
   git clone --branch jovan/integrasi-laravel https://github.com/Jopannnnnn/KasOrg.git
   Set-Location 'C:\xampp\htdocs\KasOrg'
   ~~~

   **Hasil:** root proyek memiliki artisan, composer.json dan resources/. **Jika gagal:** periksa akses GitHub, nama branch, jaringan, dan apakah folder KasOrg sudah ada. Jangan hapus folder existing. Checkpoint terbaru **ad03cc5** memuat portal dan API/Postman. Periksa branch dan HEAD dengan `git branch --show-current` serta `git rev-parse HEAD`, bukan menganggap main sudah sama.

3. **Buka VS Code dan terminal pada root.** Tujuan: supaya perintah menemukan artisan dan dependency yang benar.

   ~~~powershell
   code .
   Get-Location
   Test-Path -LiteralPath 'artisan'
   git branch --show-current
   git rev-parse HEAD
   ~~~

   **Hasil:** folder aktif C:\xampp\htdocs\KasOrg, Test-Path menghasilkan True, branch jovan/integrasi-laravel dan HEAD ad03cc52e21ee8d2a0a7fa0f70071d36024b91fc (atau pembaruan berikutnya yang disepakati). **Jika gagal:** pilih File → Open Folder secara manual bila command code belum tersedia; pilih Terminal → New Terminal → PowerShell, lalu Set-Location ke root proyek.

4. **Pasang dependency PHP.** Tujuan: menyediakan Laravel, autoload dan library sesuai lockfile.

   ~~~powershell
   composer install
   composer check-platform-reqs
   ~~~

   **Hasil:** vendor/autoload.php tersedia, dependency/platform tidak error. **Jika gagal:** baca error PHP/ekstensi/jaringan pertama; jangan beralih ke composer update atau composer run setup. Script install saat ini melakukan package discovery, bukan migration/seeder.

5. **Buat konfigurasi privat sekali.** Tujuan: menyalin template, bukan menyalin rahasia integrator.

   ~~~powershell
   if (Test-Path -LiteralPath '.env') { throw '.env sudah ada. Gunakan alur existing; jangan ditimpa.' }
   Copy-Item -LiteralPath '.env.example' -Destination '.env'
   ~~~

   **Hasil:** .env lokal baru tersedia. **Jika gagal karena sudah ada:** jangan timpa; tinjau bersama pemilik. Template pada checkpoint ini memakai MySQL, tetapi akun database, password demo dan APP_KEY masih kosong: **jangan migration dulu**. Lengkapi langkah berikut agar target dan kredensial benar.

6. **Nyalakan layanan dan buat database baru.** Tujuan: menyediakan tempat tabel aplikasi, terpisah dari kasorg_dev integrator. Di XAMPP Control Panel klik **Start MySQL** dan **Start Apache** untuk membuka phpMyAdmin. Buka http://localhost/phpmyadmin melalui browser, pilih Databases/New, masukkan contoh nama **kasorg_local**, pilih collation **utf8mb4_unicode_ci**, lalu Create.

   **Perintah terminal:** tidak ada; ini tindakan GUI pada server lokal milik sendiri. **Hasil:** database baru kosong, belum ada tabel. **Jika nama sudah berisi tabel:** STOP, jangan hapus isinya. Jika phpMyAdmin gagal dibuka, periksa Apache/port web; jika MySQL gagal Start, periksa konflik port/layanan lain. Apache diperlukan untuk phpMyAdmin atau akses via Apache, **tidak wajib untuk server Artisan**.

7. **Atur koneksi dan akun demo secara privat.** Tujuan: mengarahkan Laravel ke database kosong tadi. Buka .env lokal di editor, terapkan [contoh konfigurasi bersama](#konfigurasi-baru): APP_NAME, APP_ENV, APP_URL, DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME dan DB_PASSWORD sesuai server XAMPP sebenarnya. Tambahkan dua variabel password demo dari bagian itu, isi berbeda secara privat.

   **Perintah terminal:** tidak ada; edit hanya konfigurasi lokal sendiri. **Hasil:** DB_DATABASE sama dengan database kosong langkah 6, DB_URL kosong, session/cache file dan queue sync. **Jika belum tahu akun/port:** konfirmasi ke pemilik server, jangan mengasumsikan root tanpa password. .env.example menyediakan nama variabel/contoh target, bukan membuat database atau mengisi kredensial secara otomatis.

8. **Buat APP_KEY untuk instalasi baru.** Tujuan: menyediakan kunci enkripsi/session lokal. Hanya jika APP_KEY instalasi baru masih kosong:

   ~~~powershell
   php artisan key:generate
   php artisan config:clear
   ~~~

   **Hasil:** pesan key berhasil dibuat; cache konfigurasi lama tidak dipakai. **Jika gagal:** periksa vendor, izin .env dan root proyek. Jangan pakai --show, mencetak key atau mengganti key instalasi existing.

9. **Bangun tabel, lalu data awal.** Tujuan: membentuk struktur resmi Laravel dan dua akun demo pada **database baru kosong**, bukan mengimpor arsip SQL.

   ~~~powershell
   php artisan migrate --database=mysql
   php artisan db:seed --database=mysql
   ~~~

   **Hasil:** migration selesai, role/admin/anggota serta data minimum tersedia; [hasil seed](#hasil-seeder) menjelaskan isinya. **Jika gagal:** berhenti, periksa target DB, FK/ekstensi dan konfigurasi password demo. Jangan menjalankan perintah kedua bila yang pertama gagal; seed parsial perlu review sebelum diulang. Perintah ini menulis DB: **jangan jalankan pada kasorg_dev existing atau database berisi data kelompok**.

10. **Siapkan Node dan build Bootstrap/Vite.** Tujuan: membuat bundle frontend lokal, termasuk jalur Tailwind legacy yang masih diperlukan.

    ~~~powershell
    where.exe node
    node --version
    npm.cmd --version
    ~~~

    Jika Node hanya tersedia di Laragon, gunakan [PATH sementara](#aset) untuk terminal ini; MySQL/PHP tetap XAMPP. Lockfile sudah tersedia: jalankan perintah berikut berurutan dan berhenti saat ada error. npm.cmd adalah launcher npm untuk PowerShell tanpa mengubah execution policy:

    ~~~powershell
    npm.cmd ci
    npm.cmd run build
    Test-Path -LiteralPath 'public/build/manifest.json'
    ~~~

    **Hasil:** Vite selesai tanpa error, manifest True dan [empat entry/output](#aset) tersedia. **Jika gagal:** periksa versi Node, error npm pertama dan kesesuaian package.json/lockfile. Jangan menganggap fallback CDN sebagai bukti build berhasil atau menjalankan upgrade paksa.

11. **Jalankan server sampai Login terbuka.** Tujuan: menjalankan HTTP Laravel, bukan PHP native prototype.

    ~~~powershell
    php artisan serve --host=127.0.0.1 --port=8000
    ~~~

    **Hasil:** terminal menunjukkan server berjalan; buka http://127.0.0.1:8000/login. MySQL tetap aktif; Apache boleh dihentikan bila tidak lagi memakai phpMyAdmin/virtual host. **Jika gagal:** periksa port 8000, APP_KEY, vendor, layanan database dan izin storage; jangan reset DB. Jangan tutup terminal server selama mencoba aplikasi.

12. **Coba dua role dan CRUD Anggota pada data uji.** Tujuan: memastikan integrasi, bukan sekadar halaman tampil. Login dengan email [akun demo](#akun-demo) dan password privat yang baru disiapkan; Admin seharusnya ke Dashboard Admin, Anggota ke Dashboard Anggota. Logout di antara pergantian akun. Gunakan [skenario demo](#pengujian) dengan anggota uji terpisah bila operasi tulis telah disetujui; jangan mengubah akun demo utama.

    **Perintah/aksi:** ikuti [16 skenario CRUD](#uji-crud) melalui browser pada http://127.0.0.1:8000, bukan URL phpMyAdmin. **Hasil yang perlu dibuktikan:** CRUD tersimpan, validasi menolak input salah, Anggota ke Admin mendapat 403 dan logout mengakhiri sesi. **Jika gagal:** lihat [troubleshooting](#troubleshooting), lalu laporkan ke kelompok. Ini rencana verifikasi MySQL di mesin teman, bukan hasil yang sudah lulus.

13. **Uji API dari paket repository.** Tujuan: menguji session/JSON pada instalasi MySQL sendiri. Di terminal kedua, periksa `php artisan route:list --path=api`; kedua endpoint pada [bagian API](#postman) seharusnya terdaftar. Impor [collection](postman/KasOrg-UTS.postman_collection.json) dan [environment contoh](postman/KasOrg-Local.example.postman_environment.json) ke Postman, isi kredensial Admin hanya secara privat, lalu ikuti [13 request berurutan](postman/README.md). **Hasil yang diharapkan:** 13 request/51 assertions tanpa kegagalan; catat hasil aktual. Browser login tidak berbagi sesi dengan Postman. **Jika gagal:** periksa revisi, server/base_url, cookie dan CSRF; jangan seed ulang/reset database. Keberhasilan di laptop anggota masih perlu dibuktikan.

<a id="laragon"></a>

### B. INSTALASI KASORG MENGGUNAKAN LARAGON

1. **Buka Laragon dan pilih runtime.** Tujuan: memakai PHP >=8.2 beserta MySQL/MariaDB yang sesuai. Pilih versi melalui menu yang tersedia pada instalasi Laragon sendiri; paket/menu dapat berbeda. Buka Terminal Laragon, atau terminal PowerShell VS Code yang PATH-nya memakai runtime tersebut.

   ~~~powershell
   where.exe php
   php --version
   php --ini
   php -m
   composer --version
   git --version
   where.exe node
   node --version
   npm.cmd --version
   ~~~

   **Hasil:** persyaratan termasuk BCMath/pdo_mysql terpenuhi. **Jika gagal:** periksa PHP terminal vs web serta PATH; jangan menganggap semua bundle Laragon sudah berisi versi/ext yang tepat. Contoh berikut memakai **PowerShell**; bila terminal Laragon berbasis CMD, buka PowerShell atau gunakan cd /d untuk berpindah drive, bukan menempelkan sintaks PowerShell ke CMD.

2. **Clone ke folder Laragon.** Tujuan: memperoleh source yang sama seperti XAMPP, pada folder baru.

   ~~~powershell
   Set-Location 'C:\laragon\www'
   git clone --branch jovan/integrasi-laravel https://github.com/Jopannnnnn/KasOrg.git
   Set-Location 'C:\laragon\www\KasOrg'
   ~~~

   **Hasil:** artisan, composer.json dan resources/ ada. **Jika gagal:** cek jaringan/akses branch/folder existing; jangan gunakan Quick App untuk membuat proyek baru atau menghapus pekerjaan lama. Gunakan checkpoint **ad03cc5** pada branch integrasi, yang sudah memuat portal dan [API/Postman](#postman), bukan menganggap main sudah sama.

3. **Buka VS Code dan terminal.** Tujuan: menjalankan perintah pada root yang benar.

   ~~~powershell
   code .
   Get-Location
   Test-Path -LiteralPath 'artisan'
   ~~~

   **Hasil:** root C:\laragon\www\KasOrg, Test-Path True. **Jika code tidak tersedia:** File → Open Folder; terminal Laragon/PowerShell tetap dapat dipakai setelah Set-Location ke root. Buka ulang terminal lama jika versi PHP/PATH baru dipilih.

4. **Pasang dependency PHP dari lockfile.** Tujuan: memperoleh Laravel dan library yang benar tanpa upgrade sembarangan.

   ~~~powershell
   composer install
   composer check-platform-reqs
   ~~~

   **Hasil:** vendor/autoload.php dan pemeriksaan platform sukses. **Jika gagal:** periksa PHP yang dipakai Composer, ekstensi, izin folder dan jaringan. Jangan memakai composer run setup pada database existing.

5. **Buat .env lokal jika belum ada.** Tujuan: mempunyai konfigurasi privat milik komputer sendiri.

   ~~~powershell
   if (Test-Path -LiteralPath '.env') { throw '.env sudah ada. Jangan ditimpa; tinjau alur existing.' }
   Copy-Item -LiteralPath '.env.example' -Destination '.env'
   ~~~

   **Hasil:** template tersalin tanpa menimpa konfigurasi lama. **Jika sudah ada:** STOP dan gunakan [alur existing](#existing). Jangan menyalin key/password integrator atau langsung migration sebelum target database kosong dan konfigurasi privat dipastikan benar. Template MySQL dan variabel demo sudah tersedia pada checkpoint 15e32bc; nilainya tetap harus diisi privat.

6. **Jalankan database dan buat database kosong.** Tujuan: menyiapkan server lokal Laragon yang benar. Jalankan layanan MySQL/MariaDB yang dipilih melalui panel Laragon. Buka pengelola database yang tersedia, misalnya HeidiSQL **jika terpasang**, atau phpMyAdmin hanya bila memang dipasang/dikonfigurasi. Hubungkan ke host/port Laragon sebenarnya, lalu buat contoh database baru **kasorg_local** dengan collation utf8mb4_unicode_ci.

   **Perintah terminal:** tidak ada; tindakan melalui panel/pengelola database. **Hasil:** target baru kosong pada server yang benar. **Jika konflik port dengan XAMPP:** pilih satu layanan database yang dipakai, atau sepakati port berbeda; jangan memindahkan/import database untuk mengatasinya. Jangan mengasumsikan akun, password atau port Laragon selalu sama dengan XAMPP. Jika database sudah berisi tabel, jangan direset.

7. **Konfigurasi koneksi dan password demo.** Tujuan: menyambungkan Laravel ke database kosong langkah 6. Di editor .env lokal, gunakan [contoh bersama](#konfigurasi-baru), tetapi isi DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD berdasarkan Laragon sendiri; DB_DATABASE sesuai database baru. Atur APP_NAME=KasOrg, APP_ENV=local, APP_URL=http://127.0.0.1:8000, DB_URL kosong serta dua password demo privat berbeda.

   **Perintah terminal:** tidak ada; edit konfigurasi lokal sendiri. **Hasil:** koneksi tidak menunjuk kasorg_dev integrator/server lain. **Jika tidak yakin server/akun mana:** periksa pengelola database bersama pemilik, jangan menebak kredensial atau menjalankan migration dulu.

8. **Generate key hanya pada instalasi baru.** Tujuan: menyiapkan kunci aplikasi setelah dependency dan konfigurasi siap.

   ~~~powershell
   php artisan key:generate
   php artisan config:clear
   ~~~

   **Hasil:** key dibuat sekali dan cache konfigurasi disegarkan. **Jika gagal:** periksa .env/vendor/izin PHP/root. Jangan lakukan jika instalasi sudah memiliki key yang dipakai data/sesi existing dan jangan mencetak nilainya.

9. **Migration dan seeder pada target kosong yang disetujui.** Tujuan: membuat tabel resmi, kemudian role dan data awal.

   ~~~powershell
   php artisan migrate --database=mysql
   php artisan db:seed --database=mysql
   ~~~

   **Hasil:** tabel/relasi serta [data minimum](#hasil-seeder) tersedia. **Jika gagal:** hentikan, periksa koneksi/ext/FK/password demo; jangan meneruskan seed setelah migration gagal atau menghapus tabel. --database=mysql memilih koneksi Laravel; nama database tetap dari DB_DATABASE. **Bukan instruksi menulis database existing.**

10. **Install dependency frontend, lalu build.** Tujuan: menghasilkan aset Bootstrap MVP dan Tailwind legacy dari lockfile yang tersedia. Jalankan berurutan dan berhenti bila ada error:

    ~~~powershell
    npm.cmd ci
    npm.cmd run build
    Test-Path -LiteralPath 'public/build/manifest.json'
    ~~~

    **Hasil:** Vite exit 0, manifest True dan semua output entry ada. **Jika gagal:** cek Node/npm terminal Laragon vs VS Code, kesesuaian lockfile serta error impor pertama. Ikuti [bagian aset](#aset); jangan menghapus legacy atau upgrade paksa agar terlihat berhasil.

11. **Jalankan Laravel dan buka Login.** Tujuan: menggunakan server PHP Laravel dengan URL tetap.

    ~~~powershell
    php artisan serve --host=127.0.0.1 --port=8000
    ~~~

    **Hasil:** http://127.0.0.1:8000/login terbuka dan MySQL tetap berjalan. **Jika gagal:** cek port 8000, key, izin runtime, dependency dan server database. Apache/Nginx Laragon tidak diperlukan untuk Artisan; jangan menganggap tombol Start All saja sudah menjalankan Artisan.

12. **Login dua role dan uji CRUD Anggota.** Tujuan: memverifikasi hasil instalasi sendiri. Gunakan email [demo](#akun-demo) dan password privat; logout saat berpindah akun. Ikuti [16 skenario CRUD](#uji-crud) untuk penyimpanan, validasi, status, arsip dan 403.

    **Perintah/aksi:** form browser pada http://127.0.0.1:8000/login. **Hasil:** data tersimpan dan izin role sesuai; bukti SQL hanya dibaca pada database uji sendiri bila perlu memastikan arsip. **Jika gagal:** periksa revisi, session/cookie dan [troubleshooting](#troubleshooting). Tutorial ini **bukan klaim fresh clone Laragon/MySQL telah lulus uji**.

13. **Impor dan jalankan Postman.** Tujuan: menguji dua API pada database Laragon milik sendiri, bukan memakai sesi browser. Di terminal kedua, periksa `php artisan route:list --path=api`; impor [collection JSON](postman/KasOrg-UTS.postman_collection.json) serta [environment contoh](postman/KasOrg-Local.example.postman_environment.json), isi Admin lokal secara privat, dan jalankan 01–13 sesuai [panduan Postman](postman/README.md). **Hasil yang diharapkan:** 13 request/51 assertions lulus; ini target, bukan bukti laptop teman sudah lulus. **Jika gagal:** tinjau base_url/port, cookie, CSRF dan parameter; jangan membuat endpoint palsu atau mengulang migration/seeder sebagai tebakan.

**Alternatif virtual host Laragon:** hanya gunakan URL seperti http://kasorg.test bila Apache/Nginx aktif dan document root benar-benar **C:\laragon\www\KasOrg\public**, bukan root repository. Sesuaikan APP_URL/base_url Postman serta gunakan satu host/protokol selama session. Ketersediaan automatic virtual host tidak membuktikan document root aman; untuk pemula jalur Artisan di atas sudah cukup.

<a id="perbandingan-lokal"></a>

### C. Perbedaan XAMPP dan Laragon

| Hal | XAMPP | Laragon |
| --- | --- | --- |
| Folder contoh | C:\xampp\htdocs\KasOrg | C:\laragon\www\KasOrg |
| PHP terminal | PHP XAMPP pada PATH atau executable lengkap; cek php --ini | PHP yang dipilih Laragon/terminalnya; tetap cek versi/php.ini |
| Database | Start MySQL melalui XAMPP Control Panel | Jalankan layanan MySQL/MariaDB yang dipilih di panel Laragon |
| Pengelola DB | phpMyAdmin, membutuhkan Apache | Pengelola yang benar-benar terpasang; jangan mengasumsikan phpMyAdmin bawaan |
| Server Laravel | php artisan serve dari root | Perintah Artisan yang sama dari root |
| Node.js | Instalasi Node yang sesuai atau executable Laragon melalui PATH proses | Node Laragon atau instalasi Node lain yang sesuai |
| .env | Host/port/akun/DB milik server XAMPP sendiri | Host/port/akun/DB sesuai konfigurasi Laragon sendiri |
| Build Bootstrap | npm.cmd ci/install sesuai lockfile → npm.cmd run build | Langkah dan source aset yang sama |
| URL browser | Artisan: http://127.0.0.1:8000/login | Artisan sama; virtual host hanya bila public benar |
| Konflik port | Jangan menyalakan DB XAMPP dan Laragon pada port yang sama | Pilih server/port yang disepakati; tidak memindahkan data diam-diam |

Keduanya **alternatif lingkungan development untuk source KasOrg yang sama**. Database lokal masing-masing komputer berbeda, walaupun namanya sama; data integrator tidak otomatis tersalin. Memakai Node Laragon **tidak berarti** PHP/MySQL harus pindah dari XAMPP. Jangan impor SQL historis Kent/adaptasi sebagai installer atau mengekspos .env/storage melalui document root yang salah.

<a id="existing"></a>

## 4. Menjalankan database yang sudah tersedia

Ini alur komputer integrasi yang sudah memakai **kasorg_dev**, atau komputer lain yang database aplikasinya sudah disiapkan secara sah.

1. Pertahankan `.env`, APP_KEY, kredensial dan data yang benar. Jangan menyalin `.env.example` di atas konfigurasi existing atau menjalankan ulang `key:generate`.
2. Nyalakan layanan database yang tepat. Konfirmasi target host/port/database secara privat dengan pemilik; jangan berbagi isi `.env`.
3. Jika dependency belum ada/belum sesuai lockfile, jalankan:

   ```powershell
   composer install
   composer check-platform-reqs
   ```

   Script `post-autoload-dump` saat ini menjalankan package discovery Laravel, bukan migration/seeder. Gunakan install dari lockfile, bukan update versi tanpa review.
4. Siapkan aset menurut [bagian Vite](#aset). Jika konfigurasi lokal memang baru diubah oleh pemilik, gunakan `php artisan config:clear` agar cache tidak menyimpan target lama; bukan perintah reset data.
5. Dari root proyek jalankan:

   ```powershell
   php artisan serve --host=127.0.0.1 --port=8000
   ```

6. Buka **http://127.0.0.1:8000/login**. Admin menuju `/admin/dashboard`; Anggota menuju `/anggota/dashboard`. Logout melalui tombol Keluar menggunakan POST + CSRF. Hentikan server dengan `Ctrl+C`.

**Tidak perlu migration, seeder, atau import SQL untuk sekadar menjalankan database existing.** Jika skema tidak cocok, hentikan dan koordinasikan; jangan mencoba reset. Jangan memakai `composer run setup`: script itu menjalankan key:generate dan migrate --force selain instalasi/build. `composer run dev` juga menjalankan queue listener, log watcher dan Vite, sehingga bukan langkah minimum demo.

<a id="instalasi-baru"></a>

## 5. Konfigurasi instalasi baru dan hasil seeder

> Hanya untuk lingkungan lokal baru yang telah disetujui pemiliknya. Ini bukan instruksi mengubah kasorg_dev integrator. Jangan menjalankan seluruh README sekaligus. Berhenti bila database target sudah berisi tabel/data atau identitas target belum pasti.

<a id="konfigurasi-baru"></a>

### 5.1. Contoh konfigurasi lokal yang dipakai kedua tutorial

Langkah menyalin template, Composer, key, migration dan seeder sudah dijelaskan berurutan pada [XAMPP](#xampp) dan [Laragon](#laragon); tidak perlu mengulang keduanya. Buka .env lokal melalui editor **hanya pada instalasi baru milik sendiri**.

`.env.example` pada checkpoint ini memakai **MySQL**, database contoh baru **kasorg_local**, session/cache **file**, dan queue **sync**. APP_KEY, akun/password database serta dua password demo sengaja kosong. **Buat database lebih dahulu dan isi `.env` lokal sebelum migration**; menyalin template tidak membuat database atau akun secara otomatis. Contoh berikut tidak berasal dari `.env` integrator dan tidak berisi kredensial nyata:

```dotenv
APP_NAME=KasOrg
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_URL=
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasorg_local
DB_USERNAME=
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

KASORG_DEMO_ADMIN_PASSWORD=
KASORG_DEMO_ANGGOTA_PASSWORD=
```

| Variabel | Yang harus dipahami/diisi |
| --- | --- |
| APP_NAME / APP_ENV | Nama KasOrg dan environment **local** di laptop anggota; Seeder demo menolak production. |
| APP_KEY | Awalnya kosong; generate sekali untuk instalasi baru. Jangan menyalin atau menimpa key instalasi existing. |
| APP_DEBUG | false untuk demo aman tanpa stack trace; log dibaca privat bila perlu diagnosis. |
| APP_URL | URL Artisan/virtual host yang benar. Gunakan host yang sama selama login agar cookie konsisten. |
| DB_CONNECTION / DB_URL | mysql untuk server MySQL/MariaDB; DB_URL kosong agar URL koneksi lama tidak menimpa field koneksi. |
| DB_HOST / DB_PORT | Alamat/port server sendiri, contoh 127.0.0.1:3306; konfirmasi jika berbeda. |
| DB_DATABASE | Nama **database baru kosong yang telah dibuat**, contoh kasorg_local; bukan kasorg_dev integrator. |
| DB_USERNAME / DB_PASSWORD | Isi privat sesuai akun server sendiri. Username kosong harus dilengkapi; password kosong hanya bila akun lokal benar-benar dikonfigurasi demikian. Jangan mengasumsikan root tanpa password. |
| DB_CHARSET / DB_COLLATION | utf8mb4 / utf8mb4_unicode_ci sesuai config/database.php. |
| SESSION_DRIVER / CACHE_STORE | file: folder runtime lokal, bukan mengharuskan tabel session/cache untuk membuka login. |
| QUEUE_CONNECTION | sync: pekerjaan langsung, tanpa worker queue untuk setup minimum. |
| KASORG_DEMO_ADMIN_PASSWORD / KASORG_DEMO_ANGGOTA_PASSWORD | Isi privat, berbeda, minimal 8 karakter sesuai UserSeeder; bukan password publik bawaan. |

- `kasorg_local` adalah **contoh nama database baru di laptop anggota**, bukan pengganti/rename kasorg_dev integrator. Pilih nama lokal yang disepakati dan belum berisi data.
- Driver Laravel `mysql` dapat digunakan untuk MariaDB ini. Host, port, username dan password mengikuti server yang benar; jangan mengasumsikan root tanpa password.
- Kosongkan `DB_URL` agar URL koneksi lama tidak menimpa pengaturan target; periksa juga environment terminal/server yang dapat menimpa konfigurasi lokal.
- Isi dua password demo **secara privat, berbeda**, sesuai [aturan seeder](#akun-demo). Jangan menyalin password aktif, APP_KEY atau cookie ke README/repository/screenshot.
- `APP_DEBUG=false` diperlukan untuk demonstrasi error 500 yang aman tanpa stack trace. Debug lokal hanya diaktifkan secara sadar, bukan pada server terbuka/demo error.

<a id="hasil-seeder"></a>

### 5.2. Apa yang dibuat migration dan seeder?

Migration membentuk tabel, FK dan constraint pada database kosong yang telah diverifikasi. --database=mysql memilih nama koneksi Laravel, bukan memaksa nama database. Seeder mengisi data awal setelah struktur tersedia; kedua langkah menulis database sehingga **tidak dijalankan pada kasorg_dev existing**. Jangan gunakan migrate:fresh, db:wipe, import SQL historis atau reset sebagai solusi instalasi gagal.

Urutan [DatabaseSeeder](database/seeders/DatabaseSeeder.php): **RoleSeeder → UserSeeder → KategoriTransaksiSeeder → JenisIuranSeeder → PeriodeSeeder**. Pada instalasi baru, hasil minimumnya:

- Dua role dan dua akun demo aktif.
- Kategori Iuran anggota (pemasukan sistem), Pendapatan lainnya (pemasukan), dan Kegiatan (pengeluaran).
- Jenis Iuran Bulanan, nominal standar Rp75.000.
- Satu periode bulan berjalan terbuka dengan saldo awal nol; tidak otomatis menutup/membawa saldo periode lama.
- **Tidak ada tagihan, pembayaran, transaksi kas, atau saldo palsu.** Data lokal PC integrator tidak otomatis ikut ke laptop teman.

Seeder menghindari duplikasi berdasarkan identitas data, tetapi batch DatabaseSeeder belum satu transaksi menyeluruh. Jika gagal, sebagian langkah awal mungkin sudah tersimpan; tinjau penyebab bersama pengelola, jangan reset atau seed ulang tanpa review.

### 5.3. Aset dan server

Ikuti [instalasi/build aset](#aset), lalu jalankan Artisan seperti [alur existing](#existing) dan buka `/login`. `storage:link` **tidak diperlukan untuk bukti transfer**: disk `bukti` menggunakan `storage/app/bukti-private`, serve=false dan akses melalui Controller/Policy. Jangan membuat symlink publik untuk folder bukti. Link publik standar, bila suatu fitur lain membutuhkannya kelak, bukan izin mempublikasikan bukti pembayaran.

<a id="akun-demo"></a>

## 6. Akun demo dan hak akses

| Nama | Email login | NIM | Role/status |
| --- | --- | --- | --- |
| Admin KasOrg | `admin@kasorg.test` | `24170001` | admin / aktif |
| Anggota Demo | `anggota@kasorg.test` | `24170002` | anggota / aktif |

Sumber: [UserSeeder](database/seeders/UserSeeder.php), [config/demo.php](config/demo.php), dan [User](app/Models/User.php). **Tidak ada password publik/default dalam README.** Mintalah kredensial existing kepada penanggung jawab melalui saluran privat.

- Dua variabel lokal: `KASORG_DEMO_ADMIN_PASSWORD` dan `KASORG_DEMO_ANGGOTA_PASSWORD`, tanpa fallback. Seeder hanya mengizinkan **local/testing**, menolak nilai kosong, kurang dari 8 karakter (pemeriksaan source menggunakan `strlen`), atau password yang sama.
- Password baru di-hash oleh cast **hashed** Laravel pada User; login melalui Auth::attempt dan pemeriksaan hash provider, bukan membandingkan plaintext database. Jangan melakukan hashing ganda.
- Jika nama, NIM, email, role dan status akun demo existing cocok, seeder melewatinya **tanpa menimpa password/atribut**. Konflik identitas termasuk akun arsip membuat seeder berhenti, bukan membuat duplikat/overwrite.
- Mengganti nilai password environment tidak mereset akun yang sudah ada. Pengecualian minimal 8 untuk dua demo ini tidak melemahkan aturan pengguna nyata.
- Akun anggota baru dari UI memerlukan password awal minimal **12 karakter**, huruf besar/kecil, angka, simbol dan konfirmasi; maksimum **72 byte**. Admin menyampaikannya secara privat. Edit profil tidak mengganti password; reset mandiri belum tersedia.
- Admin aktif mengelola anggota/iuran/kas; Anggota mengakses kewajiban pribadi dan informasi organisasi yang diizinkan. Anggota membuka `/admin/anggota` atau `/admin/dashboard` mendapat **403**. Tamu menuju login; akun nonaktif/arsip tidak diizinkan melanjutkan akses.

Role backend hanya **admin** dan **anggota**. Bendahara adalah tanggung jawab Admin, bukan role database ketiga. Akun demo utama dilindungi dari perubahan melalui CRUD Anggota. Detail: `docs/DATA_DEMO_UTS.md`.

### Mencoba login dua role

1. Pastikan migration/seeder **instalasi baru sendiri** selesai dan MySQL tetap menyala; jalankan server, lalu buka **http://127.0.0.1:8000/login**.
2. Isi email `admin@kasorg.test` dan password variabel Admin, lalu Masuk. Harapan: `/admin/dashboard`; buka Data Anggota.
3. Klik **Keluar** pada sidebar. Jangan mengubah email, NIM, role atau password dua demo utama untuk latihan.
4. Login dengan `anggota@kasorg.test` dan password variabel Anggota. Harapan: `/anggota/dashboard`; membuka `/admin/anggota` harus **403**, bukan daftar Admin.
5. Keluar lagi. Halaman terlindungi setelah logout harus mengarahkan ke login. Jika gagal, periksa akun/target database/session privat, bukan membandingkan password plaintext di database.

<a id="aset"></a>

## 7. Bootstrap, Tailwind legacy, dan Vite

### Bedakan enam istilah frontend

| Istilah | Peran sederhana di KasOrg |
| --- | --- |
| Blade | Menyusun HTML dari data PHP melalui layout/komponen; bukan framework CSS. |
| Bootstrap 5 | Menyediakan grid, form, tabel, card, tombol dan modal MVP; CSS KasOrg menjaga desain Refan. |
| Tailwind | Utility CSS untuk halaman legacy yang belum dikonversi; tidak dimuat bersama Bootstrap pada MVP. |
| Node.js | Runtime untuk menjalankan alat frontend saat develop/build, bukan server Laravel/MySQL. |
| npm | Memasang package sesuai package.json/lockfile serta memanggil script dev/build. |
| Vite | Membaca entry CSS/JS dan membuat bundle + manifest agar Blade memilih aset yang benar. |

### Instalasi dependency dan build

`package-lock.json` **sudah committed dan ikut clone** (lockVersion 3). Gunakan versi dependency terkunci:

```powershell
node --version
npm.cmd --version
```

Pasang dependency dan build **berurutan**, hanya jika perintah sebelumnya berhasil:

```powershell
npm.cmd ci
npm.cmd run build
```

npm ci membangun ulang node_modules pada instalasi sendiri dan memeriksa kesesuaian package.json/lockfile. Jika lockfile hilang/tidak sesuai, hentikan dan periksa revisi; jangan menghapus lockfile atau menjalankan update/audit fix --force sebagai tebakan.

Keberhasilan ditandai exit code 0, output Vite selesai, **`public/build/manifest.json`** tersedia, dan keempat entry berikut menunjuk file hasil build yang ada. `public/build/` dan node_modules di-ignore: komputer baru perlu install/build sendiri, bukan menganggap bundle ikut clone. Untuk pengembangan, `npm.cmd run dev` pada terminal kedua dapat dipakai bersama Artisan; jangan membagikan `public/hot` milik mesin sendiri.

| Jalur | Entry dan pemakai |
| --- | --- |
| Bootstrap MVP | `resources/css/bootstrap.css` mengimpor Bootstrap 5.3.8 dan `public/css/kasorg-bootstrap.css`; `resources/js/bootstrap-mvp.js` mengimpor **Modal** Bootstrap. Guest/error serta route admin.dashboard, anggota.dashboard, admin.anggota*, admin.iuran.jenis.* menggunakan jalur ini. |
| Tailwind legacy | `resources/css/app.css` dan `resources/js/app.js` untuk tagihan, verifikasi, transaksi, laporan, transparansi dan preview portal yang belum dikonversi. `resources/js/bootstrap.js` lama mengatur **Axios**, bukan Bootstrap UI. |
| CSS desain | `public/css/kasorg-*.css`: tema bersama dan stylesheet khusus Anggota, Dashboard Admin/Anggota, Login, Jenis Iuran. Scope halaman menjaga perubahan tidak merusak halaman lain. |
| Pemilih layout | `layouts/app.blade.php` memilih satu jalur per route. `frontend/bootstrap-assets.blade.php` memakai @vite bila entry build/hot tersedia; jika tidak, Bootstrap CDN 5.3.8 + CSS lokal. Wrapper sidebar/topbar dan pagination mempertahankan varian legacy. |

Jangan memuat dua framework/reset sekaligus, menambahkan Bootstrap bundle CDN di atas JS Vite, atau memasang handler modal legacy pada modal Bootstrap. Font Google dan Lucide unpkg masih eksternal; **build berhasil belum berarti demo siap offline**. Fallback CDN membantu preview tetapi **bukan bukti build Vite berhasil**.

### Node/npm tidak ditemukan di Windows

Periksa `where.exe node`, `where.exe npm`, `node --version`, `npm.cmd --version`. Buka ulang terminal VS Code setelah pemilik mengubah PATH; gunakan npm.cmd jika npm.ps1 terhalang kebijakan PowerShell. Jangan mengubah kebijakan sistem secara global hanya untuk build.

Contoh **opsional** Node Laragon yang tersedia di komputer integrator (bukan kewajiban semua laptop):

```powershell
$env:Path = 'C:\laragon\bin\nodejs\node-v22;' + $env:Path
node --version
npm.cmd --version
```

Ini hanya PATH proses terminal tersebut, bukan pengaturan Windows permanen. Pastikan folder memang tersedia; jangan menyalin path yang tidak ada atau menganggap npm wrapper tanpa executable Node sudah cukup.

**Bukti paket terisolasi 9 November 2026:** Node 22.23.4/npm 10.9.9 berhasil menjalankan npm ci (90 package) dan build Vite 7.3.7; empat entry/output diverifikasi. Bootstrap MVP dan Tailwind legacy dicek terpisah melalui browser. Ini bukti salinan SQLite terisolasi, bukan fresh clone MySQL laptop teman. Build tidak diulang untuk pembaruan README ini. Catatan lokal `docs/MIGRASI_BOOTSTRAP_MVP.md` masih historis dan belum ikut clone.

<a id="struktur"></a>

## 8. Struktur folder dan cara membaca kode

```text
KasOrg/
├── app/
│   ├── Http/Controllers/    Alur request → service/model → respons
│   ├── Http/Requests/       Validasi server-side dan izin request
│   ├── Http/Middleware/     Status akun, role, preview, dan format JSON API
│   ├── Http/Responses/      Envelope JSON sukses/error API
│   ├── Models/              Eloquent dan relasi tabel
│   ├── Policies/            Otorisasi tindakan pada objek
│   ├── Providers/           Registrasi policy/provider
│   ├── Services/            Aturan bisnis dan perhitungan kas
│   └── Support/             Helper, termasuk perlindungan akun demo
├── bootstrap/              Boot Laravel; *.php prototype Refan juga dipertahankan
├── config/                 Konfigurasi app, auth, database, demo, disk privat
├── database/
│   ├── migrations/         Sumber resmi struktur tabel/foreign key
│   ├── seeders/            Data awal terurut
│   └── factories/          Pembentuk fixture pengujian
├── resources/
│   ├── views/              Blade layout, komponen, portal, fitur, error/preview
│   ├── css/                Entry Bootstrap dan Tailwind Vite terpisah
│   └── js/                 Entry JavaScript dan inisialisasi aset
├── public/                 Document root; css/ khusus dan build/ hasil Vite
├── routes/                 web.php, api.php dan console.php sudah ikut clone
├── tests/                  Unit/Feature tersedia; suite/runner lengkap masih lokal
├── postman/                Collection JSON 13 request, environment contoh dan README
├── docs/                   Referensi dosen tersedia; banyak dokumen tahap/arsip masih lokal
├── storage/                Session/cache/log/upload privat; bukan source publik
├── vendor/                 Dibuat composer install, tidak ikut clone; jangan diedit
├── node_modules/           Dibuat npm ci, tidak ikut clone; jangan diedit
├── composer.json           Dependency PHP, autoload dan script Laravel
├── composer.lock           Versi dependency PHP terkunci
├── package.json            Dependency dan script npm/Vite
├── package-lock.json       Versi dependency frontend terkunci
├── vite.config.js          Empat entry aset Bootstrap/legacy
├── .env.example            Template konfigurasi tanpa kredensial
└── README.md               Panduan instalasi, source, testing dan demo ini
```

`bootstrap/app.php` adalah bootstrap Laravel, berbeda dari `bootstrap/login.php` prototype. `bootstrap/cache/` berisi cache generated, bukan source desain. Front controller browser adalah `public/index.php`; Artisan adalah pintu masuk CLI. Jangan mengubah file vendor/cache untuk memperbaiki aturan aplikasi.

<a id="peta-file"></a>

### Peta file inti — gunakan sebagai petunjuk membaca source

**Legenda distribusi:** tautan relatif ke file/folder dipilih dari path yang tercatat pada checkpoint **ad03cc5**, bukan sekadar ditemukan di laptop integrator. File tracked yang memiliki perubahan lokal tetap memiliki versi GitHub, tetapi isinya belum tentu sama dengan working tree. Tujuh path API, termasuk registrasi/exception JSON pada `bootstrap/app.php`, serta tiga file utama Postman **sudah committed/dipush**. Path tanpa tautan untuk suite regresi/runner, arsip pindahan dan dokumen tahap berarti **masih lokal/belum dipush**, bukan file yang pasti ada setelah clone. Lihat [ringkasan distribusi](#distribusi-checkpoint).

Tidak perlu menghafal semua file. Mulai dari URL yang dibuka, cari route-nya, ikuti Request/Policy, Controller, Model/Service, lalu Blade. Method adalah pekerjaan di dalam class; tabel ini menunjukkan nama **yang ada di source**, bukan fungsi baru yang harus dibuat.

### 8.1. Controller — mengatur perjalanan request

Path pada kolom pertama relatif terhadap **app/Http/Controllers/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [Controller.php](app/Http/Controllers/Controller.php) | Base class untuk controller fitur. | Class abstrak, bukan endpoint. | Controller turunan di folder ini. |
| [Auth/AuthenticatedSessionController.php](app/Http/Controllers/Auth/AuthenticatedSessionController.php) | Menampilkan form, memproses masuk dan mengakhiri sesi. | create, store, destroy; session regenerate/invalidate dan redirect sesuai role. | LoginRequest, Auth/User/Role, routes/web.php, auth/login.blade.php. |
| [DashboardController.php](app/Http/Controllers/DashboardController.php) | Mengirim pengguna yang sudah login ke portal role-nya. | __invoke; redirect admin.dashboard/anggota.dashboard. | User::isAdmin, middleware auth/active. |
| [RingkasanKasController.php](app/Http/Controllers/RingkasanKasController.php) | Menyiapkan data Dashboard, Transparansi dan daftar kas dari database. | dashboard, transparency, transactions; statistik tagihan, riwayat pribadi, filter/pagination. | RingkasanKasRequest, RingkasanKasService, Model domain, Blade portal. |
| [Admin/AnggotaController.php](app/Http/Controllers/Admin/AnggotaController.php) | Mencari/menampilkan anggota, menyimpan akun baru, edit, status dan arsip. | index, store, update, updateStatus, destroy; save membungkus operasi dan menangani konflik unique. | Request Anggota, UserPolicy, User/Role, admin/anggota/index.blade.php. |
| [IuranController.php](app/Http/Controllers/IuranController.php) | Menampilkan daftar tagihan sesuai role dan menerbitkan tagihan individual; juga menerima tambah jenis. | index, store, storeJenis; opsi anggota/jenis aktif dan periode terbuka. | IuranService, JenisIuranService, Request keuangan, Blade iuran/tagihan. |
| [Admin/JenisIuranController.php](app/Http/Controllers/Admin/JenisIuranController.php) | Mengelola daftar, edit, status dan penghapusan master tanpa merusak tagihan lama. | index, update, status, destroy, missing; hitung jumlah tagihan, redirect daftar manajemen. | JenisIuranService, Manage/UpdateJenisIuranRequest, admin/iuran/jenis/. |
| [PembayaranController.php](app/Http/Controllers/PembayaranController.php) | Menyimpan upload privat, menampilkan antrean, memverifikasi dan melayani bukti berizin. | store, index, verify, proof; hapus upload baru bila pengajuan gagal. | IuranService, Request/Policy pembayaran, disk bukti, Blade verifikasi/tagihan. |
| [Admin/TransaksiKasController.php](app/Http/Controllers/Admin/TransaksiKasController.php) | Menerima aksi tulis kas manual; daftar bacanya tetap di RingkasanKasController. | store, update, destroy; redirect dengan pesan hasil. | Save/DeleteTransaksiKasRequest, TransaksiManualService, transaksi/form.blade.php. |
| [LaporanController.php](app/Http/Controllers/LaporanController.php) | Menyiapkan laporan HTML dan unduhan PDF memakai hitungan yang sama. | __invoke, pdf, helper data; pagination HTML dan batas 2.000 transaksi PDF. | LaporanKeuanganRequest, RingkasanKasService, Dompdf, laporan/index dan pdf. |
| [Api/KasController.php](app/Http/Controllers/Api/KasController.php) | Mengembalikan kategori dan ringkasan organisasi, bukan data pribadi pengguna. | categories, summary; select kolom aman dan proyeksi saldo/grafik. | Request Api, KategoriTransaksi, RingkasanKasService, ApiResponse. |

### 8.2. Form Request — memeriksa input sebelum disimpan

Path pada kolom pertama relatif terhadap **app/Http/Requests/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php) | Memeriksa format login dan kredensial akun aktif; membatasi percobaan gagal. | rules, messages, authenticate, throttleKey; Auth::attempt dan RateLimiter. | AuthenticatedSessionController, User dan provider auth. |
| [Anggota/IndexAnggotaRequest.php](app/Http/Requests/Anggota/IndexAnggotaRequest.php) | Menolak filter/status/jumlah halaman daftar yang tidak sah. | authorize, rules, messages; q/status/per_page/page. | UserPolicy::viewAny dan AnggotaController::index. |
| [Anggota/SaveAnggotaRequest.php](app/Http/Requests/Anggota/SaveAnggotaRequest.php) | Aturan identitas bersama tambah/edit serta mengingat modal saat validasi gagal. | identityRules, authorize, prepareForValidation, rememberFormContext, failedValidation; NIM/email unique, role input dilarang. | Store/UpdateAnggotaRequest, DemoAccountProtection dan UserPolicy. |
| [Anggota/StoreAnggotaRequest.php](app/Http/Requests/Anggota/StoreAnggotaRequest.php) | Memastikan akun baru mempunyai kata sandi awal kuat dan konfirmasi. | rules; minimal 12, mixed case, angka/simbol, confirmed, maksimum 72 byte. | SaveAnggotaRequest, AnggotaController::store, cast hashed User. |
| [Anggota/UpdateAnggotaRequest.php](app/Http/Requests/Anggota/UpdateAnggotaRequest.php) | Mengizinkan edit identitas/status tanpa mengganti password atau role. | rules; password/password_confirmation prohibited. | SaveAnggotaRequest, AnggotaController::update. |
| [Anggota/UpdateStatusAnggotaRequest.php](app/Http/Requests/Anggota/UpdateStatusAnggotaRequest.php) | Membatasi perubahan status hanya aktif/nonaktif pada objek yang diizinkan. | authorize, rules, messages. | UserPolicy::updateStatus, AnggotaController::updateStatus. |
| [Keuangan/KeuanganRequest.php](app/Http/Requests/Keuangan/KeuanganRequest.php) | Menyediakan pesan dan nama field berbahasa Indonesia yang dipakai ulang. | messages, attributes; base class abstrak. | Request keuangan dan API turunannya. |
| [Keuangan/IndexKeuanganRequest.php](app/Http/Requests/Keuangan/IndexKeuanganRequest.php) | Memvalidasi pencarian/status daftar tagihan atau verifikasi. | authorize, rules, getRedirectUrl; status mengikuti halaman. | IuranController::index, PembayaranController::index. |
| [Keuangan/RingkasanKasRequest.php](app/Http/Requests/Keuangan/RingkasanKasRequest.php) | Memvalidasi periode, jenis, pencarian dan pagination ringkasan. | authorize, rules; ID periode harus tersedia atau semua. | RingkasanKasController, Periode, LaporanKeuanganRequest. |
| [Keuangan/LaporanKeuanganRequest.php](app/Http/Requests/Keuangan/LaporanKeuanganRequest.php) | Membatasi parameter laporan/PDF ke periode dan halaman. | rules, getRedirectUrl; memakai aturan periode parent. | LaporanController dan RingkasanKasRequest. |
| [Keuangan/StoreTagihanRequest.php](app/Http/Requests/Keuangan/StoreTagihanRequest.php) | Memeriksa anggota, jenis iuran, periode dan jatuh tempo; nominal ditentukan server. | authorize, rules; nominal/status dari klien dilarang. | TagihanIuranPolicy, IuranController::store, IuranService. |
| [Keuangan/StoreJenisIuranRequest.php](app/Http/Requests/Keuangan/StoreJenisIuranRequest.php) | Memvalidasi nama unik, nominal positif, deskripsi/status dan konteks redirect. | rules, messages, listingParameters, prepareForValidation, getRedirectUrl. | IuranController::storeJenis, JenisIuranService dan modal jenis. |
| [Keuangan/UpdateJenisIuranRequest.php](app/Http/Requests/Keuangan/UpdateJenisIuranRequest.php) | Memakai aturan master dengan pengecualian unique untuk record yang diedit. | authorize, rules; objek dari route binding. | StoreJenisIuranRequest, JenisIuranController::update. |
| [Keuangan/ManageJenisIuranRequest.php](app/Http/Requests/Keuangan/ManageJenisIuranRequest.php) | Memeriksa filter daftar, status dan persetujuan hapus jenis. | authorize, rules, messages; konfirmasi DELETE wajib. | JenisIuranController, JenisIuranPolicy. |
| [Keuangan/StorePembayaranRequest.php](app/Http/Requests/Keuangan/StorePembayaranRequest.php) | Memeriksa metode/tanggal/catatan dan gambar bukti transfer milik pengaju. | authorize, rules; gambar maksimal 5 MB, field sistem dilarang. | TagihanIuranPolicy::pay, PembayaranController::store. |
| [Keuangan/VerifyPembayaranRequest.php](app/Http/Requests/Keuangan/VerifyPembayaranRequest.php) | Memeriksa keputusan Admin; wajib konfirmasi penerimaan atau alasan penolakan. | authorize, rules; disetujui/ditolak, bukan status bebas. | PembayaranIuranPolicy::verify, PembayaranController::verify. |
| [Keuangan/SaveTransaksiKasRequest.php](app/Http/Requests/Keuangan/SaveTransaksiKasRequest.php) | Memeriksa nominal positif, jenis/kategori cocok, periode, tanggal dan keterangan. | authorize, rules, prepareForValidation; sumber/petugas/FK pembayaran ditetapkan server. | TransaksiKasPolicy, TransaksiKasController, TransaksiManualService. |
| [Keuangan/DeleteTransaksiKasRequest.php](app/Http/Requests/Keuangan/DeleteTransaksiKasRequest.php) | Memerlukan izin dan checkbox konfirmasi sebelum soft delete kas. | authorize, rules, messages. | TransaksiKasPolicy::delete dan TransaksiKasController::destroy. |
| [Api/KategoriTransaksiRequest.php](app/Http/Requests/Api/KategoriTransaksiRequest.php) | Menolak filter jenis kategori selain pemasukan/pengeluaran. | rules, attributes; nullable jenis. | Api/KasController::categories, KeuanganRequest. |
| [Api/RingkasanRequest.php](app/Http/Requests/Api/RingkasanRequest.php) | Menolak periode API yang tidak valid/tidak ada. | rules; periode_id opsional, ID positif existing atau semua. | Api/KasController::summary, Periode, KeuanganRequest. |

### 8.3. Middleware — menyaring siapa yang boleh lewat

Path pada kolom pertama relatif terhadap **app/Http/Middleware/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [EnsureAccountIsActive.php](app/Http/Middleware/EnsureAccountIsActive.php) | Menghentikan sesi akun nonaktif/arsip meskipun sebelumnya sudah login. | handle; logout, invalidate dan pesan login. | Alias active di bootstrap/app.php, User::isAktif. |
| [EnsureRole.php](app/Http/Middleware/EnsureRole.php) | Memastikan role session termasuk role yang diizinkan route. | handle; role salah menghasilkan 403. | Alias role, User::role, route portal/API. |
| [EnsureLocalPreview.php](app/Http/Middleware/EnsureLocalPreview.php) | Menolak preview pada environment selain local. | handle; abort 404 jika bukan local. | Alias preview.local dan route ui-preview.*. |
| [EnsureApiJson.php](app/Http/Middleware/EnsureApiJson.php) | Memaksa respons API JSON dan mengubah redirect penolakan akun menjadi 403 JSON. | handle; menetapkan Accept JSON. | routes/api.php, ApiResponse, middleware active. |

Middleware **auth** dan **guest** berasal dari Laravel; tidak ada file custom Auth palsu. Alias middleware KasOrg serta exception JSON terdaftar di [bootstrap/app.php](bootstrap/app.php).

### 8.4. Model — menghubungkan objek PHP dengan tabel

Path pada kolom pertama relatif terhadap **app/Models/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [User.php](app/Models/User.php) | Identitas login dan anggota, bukan template halaman. | role, tagihanIuran, pembayaranDiajukan/Diverifikasi, transaksiDibuat, periodeDitutup, isAdmin/isAktif; hashed dan SoftDeletes. | Tabel users, Role, Auth, CRUD, Policy, Seeder/Factory. |
| [Role.php](app/Models/Role.php) | Dua kode role yang dibaca backend. | ADMIN, ANGGOTA, users. | roles, User::role, middleware, RoleSeeder. |
| [JenisIuran.php](app/Models/JenisIuran.php) | Master nominal standar/deskripsi/aktif, bukan kewajiban pembayaran. | tagihanIuran, casts decimal:2 dan boolean. | jenis_iuran, JenisIuranService, TagihanIuran. |
| [Periode.php](app/Models/Periode.php) | Rentang tanggal, saldo awal dan status tutup buku. | penutup, tagihanIuran, cast tanggal/desimal; TERBUKA/DITUTUP. | periode, User petugas dan semua service keuangan. |
| [KategoriTransaksi.php](app/Models/KategoriTransaksi.php) | Nama kategori dan jenis pemasukan/pengeluaran. | transaksiKas; penanda is_sistem. | kategori_transaksi, transaksi manual, verifikasi, API kategori. |
| [TagihanIuran.php](app/Models/TagihanIuran.php) | Kewajiban satu anggota untuk jenis/periode tertentu. | anggota, jenisIuran, periode, pembayaranIuran, transaksiKas; BELUM_LUNAS/LUNAS. | tagihan_iuran, IuranService dan daftar tagihan. |
| [PembayaranIuran.php](app/Models/PembayaranIuran.php) | Percobaan pengajuan serta hasil verifikasi, termasuk bukti privat. | tagihanIuran, pengaju, verifikator, transaksiKas; konstanta metode/status Indonesia. | pembayaran_iuran, IuranService, PembayaranController/Policy. |
| [TransaksiKas.php](app/Models/TransaksiKas.php) | Ledger kas sah, manual atau hasil persetujuan pembayaran. | kategoriTransaksi, pembayaranIuran, tagihanIuran, pembuat, pengubah; decimal:2 dan SoftDeletes. | transaksi_kas, RingkasanKasService, IuranService, TransaksiManualService. |

### 8.5. Policy — izin terhadap tindakan dan objek

Path pada kolom pertama relatif terhadap **app/Policies/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [UserPolicy.php](app/Policies/UserPolicy.php) | Hanya Admin aktif mengelola anggota; akun Admin/demo utama dilindungi. | viewAny, create, update, updateStatus, delete. | AnggotaController, Request Anggota, DemoAccountProtection. |
| [JenisIuranPolicy.php](app/Policies/JenisIuranPolicy.php) | Mengizinkan pengelolaan master hanya untuk Admin aktif. | viewAny, create, update, delete. | Request jenis, JenisIuranService; riwayat terpakai dicek service. |
| [TagihanIuranPolicy.php](app/Policies/TagihanIuranPolicy.php) | Membedakan izin Admin menerbitkan dan Anggota membayar tagihan sendiri. | create, pay. | StoreTagihanRequest, StorePembayaranRequest dan IuranService. |
| [PembayaranIuranPolicy.php](app/Policies/PembayaranIuranPolicy.php) | Melindungi bukti milik pengaju dan keputusan verifikasi Admin. | view, verify. | PembayaranController::proof, VerifyPembayaranRequest, IuranService. |
| [TransaksiKasPolicy.php](app/Policies/TransaksiKasPolicy.php) | Melarang CRUD manual pada transaksi otomatis hasil pembayaran. | create, update, delete; sumber manual dan FK iuran kosong. | Request kas, daftar kas dan TransaksiManualService. |

### 8.6. Service — tempat aturan bisnis dipakai bersama

Path pada kolom pertama relatif terhadap **app/Services/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [RingkasanKasService.php](app/Services/RingkasanKasService.php) | Menghindari rumus saldo berbeda antarhalaman/API. | scope memilih periode, transactions menyaring kas sah, summary menghitung, rows menjaga proyeksi/privasi, rupiah memformat. | RingkasanKasController, LaporanController, Api/KasController; Periode/TransaksiKas/Kategori. |
| [IuranService.php](app/Services/IuranService.php) | Menjaga tagihan, pengajuan, keputusan dan satu pemasukan tetap konsisten. | createBill, submit, verify, openPeriod; transaction + lock + unique. | Iuran/PembayaranController dan Model tagihan/pembayaran/kas. |
| [JenisIuranService.php](app/Services/JenisIuranService.php) | Menyimpan master tanpa mengubah tagihan lama; hapus hanya jika belum dipakai. | save, setStatus, delete. | JenisIuran/IuranController, JenisIuranPolicy, tagihan terkait. |
| [TransaksiManualService.php](app/Services/TransaksiManualService.php) | Menyimpan/edit/soft delete kas manual dalam periode yang diizinkan. | save, delete, openPeriodForDate; validasi periode lama/baru dan audit petugas. | Admin/TransaksiKasController, TransaksiKasPolicy, kategori/periode/kas. |

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [app/Support/DemoAccountProtection.php](app/Support/DemoAccountProtection.php) | Menandai identitas dua demo utama agar tidak terpakai ulang/diubah lewat CRUD. | protects; konstanta EMAILS/NIMS. | UserPolicy dan SaveAnggotaRequest. |
| [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) | Mendaftarkan pemetaan otorisasi pengguna. | boot: Gate::policy(User, UserPolicy). | User dan UserPolicy. |
| [app/Http/Responses/ApiResponse.php](app/Http/Responses/ApiResponse.php) | Membuat bentuk JSON sukses/gagal yang sama. | success, error; success/message/data/errors dan no-store. | KasController, EnsureApiJson, bootstrap/app.php. |

### 8.7. Migration — urutan pembentukan tabel dan FK

Path pada kolom pertama relatif terhadap **database/migrations/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [0001_01_01_000000_create_users_table.php](database/migrations/0001_01_01_000000_create_users_table.php) | Membuat identitas dasar dan tabel teknis auth. | up/down: users, password_reset_tokens, sessions. | Ditambah atribut KasOrg setelah roles tersedia; tidak membuat users dua kali. |
| [0001_01_01_000001_create_cache_table.php](database/migrations/0001_01_01_000001_create_cache_table.php) | Infrastruktur cache bila driver database dipakai. | up/down: cache, cache_locks. | config/cache.php; bukan tabel saldo. |
| [0001_01_01_000002_create_jobs_table.php](database/migrations/0001_01_01_000002_create_jobs_table.php) | Infrastruktur queue bila dipakai konfigurasi. | up/down: jobs, job_batches, failed_jobs. | config/queue.php; bukan bukti notifikasi nyata. |
| [2026_11_03_000001_create_roles_table.php](database/migrations/2026_11_03_000001_create_roles_table.php) | Membuat master role sebelum FK pengguna ditambahkan. | kode unique, nama; nilai role diisi seeder. | Role, RoleSeeder, migration users tambahan. |
| [2026_11_03_000002_add_kasorg_fields_to_users_table.php](database/migrations/2026_11_03_000002_add_kasorg_fields_to_users_table.php) | Menambah identitas mahasiswa dan akses ke users yang sudah ada. | nim wajib/unique, role_id FK, status_akun, last_login_at, deleted_at. | User/Role dan CRUD/auth. |
| [2026_11_03_000003_create_kategori_transaksi_table.php](database/migrations/2026_11_03_000003_create_kategori_transaksi_table.php) | Membentuk kategori dengan jenis yang dapat dirujuk bersama oleh kas. | unique nama+jenis dan id+jenis. | KategoriTransaksi, FK komposit transaksi_kas. |
| [2026_11_03_000004_create_jenis_iuran_table.php](database/migrations/2026_11_03_000004_create_jenis_iuran_table.php) | Membentuk master nominal standar. | nama unique, nominal DECIMAL, deskripsi, aktif. | JenisIuran dan tagihan_iuran. |
| [2026_11_03_000004_create_periode_table.php](database/migrations/2026_11_03_000004_create_periode_table.php) | Membentuk rentang dan saldo awal/snapshot. | tahun/bulan dan rentang unique, status, petugas nullable RESTRICT. | Periode, tagihan dan service ringkasan. |
| [2026_11_03_000005_create_tagihan_iuran_table.php](database/migrations/2026_11_03_000005_create_tagihan_iuran_table.php) | Membentuk kewajiban per anggota/jenis/periode. | FK tiga induk, nominal snapshot, status, unique gabungan. | TagihanIuran, User, JenisIuran, Periode. |
| [2026_11_03_000006_create_pembayaran_iuran_table.php](database/migrations/2026_11_03_000006_create_pembayaran_iuran_table.php) | Membentuk pengajuan tunai/transfer dan riwayat percobaan. | metode, status Indonesia, bukti_path, pengaju/verifikator, nominal/tanggal. | PembayaranIuran, TagihanIuran dan User. |
| [2026_11_03_000007_create_transaksi_kas_table.php](database/migrations/2026_11_03_000007_create_transaksi_kas_table.php) | Membentuk ledger dan perlindungan relasi otomatis. | FK kategori+jenis, payment/tagihan unique nullable, DECIMAL, sumber, petugas, deleted_at. | TransaksiKas, seluruh service keuangan. |

### 8.8. Seeder — data awal, bukan transaksi demo palsu

Path pada kolom pertama relatif terhadap **database/seeders/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) | Menentukan urutan agar role tersedia sebelum user. | run: Role → User → Kategori → Jenis → Periode. | Lima seeder di bawah; batch belum satu transaksi menyeluruh. |
| [RoleSeeder.php](database/seeders/RoleSeeder.php) | Membuat tepat admin dan anggota tanpa duplikat. | run, firstOrCreate berdasarkan kode. | Role dan UserSeeder. |
| [UserSeeder.php](database/seeders/UserSeeder.php) | Membuat dua akun demo lokal dengan password privat tanpa overwrite akun existing. | run; guard local/testing, validasi dua password berbeda, pemeriksaan konflik identitas. | config/demo.php, User hashed cast, Role. |
| [KategoriTransaksiSeeder.php](database/seeders/KategoriTransaksiSeeder.php) | Membuat tiga kategori minimum termasuk pemasukan sistem iuran. | run, firstOrCreate nama+jenis. | KategoriTransaksi dan verifikasi pembayaran. |
| [JenisIuranSeeder.php](database/seeders/JenisIuranSeeder.php) | Menyiapkan Iuran Bulanan, bukan tagihan atau pemasukan. | run, firstOrCreate; nominal standar 75000.00. | JenisIuran. |
| [PeriodeSeeder.php](database/seeders/PeriodeSeeder.php) | Membuat satu periode terbuka bila belum ada, tanpa saldo palsu. | run; bulan berjalan, saldo_awal 0, konflik periode ditolak. | Periode dan filter keuangan. |

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [database/factories/UserFactory.php](database/factories/UserFactory.php) | Membuat identitas fixture uji, bukan akun website otomatis. | definition, admin, anggota, nonaktif, unverified, roleId; NIM unik dan hash password acak. | User/Role; role harus tersedia di DB testing lebih dahulu. |
| [config/demo.php](config/demo.php) | Mengambil dua password privat dari environment tanpa default. | passwords.admin dan passwords.anggota. | UserSeeder; bukan pengaturan ulang password existing. |
| [config/database.php](config/database.php) | Memilih koneksi/target operasional dari konfigurasi lokal. | Koneksi mysql/sqlite dan DB_*; tidak membaca arsip SQL. | Eloquent, migration/seeder dan phpunit.xml untuk target testing. |
| [config/auth.php](config/auth.php) | Menentukan guard session dan provider pengguna. | Guard web, provider Eloquent User. | Auth::attempt, middleware auth. |
| [config/filesystems.php](config/filesystems.php) | Menetapkan disk upload privat bukti yang tidak dilayani publik. | Disk bukti: storage/app/bukti-private, serve=false. | PembayaranController::store/proof, Policy pembayaran. |

### 8.9. Blade — tampilan data, form dan komponen bersama

Path pada kolom pertama relatif terhadap **resources/views/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [layouts/app.blade.php](resources/views/layouts/app.blade.php) | Kerangka portal dengan identitas session dan pemilih Bootstrap/legacy. | @yield content, @stack styles/scripts, pemilihan jalur berdasarkan route. | Sidebar/topbar, halaman portal, frontend/bootstrap-assets. |
| [layouts/guest.blade.php](resources/views/layouts/guest.blade.php) | Kerangka login dan error tanpa navigasi portal. | Font/ikon, Bootstrap assets dan slot konten. | auth/login dan errors/403,404,500. |
| [auth/login.blade.php](resources/views/auth/login.blade.php) | Form email/password dan toggle sesuai desain Refan. | POST login.store + CSRF; error dan email lama; preview tidak memproses login. | AuthenticatedSessionController, guest layout, kasorg-login.css. |
| [admin/dashboard.blade.php](resources/views/admin/dashboard.blade.php) | Empat kartu, grafik, panel perhatian dan transaksi terbaru Admin. | scope, summary, billStats, activeMembers, transactions. | RingkasanKasController, partial bootstrap/keuangan, CSS Dashboard Admin. |
| [anggota/dashboard.blade.php](resources/views/anggota/dashboard.blade.php) | Ringkasan organisasi dan kewajiban/riwayat anggota login. | scope/summary, billStats, personalBills, personalPayments. | RingkasanKasController, personal-dashboard dan CSS Anggota. |
| [admin/anggota/index.blade.php](resources/views/admin/anggota/index.blade.php) | Daftar, filter, modal tambah/edit/status/arsip dengan action Laravel asli. | members/stats/filters/formMember, CSRF, method spoofing, toggle dua password. | AnggotaController, Request Anggota, pagination dan kasorg-members.css. |
| [admin/anggota/pagination.blade.php](resources/views/admin/anggota/pagination.blade.php) | Pintu pagination reusable untuk konsumen Bootstrap atau legacy. | Memilih partial sesuai argumen bootstrap. | Bootstrap/legacy pagination dan daftar anggota/keuangan. |
| [admin/iuran/jenis/index.blade.php](resources/views/admin/iuran/jenis/index.blade.php) | Daftar dan modal CRUD jenis, jumlah tagihan, status dan konfirmasi hapus. | types/filters, form partial, x-bootstrap.modal, modal-scripts. | JenisIuranController, JenisIuranService dan CSS jenis. |
| [admin/iuran/jenis/form.blade.php](resources/views/admin/iuran/jenis/form.blade.php) | Field tambah/edit jenis yang dipakai bersama. | nama, nominal, deskripsi, aktif, old/error. | Index jenis dan Store/UpdateJenisIuranRequest. |
| [admin/iuran/index.blade.php](resources/views/admin/iuran/index.blade.php) | Penerbitan tagihan individual dan daftar/riwayat kewajiban. | bills, members, types, periods dan filter/modal. | IuranController, Request tagihan, komponen keuangan legacy. |
| [anggota/tagihan/index.blade.php](resources/views/anggota/tagihan/index.blade.php) | Tagihan milik user login, modal pengajuan dan riwayat/retry. | Form multipart metode/tanggal/bukti/catatan; nominal bukan input bebas. | IuranController, PembayaranController, history dan scripts legacy. |
| [admin/pembayaran/verifikasi.blade.php](resources/views/admin/pembayaran/verifikasi.blade.php) | Antrean/detail, bukti berizin dan keputusan manual Admin. | payments; konfirmasi penerimaan atau alasan penolakan. | PembayaranController, VerifyPembayaranRequest, endpoint pembayaran.bukti. |
| [admin/transaksi/index.blade.php](resources/views/admin/transaksi/index.blade.php) | Daftar asli dan tombol CRUD hanya untuk kas manual yang diizinkan. | summary/scope, pagination/filter, manageable, form partial. | RingkasanKasController, TransaksiKasController, TransaksiKasPolicy. |
| [admin/transaksi/form.blade.php](resources/views/admin/transaksi/form.blade.php) | Field kas manual yang mengikuti kategori/periode sebenarnya. | jenis, kategori_transaksi_id, periode_id, tanggal, nominal, keterangan. | Index transaksi, SaveTransaksiKasRequest dan service manual. |
| [laporan/index.blade.php](resources/views/laporan/index.blade.php) | Laporan kedua role dengan kartu, grafik, tabel dan tombol PDF. | Periode/Terapkan/Unduh PDF, data summary dan transactions. | LaporanController, periode-filter/arus-kas, partial kas/alokasi. |
| [laporan/pdf.blade.php](resources/views/laporan/pdf.blade.php) | Template HTML khusus untuk Dompdf, bukan halaman interaktif. | Organisasi, periode/tanggal cetak, saldo dan semua rincian sesuai batas. | LaporanController::pdf dan RingkasanKasService. |
| [laporan/periode-filter.blade.php](resources/views/laporan/periode-filter.blade.php) | Form filter periode khusus header Laporan. | GET periode_id + Terapkan. | laporan/index; terpisah dari form unduhan PDF. |
| [laporan/arus-kas.blade.php](resources/views/laporan/arus-kas.blade.php) | Grafik garis laporan berdasarkan dataset dinamis. | SVG pemasukan/pengeluaran dari summary.bars. | laporan/index dan RingkasanKasService. |
| [transparansi/index.blade.php](resources/views/transparansi/index.blade.php) | Saldo, alokasi/donut dan rincian organisasi yang aman untuk kedua role. | Filter periode/jenis dan pagination. | RingkasanKasController::transparency, partial tabel/alokasi. |
| [frontend/bootstrap-assets.blade.php](resources/views/frontend/bootstrap-assets.blade.php) | Memilih build/dev server atau fallback CDN Bootstrap. | Cek manifest dua entry Bootstrap, @vite atau CDN berpinned. | Layout app/guest, Vite dan CSS tema KasOrg. |
| [components/sidebar.blade.php](resources/views/components/sidebar.blade.php) | Memilih navigasi Bootstrap atau legacy tanpa mengganti authorization. | Prop bootstrap/role; delegasi ke varian sidebar. | Layout app dan components/bootstrap/sidebar / legacy/sidebar. |
| [components/topbar.blade.php](resources/views/components/topbar.blade.php) | Memilih topbar berdasarkan jalur aset. | Props heading/user/role; delegasi varian. | Layout app, topbar Bootstrap/legacy. |
| [components/bootstrap/sidebar.blade.php](resources/views/components/bootstrap/sidebar.blade.php) | Menu per role, route aktif, logout POST + CSRF versi Bootstrap. | Named routes; badge tertentu masih simulasi. | Wrapper sidebar dan routes/web.php. |
| [components/bootstrap/topbar.blade.php](resources/views/components/bootstrap/topbar.blade.php) | Nama/initial session serta label Admin/Anggota. | Identitas dari layout, bukan URL role. | Wrapper topbar dan layout app. |
| [components/bootstrap/modal.blade.php](resources/views/components/bootstrap/modal.blade.php) | Wadah dialog Bootstrap untuk form reusable. | id/title/slot dan tombol penutup. | Jenis Iuran dan adapter modal-scripts. |
| [components/bootstrap/feedback.blade.php](resources/views/components/bootstrap/feedback.blade.php) | Menampilkan flash sukses dan pesan error. | session success dan errors dengan output aman. | Halaman Dashboard/Jenis Iuran. |
| [components/bootstrap/status-iuran.blade.php](resources/views/components/bootstrap/status-iuran.blade.php) | Menampilkan badge status pada jalur Bootstrap. | Pemetaan label/warna, bukan update status DB. | Dashboard/partial pribadi sesuai pemanggil. |
| [bootstrap/keuangan/arus-kas.blade.php](resources/views/bootstrap/keuangan/arus-kas.blade.php) | Grafik batang Dashboard Bootstrap tanpa membuat angka baru. | summary.bars, skala/empty state. | Kedua Dashboard, RingkasanKasService. |
| [bootstrap/keuangan/periode-filter.blade.php](resources/views/bootstrap/keuangan/periode-filter.blade.php) | Pilihan periode Bootstrap dari database. | scope.periods/selected dan GET periode_id. | Kedua Dashboard. |
| [bootstrap/keuangan/personal-dashboard.blade.php](resources/views/bootstrap/keuangan/personal-dashboard.blade.php) | Dua tabel tagihan/pembayaran pribadi. | personalBills/personalPayments dan status. | Dashboard Anggota. |
| [bootstrap/keuangan/modal-scripts.blade.php](resources/views/bootstrap/keuangan/modal-scripts.blade.php) | Adapter interaksi modal finansial Bootstrap. | open/close/reopen error, fokus dan API Modal. | Jenis Iuran; jangan dimuat bersama handler legacy untuk modal sama. |
| [bootstrap/pagination.blade.php](resources/views/bootstrap/pagination.blade.php) | Tautan halaman dengan komponen Bootstrap. | paginator dan query context. | Wrapper pagination anggota. |
| [legacy/pagination.blade.php](resources/views/legacy/pagination.blade.php) | Menjaga tampilan pagination halaman belum dimigrasi. | paginator dengan styling legacy. | Wrapper pagination, daftar keuangan. |
| [keuangan/history.blade.php](resources/views/keuangan/history.blade.php) | Riwayat percobaan pembayaran suatu tagihan. | Metode/status/tanggal/alasan dan link bukti berizin. | Admin iuran dan Tagihan Anggota. |
| [keuangan/filter.blade.php](resources/views/keuangan/filter.blade.php) | Pencarian dan filter status reusable. | GET q/status. | Iuran/verifikasi legacy. |
| [keuangan/periode-filter.blade.php](resources/views/keuangan/periode-filter.blade.php) | Filter periode/jenis/pencarian versi legacy. | scope/filters, GET parameter yang dipakai halaman. | Transparansi/Transaksi dan konsumen legacy. |
| [keuangan/arus-kas.blade.php](resources/views/keuangan/arus-kas.blade.php) | Versi grafik batang sebelum konversi Bootstrap. | Dataset summary.bars yang sama. | Konsumen Dashboard legacy; bukan alasan menghapus source. |
| [keuangan/personal-dashboard.blade.php](resources/views/keuangan/personal-dashboard.blade.php) | Versi tabel pribadi legacy. | Tagihan/pembayaran pribadi. | Konsumen Dashboard legacy. |
| [keuangan/alokasi-pengeluaran.blade.php](resources/views/keuangan/alokasi-pengeluaran.blade.php) | Donut dan legenda dengan nominal/persentase kategori. | summary.allocations; aman ketika pengeluaran nol. | Transparansi dan Laporan. |
| [keuangan/tabel-kas.blade.php](resources/views/keuangan/tabel-kas.blade.php) | Rincian transaksi yang sudah diproyeksikan sesuai akses. | Tanggal, jenis, kategori, keterangan, nominal dan empty state. | Laporan/Transparansi dan RingkasanKasService::rows. |
| [keuangan/scripts.blade.php](resources/views/keuangan/scripts.blade.php) | Interaksi modal/file bukti halaman legacy. | Open/close, fokus, metode transfer dan preview file. | Iuran, pembayaran, tagihan dan transaksi legacy. |
| [components/keuangan-modal.blade.php](resources/views/components/keuangan-modal.blade.php) | Wadah dialog finansial legacy. | Slot, modal id, pesan/konteks form. | Halaman keuangan dan keuangan/scripts. |
| [components/keuangan-feedback.blade.php](resources/views/components/keuangan-feedback.blade.php) | Pesan sukses/error versi legacy. | session/errors. | Halaman keuangan legacy. |
| [components/status-iuran.blade.php](resources/views/components/status-iuran.blade.php) | Badge status versi legacy. | Label/warna sesuai status yang diterima. | Tagihan, pembayaran, riwayat dan Dashboard legacy. |
| [errors/403.blade.php](resources/views/errors/403.blade.php) | Pesan akses ditolak ramah, status tetap 403. | Tujuan aman tanpa menonaktifkan Policy. | Laravel exception renderer dan layout guest. |
| [errors/404.blade.php](resources/views/errors/404.blade.php) | Pesan URL/objek tidak ditemukan, status tetap 404. | Brand KasOrg dan tautan aman. | Exception renderer dan layout guest. |
| [errors/500.blade.php](resources/views/errors/500.blade.php) | Pesan gangguan tanpa SQL/stack trace/path rahasia. | Halaman umum saat debug nonaktif, status tetap 500. | Exception renderer, layout guest, APP_DEBUG. |

Varian [sidebar legacy](resources/views/components/legacy/sidebar.blade.php) dan [topbar legacy](resources/views/components/legacy/topbar.blade.php) dipertahankan untuk halaman tambahan. File dashboard-preview.blade.php dan */preview.blade.php adalah fixture tampilan untuk ui-preview.*, bukan query data asli. [welcome](resources/views/welcome.blade.php) adalah scaffold yang bukan route root sekarang; `resources/views/keuangan/saldo-note.blade.php` masih ada tetapi panel tambahannya tidak lagi dipanggil halaman yang direstorasi. Daftar ini **bukan instruksi menghapus file**.

### 8.10. CSS/JS — aset dan interaksi

Path pada kolom pertama relatif terhadap **resources/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [css/bootstrap.css](resources/css/bootstrap.css) | Entry CSS MVP yang benar-benar memuat Bootstrap. | Import bootstrap.min.css dan CSS KasOrg. | Vite, frontend/bootstrap-assets, public/css/kasorg-bootstrap.css. |
| [js/bootstrap-mvp.js](resources/js/bootstrap-mvp.js) | Menyediakan engine modal Bootstrap untuk halaman MVP. | Import Modal, window.bootstrap.Modal. | Modal anggota/jenis; tidak ditambah bundle CDN saat Vite dipakai. |
| [css/app.css](resources/css/app.css) | Entry utility CSS dan tema biru legacy. | Import Tailwind, @source, @theme Plus Jakarta/brand. | Vite dan layout legacy. |
| [js/app.js](resources/js/app.js) | Entry JS legacy dan inisialisasi ikon. | Import ./bootstrap, DOMContentLoaded Lucide. | Layout legacy, js/bootstrap.js. |
| [js/bootstrap.js](resources/js/bootstrap.js) | Mengatur klien HTTP Axios; bukan framework Bootstrap 5. | window.axios dan X-Requested-With. | js/app.js dan package axios. |

### CSS khusus KasOrg — menjaga desain Refan

Path pada kolom pertama relatif terhadap **public/css/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [kasorg-bootstrap.css](public/css/kasorg-bootstrap.css) | Tema/shell bersama untuk halaman Bootstrap. | Warna biru, font, sidebar/topbar, radius, spacing dan komponen. | Entry resources/css/bootstrap.css dan fallback Bootstrap. |
| [kasorg-members.css](public/css/kasorg-members.css) | Penyesuaian geometri kartu/filter/tabel/modal Data Anggota. | Scope halaman anggota agar tidak mengubah Dashboard. | admin/anggota/index.blade.php. |
| [kasorg-admin-dashboard.css](public/css/kasorg-admin-dashboard.css) | Ukuran kartu, grafik dan panel Admin sesuai source visual. | Scope ko-admin-dashboard dan responsivitas. | admin/dashboard.blade.php. |
| [kasorg-anggota-dashboard.css](public/css/kasorg-anggota-dashboard.css) | Komposisi ringkasan pribadi dan organisasi Anggota. | Scope ko-anggota-dashboard dan responsivitas. | anggota/dashboard.blade.php. |
| [kasorg-login.css](public/css/kasorg-login.css) | Menjaga dua panel, card, input dan toggle Login. | Scope ko-login-page. | auth/login.blade.php dan layout guest. |
| [kasorg-jenis-iuran.css](public/css/kasorg-jenis-iuran.css) | Proporsi daftar/filter/modal Jenis Iuran. | Scope halaman jenis. | admin/iuran/jenis/index.blade.php. |

### 8.11. Routes, konfigurasi boot dan Postman

Path pada kolom pertama relatif terhadap **routes/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [web.php](routes/web.php) | Memetakan URL halaman/form ke aksi dan pelindungnya. | GET/POST login, POST logout; auth/active/role; route CRUD dan preview local. | Controller, Request/Policy dan named route pada Blade. |
| [api.php](routes/api.php) | Dua GET JSON session yang dapat diuji Postman. | /kategori-transaksi dan /transparansi/ringkasan; name api.*. | EnsureApiJson, KasController dan bootstrap/app.php. |
| [console.php](routes/console.php) | Deklarasi command CLI scaffold. | Command inspire; bukan proses tutup buku. | Registrasi commands pada bootstrap/app.php. |

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| [bootstrap/app.php](bootstrap/app.php) | Mendaftarkan web/API/console/health, alias active/role/preview dan redirect login. | withRouting, withMiddleware, withExceptions; error API berstatus asli dan JSON disanitasi. | routes/web.php, routes/api.php, middleware dan ApiResponse yang sudah ikut clone. |
| [vite.config.js](vite.config.js) | Mendaftarkan empat entry dan plugin build. | Plugin Laravel/Tailwind, input Bootstrap/legacy terpisah. | resources/css, resources/js dan manifest public/build. |
| [postman/KasOrg-UTS.postman_collection.json](postman/KasOrg-UTS.postman_collection.json) | Request HTTP dan pemeriksaan otomatis untuk demo API/session. | 13 request; script CSRF, cookie, status, privasi dan saldo. | routes/api.php, login/logout, Dashboard Admin. |
| [postman/KasOrg-Local.example.postman_environment.json](postman/KasOrg-Local.example.postman_environment.json) | Template tanpa kredensial nyata; base_url contoh dan nilai privat/runtime kosong. | base_url, email/password, periode_id, csrf_token, api_saldo_akhir. | Collection Postman dan environment privat pengguna. |
| [postman/README.md](postman/README.md) | Panduan database lokal XAMPP/Laragon, session/CSRF, Runner dan batas bukti uji. | Redirect OFF, cookie aktif, 13 request/51 assertions; bukti SQLite dipisahkan dari pengujian MySQL/GUI yang belum dilakukan. | Tutorial Postman README ini. |

### 8.12. Tests — contoh perilaku yang seharusnya terjadi

Path pada kolom pertama relatif terhadap **tests/**.

| NAMA FILE | FUNGSI | METHOD/ISI PENTING | BERHUBUNGAN DENGAN FILE APA |
| --- | --- | --- | --- |
| `tests/Feature/AuthenticationTest.php` | Memeriksa login dua role, password salah, nonaktif, session, CSRF dan 403. | Fixture SQLite RAM; sebagian skenario browser memerlukan Chrome. | Auth Controller/Request/middleware, FinanceSchema. |
| `tests/Feature/AnggotaCrudTest.php` | Memeriksa penyimpanan/edit/status/arsip, filter, unique dan perlindungan demo. | Kasus valid/invalid dan akses; bukan transaksi data operasional. | AnggotaController, Request/Policy, User dan Blade. |
| `tests/Feature/JenisIuranCrudTest.php` | Memeriksa master, redirect/hapus dan integrasi tagihan individual. | Snapshot nominal/count/anggota target, konflik dan periode. | Controller/Service jenis dan IuranService. |
| `tests/Feature/PembayaranFlowTest.php` | Memeriksa pengajuan, upload/retry dan satu pemasukan approval. | SQLite default; mode MariaDB hanya opt-in, bukan panduan ini. | IuranService, PembayaranController, storage fake. |
| `tests/Feature/TransaksiManualTest.php` | Memeriksa manual CRUD, periode, perlindungan otomatis dan saldo. | Default SQLite; jangan aktifkan mode live MariaDB. | TransaksiManualService/Policy, RingkasanKasService. |
| `tests/Feature/RingkasanKasTest.php` | Memeriksa saldo/grafik/filter/privasi dan konsistensi berbagai halaman. | Transaksi sah, soft delete, empty state dan snapshot. | RingkasanKasService/Controller dan Blade. |
| `tests/Feature/LaporanKeuanganTest.php` | Memeriksa laporan, PDF, nominal dan proyeksi data sesuai role. | SQLite; pemeriksaan PDF/browser bergantung alat yang tersedia. | LaporanController, Dompdf dan service ringkasan. |
| `tests/Feature/KasApiTest.php` | Memeriksa JSON/status/auth/query/privasi serta konsistensi saldo. | Session asli dalam test; SQLite default, MariaDB opt-in tidak digunakan. | KasController, ApiResponse, Request/API middleware. |
| [Unit/DemoAccountConfigurationTest.php](tests/Unit/DemoAccountConfigurationTest.php) | Memeriksa konfigurasi/hash/identitas demo tanpa koneksi database. | Resolver database diblokir; mode Git index opt-in terpisah. | config/demo.php, UserSeeder, UserFactory. |
| `tests/Unit/SkemaDatabaseKentTest.php` | Membandingkan teks migration/arsip, bukan mengimpor SQL. | FK/status/constraint dan keberadaan arsip historis. | database/migrations dan docs/database/historis. |
| `tests/Support/FinanceSchema.php` | Membentuk tabel fixture hanya jika testing + SQLite :memory:. | create; guard sebelum DDL RAM. | Feature Test; bukan pengganti uji migration MariaDB. |
| `tests/Support/ChromiumPasswordCheck.php` | Helper menjalankan skenario browser pada HTML fixture. | run/runInteractive; dapat membuat output sementara saat tes dijalankan. | Authentication/CRUD/browser checks; tidak dijalankan oleh tugas README. |
| `tests/Frontend/bootstrap-render.php` | Merender Blade dengan fixture tanpa aplikasi/database aktif. | HTML fixture, pemilihan aset, baseline sebelum migrasi. | Runner Frontend dan artifacts lokal. |
| `tests/Frontend/bootstrap-browser.ps1` | Memeriksa isolasi Bootstrap/legacy dan interaksi umum browser. | Browser/viewport/JS/modal/toggle; tidak menjalankan CRUD database. | HTML fixture, manifest/build dan server aset fixture. |
| `tests/Frontend/members-visual.ps1` | Membandingkan geometri Data Anggota pada viewport sama. | Kartu/filter/tabel/modal dan screenshot. | Renderer, baseline dan kasorg-members.css. |
| `tests/Frontend/members-interactions.ps1` | Memeriksa interaksi Data Anggota terisolasi. | Modal/toggle/form; bukan penyimpanan MySQL. | Fixture anggota dan browser. |
| `tests/Frontend/admin-dashboard-visual.ps1` | Memeriksa geometri/interaksi Dashboard Admin. | Viewport desktop/mobile dan grafik/empty state fixture. | Renderer, baseline dan CSS Dashboard Admin. |
| `tests/Frontend/anggota-dashboard-visual.ps1` | Memeriksa Dashboard Anggota dengan data fixture pribadi. | Geometri, filter/navigasi dan tabel pribadi. | Renderer dan CSS Dashboard Anggota. |
| `tests/Frontend/login-visual.ps1` | Memeriksa proporsi Login, input dan toggle mouse/keyboard. | Geometri/interaksi fixture; bukan login HTTP. | Renderer dan kasorg-login.css. |
| `tests/Frontend/jenis-visual.ps1` | Memeriksa daftar/filter/modal Jenis Iuran Bootstrap. | Geometri dan interaksi fixture. | Renderer, CSS jenis dan adapter modal. |

Browser/password-toggle.js dan Browser/password-visibility.ps1 berisi skenario toggle/keyboard. TestCase.php adalah dasar Feature Test; ExampleTest.php adalah scaffold/tes ringan, bukan bukti CRUD. Runner `tests/Manual/payment-concurrency.php` dan `tests/Manual/postman-api-smoke.php` memiliki ketergantungan lingkungan/data nyata: **jangan dijalankan sebagai tes pemula atau tanpa izin**. Artefak/screenshot dan baseline visual di Frontend/artifacts/ bukan source aplikasi. Cara memilih target tes yang aman ada di [pengujian](#pengujian).

<a id="alur-kode"></a>

### 8.13. Empat contoh alur kode dan perpindahan data

**A. Login: Route → LoginRequest → AuthenticatedSessionController → Session → middleware → Dashboard**

1. Browser membuka GET /login. Route bernama login memanggil create() dan menghasilkan HTML auth/login.blade.php.
2. Pengguna mengisi form; browser mengirim POST /login (login.store), email/password dan token CSRF. Middleware web memeriksa token; Laravel menyelesaikan rules()/messages() LoginRequest sebelum method Controller menerima request.
3. store() memanggil LoginRequest::authenticate(). Di dalamnya Auth::attempt memeriksa email/password melalui provider User serta syarat status_akun aktif; password database adalah hash. RateLimiter menahan percobaan gagal berulang.
4. Setelah berhasil, Controller meregenerasi session lalu membaca role->kode dan mengirim redirect ke admin.dashboard atau anggota.dashboard. Password tidak dimasukkan ke URL dan role bukan parameter bebas dari browser.
5. Request Dashboard berikutnya membawa cookie sesi. Middleware auth, active dan role memeriksa identitas/hak akses, lalu Controller menyiapkan halaman. Session mengingat login antar-request; ia bukan tabel saldo.
6. Saat Keluar, form POST /logout + CSRF memanggil destroy(): Auth logout, session invalidate, token baru, redirect login. GET logout bukan endpoint aplikasi.

Urutan ringkas di atas bukan berarti semua logika authenticate berjalan sebelum store(): **validasi Form Request diselesaikan dahulu, sedangkan authenticate() dipanggil oleh store()**. Ini terlihat langsung pada dua file tersebut.

**B. CRUD Anggota: Route → Form Request/Policy → AnggotaController → User/Role → MySQL → Blade**

1. GET /admin/anggota dilindungi auth/active/role:admin. IndexAnggotaRequest memeriksa izin viewAny dan format q/status/per_page/page; Controller mengambil daftar User ber-role anggota, relasi role, statistik dan pagination.
2. Controller mengirim members/stats/filters/formMember ke admin/anggota/index.blade.php. Blade menampilkan nama/role yang dapat dibaca manusia, form dan modal; tidak menjalankan SQL sendiri.
3. Tombol Simpan mengirim POST /admin/anggota (admin.anggota.store) dengan token CSRF. StoreAnggotaRequest mewarisi aturan identitas SaveAnggotaRequest dan menambah aturan password awal/konfirmasi. authorize() memanggil UserPolicy; format salah menghasilkan pesan Indonesia tanpa menyimpan User.
4. store() mengambil Role anggota dari database dan memakai data tervalidasi untuk User::create dalam transaksi. Cast hashed pada User membuat hash; role ditetapkan server, bukan pilihan role bebas dari form.
5. MySQL memeriksa unique NIM/email serta FK role. Berhasil: redirect admin.anggota dengan pesan sukses; GET daftar berikutnya membaca row terbaru. Bukan hanya menambah baris HTML.
6. PUT /admin/anggota/{anggota} memakai UpdateAnggotaRequest untuk edit, PATCH .../status mengubah status, DELETE ... mengarsipkan. Route model binding mengambil objek, Policy memeriksa objek tersebut, dan update/arsip mengunci row serta memeriksa izin lagi. Soft delete menjaga row/FK finansial; dua demo utama dilindungi.

Laravel memanggil authorize()/rules pada Form Request sebelum aksi Controller; Policy bisa diperiksa di tahap itu dan kembali di operasi terkunci. Jadi Policy bukan tombol yang cukup disembunyikan melalui CSS.

**C. Dashboard: Route → RingkasanKasController → RingkasanKasService → Model → statistik → Blade**

1. GET /admin/dashboard atau /anggota/dashboard melewati middleware role serta RingkasanKasRequest. periode_id yang salah ditolak, bukan diubah menjadi periode contoh.
2. dashboard() memanggil scope() untuk mendapatkan periode yang dipilih, batas tanggal dan saldo awal. Model Periode membaca tabel periode.
3. summary() memakai transactions() untuk membaca Model TransaksiKas yang sah dalam rentang tanggal. Soft delete dikecualikan; pemasukan otomatis harus terhubung dengan pembayaran disetujui yang sesuai.
4. Service menghitung opening/income/expense/closing serta dataset bars/allocations menggunakan string desimal/BCMath. Nominal tidak dikarang oleh Blade.
5. Controller menambahkan jumlah anggota aktif (Admin), total tagihan/menunggu, transaksi terbaru, serta personalBills/personalPayments yang dibatasi pengguna login untuk Anggota.
6. View admin.dashboard atau anggota.dashboard menerima array/collection tersebut; partial Bootstrap menggambar kartu, batang dan tabel. Jika belum ada ledger sah, nol/empty state tetap ditampilkan, bukan data mock.

**D. API: route API → middleware → Request → Controller → JSON → Postman**

1. Postman mengirim GET /api/kategori-transaksi atau /api/transparansi/ringkasan. Registrasi API berasal dari bootstrap/app.php, bukan hanya nama folder.
2. EnsureApiJson memastikan format JSON, middleware web menyediakan session/cookie, auth:web/active/role memeriksa sesi. Belum login menghasilkan 401; JSON bukan alasan membuka endpoint tanpa izin.
3. KategoriTransaksiRequest memvalidasi jenis, atau RingkasanRequest memvalidasi periode_id. Parameter salah menghasilkan JSON 422 dengan errors field.
4. KasController::categories membaca kolom id/nama/jenis kategori. summary memakai RingkasanKasService yang juga digunakan Dashboard; bukan rumus saldo API baru.
5. ApiResponse::success mengemas data menjadi success/message/data/errors dengan HTTP 200. Exception API dirender konsisten di bootstrap/app.php, tanpa stack trace/SQL.
6. Postman menampilkan JSON pada Body dan mengevaluasi assertions pada Test Results/Runner. Cookie sesi diperoleh melalui login resmi; GET API tidak memerlukan token tulis, tetapi POST login/logout tetap memerlukan CSRF yang cocok.

<a id="database"></a>

## 9. Database, relasi, dan arsip Kent

| Komponen | Kedudukan yang disepakati |
| --- | --- |
| MariaDB kasorg_dev | Database website pada komputer integrasi; laptop anggota memakai database lokalnya sendiri. Koneksi dipilih melalui config/database.php dan environment, bukan file SQL. |
| [database/migrations/](database/migrations/) | **Satu sumber struktur resmi instalasi Laravel**: kolom, PK, FK dan constraint. Bukan perintah rutin setiap menyalakan server. |
| [database/seeders/](database/seeders/) | Sumber data awal; bukan salinan seluruh data integrator atau alat reset akun existing. |
| [app/Models/](app/Models/) | Penghubung Eloquent ke tabel, cast dan relasi; tidak membangun database otomatis saat login. |
| `docs/database/historis/sakuorg.sql` | Rancangan awal Kent, ada sebagai arsip di working tree; pemindahannya belum committed. Pada checkpoint Git masih tercatat sebagai `database/sakuorg.sql`. |
| `docs/database/historis/kasorg.sql` | Adaptasi Laravel **masih ada secara lokal**, belum termasuk checkpoint clone. Bukan file asli Kent atau bukti persetujuan Kent atas seluruh adaptasi. |

**Kedua SQL bukan database aktif, bukan sumber koneksi runtime, dan bukan installer resmi. Jangan diimpor ke database Laravel yang aktif.** Arsip mengandung DDL destruktif; komentar import lama di dalamnya tidak mengganti keputusan instalasi melalui migration.

**Peringatan privasi arsip Kent:** audit sebelumnya menemukan data pengguna dan hash password pada SQL historis yang sudah memiliki versi di GitHub. Arsip tersebut berpotensi mengekspos data pribadi; jangan menganggap hash aman untuk dipublikasikan. Penanganannya memerlukan koordinasi pemilik data dan persetujuan kelompok secara terpisah, tanpa menghapus atribusi Kent. Pembaruan README ini **tidak membaca ulang, menampilkan data/hash, memindahkan, menghapus atau memublikasikan ulang SQL**. Jangan menyertakannya dalam paket instalasi/Postman atau mengimpor arsip untuk menyalin akun/data integrator.

`tests/Unit/SkemaDatabaseKentTest.php` masih lokal/belum committed dan membaca SQL adaptasi **sebagai teks untuk pengujian statis** dan memeriksa keberadaan arsip Kent. Ini bukan eksekusi/import SQL. Rencana lama menghapus SQL adaptasi/melepas dependensi tes **belum dilakukan**; jangan menganggap rencana sebagai kondisi aktual atau menghapus arsip. Backup eksternal belum dikonfirmasi.

### Tabel aplikasi dan primary key

**Primary key (PK)** adalah identitas unik setiap row, umumnya kolom `id`. **Foreign key (FK)** menunjuk PK/unique key tabel lain dan mencegah relasi tidak sah. Eloquent menerjemahkan relasi itu menjadi objek, misalnya `$tagihan->anggota->name`, bukan ID mentah yang harus ditebak pengguna.

| Tabel / Model | PK dan isi penting dari migration | Fungsi |
| --- | --- | --- |
| roles / Role | id; kode unik, nama | Dua role backend admin/anggota; satu role memiliki banyak users. |
| users / User | id; name, nim unik, email unik, password, role_id, status_akun, deleted_at | Akun login sekaligus data anggota. Password adalah hash; soft delete menandai deleted_at, tidak menghapus row. |
| kategori_transaksi / KategoriTransaksi | id; nama, jenis, is_sistem; unique nama+jenis dan id+jenis | Kelompok pemasukan/pengeluaran; mendukung FK komposit kategori+jenis ledger. |
| jenis_iuran / JenisIuran | id; nama unik, nominal DECIMAL(15,2), deskripsi nullable, aktif | Aturan/nominal standar untuk membuat tagihan baru, bukan row kewajiban setiap anggota. |
| periode / Periode | id; tahun, bulan, tanggal_mulai/selesai, saldo_awal, status, penutupan | Rentang laporan/periode tagihan; unique tahun+bulan serta pasangan batas tanggal. |
| tagihan_iuran / TagihanIuran | id; user_id, jenis_iuran_id, periode_id, nominal, jatuh_tempo, status, lunas_at | Kewajiban individual; nominal disimpan sebagai snapshot. Unique anggota+jenis+periode. |
| pembayaran_iuran / PembayaranIuran | id; tagihan_iuran_id, diajukan_oleh, metode, nominal, tanggal_bayar, bukti_path, status, diverifikasi_oleh | Pengajuan/verifikasi; beberapa percobaan dapat merujuk satu tagihan. |
| transaksi_kas / TransaksiKas | id; tanggal, jenis, kategori_transaksi_id, keterangan, nominal, sumber, dibuat_oleh/diubah_oleh, deleted_at; referensi tagihan/pembayaran nullable unik | Ledger kas sah; transaksi manual/otomatis dibedakan. Soft delete dikeluarkan dari saldo aktif. |

Migration/seeder di GitHub **bukan salinan database MySQL integrator**. Komputer teman mendapat struktur dan data awal ketika menjalankannya pada database baru miliknya; tidak mendapat anggota, bukti transfer atau uang percobaan dari kasorg_dev.

### Relasi utama dari migration/model

| Relasi | Makna dan pengaman |
| --- | --- |
| users.role_id → roles.id | Dua role admin/anggota; NIM dan email users wajib unik, akun aktif/nonaktif dan SoftDeletes. |
| tagihan_iuran.user_id → users.id | Satu anggota mempunyai banyak tagihan; relasi anggota() dapat membaca user arsip untuk riwayat. |
| tagihan_iuran.jenis_iuran_id → jenis_iuran.id | Nominal standar disalin ke tagihan saat dibuat; edit master tidak mengubah tagihan lama. |
| tagihan_iuran.periode_id → periode.id | Unique user_id + jenis_iuran_id + periode_id mencegah tagihan ganda. |
| pembayaran_iuran.tagihan_iuran_id → tagihan_iuran.id | Satu tagihan dapat memiliki beberapa percobaan pembayaran; status menunggu/disetujui/ditolak, metode tunai/transfer. |
| transaksi_kas → kategori_transaksi | FK komposit kategori_transaksi_id + jenis menjaga kategori cocok dengan pemasukan/pengeluaran. |
| transaksi_kas → tagihan_iuran/pembayaran_iuran | Dua FK nullable masing-masing unik untuk pemasukan otomatis; ledger manual tidak mengisi keduanya. |
| Referensi petugas → users | Pembuat/pengubah/verifikator/penutup menjaga jejak petugas; FK domain menggunakan RESTRICT. Nullable pada petugas tertentu bukan berarti ON DELETE SET NULL. |

`transaksi_kas` **tidak memiliki periode_id**; laporan mengelompokkan berdasarkan tanggal transaksi dalam rentang periode. Migration dasar membuat users sekali, migration KasOrg menambahkan atributnya. Ada tabel teknis session/cache/queue/reset bawaan Laravel; keberadaannya bukan bukti semua layanan itu aktif atau reset password mandiri tersedia.

Contoh membaca nama relasi, **bukan perintah setup/penulisan dan tidak dijalankan saat menyusun README**:

```php
$tagihan = \App\Models\TagihanIuran::with(['anggota', 'jenisIuran', 'periode'])
    ->where('user_id', $userLogin->id)
    ->get();

foreach ($tagihan as $item) {
    $namaAnggota = $item->anggota->name;
    $namaIuran = $item->jenisIuran->nama;
}
```

`$userLogin` berarti User yang sudah terautentikasi dan diotorisasi; jangan menggantinya dengan user_id bebas dari URL. Eager loading with() menghindari mengambil relasi berulang per baris. Controller operasional tetap menerapkan Request/Policy dan pagination sesuai fiturnya.

### Mengapa berbeda dari rancangan awal Kent?

SQL awal memakai iuran sebagai satu konsep pencatatan, role ENUM pada users, serta procedure/trigger/view. Integrasi memisahkan **jenis_iuran → tagihan_iuran → pembayaran_iuran → transaksi_kas**, memakai tabel roles dan atribut autentikasi User saat ini, serta menempatkan proses verifikasi atomik pada Service Laravel. Konsep periode, kategori dan ledger dari rancangan Kent tetap menjadi fondasi.

Pemisahan mendukung standar nominal vs kewajiban tetap, pengajuan ulang, bukti privat dan pencegahan pemasukan ganda. Ini **pilihan adaptasi**, bukan kewajiban Laravel mengganti rancangan Kent. Backend saat ini tidak langsung cocok dengan skema SQL awal tanpa pemetaan. Notifikasi, arsip PDF permanen, audit_log lengkap dan procedure tutup buku awal belum dibawa sebagai fitur setara; jangan mengklaim seluruh adaptasi telah disetujui Kent. Detail: `docs/REVIEW_DATABASE_BERSAMA_KENT.md`.

### Sumber perhitungan uang

**Saldo akhir = saldo awal + pemasukan sah − pengeluaran sah.** Nominal disimpan DECIMAL(15,2), model menggunakan decimal:2 dan service memakai string/BCMath, bukan float untuk saldo. RingkasanKasService dipakai bersama Dashboard, Transaksi, Transparansi, Laporan/PDF dan API.

Tagihan belum dibayar serta pembayaran menunggu/ditolak **tidak menambah kas**. Persetujuan Admin mencatat satu pemasukan dan pelunasan bersama dalam DB transaction, row locking dan constraint unik. Transaksi soft delete tidak dihitung; transaksi otomatis dilindungi dari CRUD manual. Grafik/alokasi berasal dari ledger sah; nol tetap nol/empty state. Pilihan semua periode memakai satu saldo awal periode paling awal, bukan menjumlah saldo awal setiap bulan. Risiko pengakuan lintas periode dijelaskan di [batas fitur](#batas).

<a id="postman"></a>

## 10. API JSON dan Postman 13 request

**SUDAH TERSEDIA DALAM CLONE BRANCH PADA ad03cc5.** Paket **7 path API + 3 file Postman** sudah committed/dipush: route, Controller, middleware, dua Request, response helper dan registrasi API, bersama collection JSON, environment contoh dan [README Postman](postman/README.md). Ikuti instalasi [XAMPP](#xampp) atau [Laragon](#laragon) pada database lokal sendiri; jangan memakai kasorg_dev integrator. Paket ini siap **dicoba**, bukan klaim MySQL/Postman GUI laptop anggota sudah lulus. Lihat [bukti SQLite yang terpisah](#hasil-uji-api).

Base URL contoh: `http://127.0.0.1:8000`. Prefix `/api` berasal dari registrasi [routes/api.php](routes/api.php) pada [bootstrap/app.php](bootstrap/app.php), keduanya sudah termasuk checkpoint. Setelah dependency, konfigurasi privat dan database sendiri siap, periksa melalui terminal kedua `php artisan route:list --path=api`; harapannya dua route GET|HEAD berikut terdaftar. Perintah tersebut hanya memeriksa pendaftaran, bukan membuktikan query/login berhasil.

| Method / endpoint | Query opsional | Hasil |
| --- | --- | --- |
| GET `/api/kategori-transaksi` | jenis=pemasukan atau pengeluaran | ID, nama, jenis kategori; bukan data pengguna. |
| GET `/api/transparansi/ringkasan` | periode_id kosong/default, ID periode tersedia, atau semua | Periode, saldo awal/masuk/keluar/akhir, jumlah transaksi, grafik bulanan dan alokasi. |

Keduanya memakai **session/cookie Laravel** dengan web, auth:web, active dan role:admin,anggota. Bukan endpoint publik, bukan Sanctum/bearer token. Login/logout tetap di `/login` dan `/logout` dengan CSRF; GET API membaca cookie sesi login. Tidak ada endpoint untuk melewati Policy bukti transfer atau mengubah keuangan.

Envelope JSON konsisten: success, message, data, errors. **200** berhasil; **401** tanpa sesi; **403** role/status tidak berhak; **422** parameter tidak valid; **404** endpoint tidak ada; **405** metode salah; **500** error server disanitasi. Login/logout form sukses menghasilkan **302**, bukan 200. Error CSRF form dapat menghasilkan **419**.

### Contoh request dan respons — ILUSTRASI

Kirim melalui Send di Postman setelah mengikuti login/cookie di bawah. Contoh ini **bukan request yang dijalankan dalam tugas README**, bukan data kasorg_dev yang baru diambil, dan ID/jumlah aktual bisa berbeda.

~~~http
GET http://127.0.0.1:8000/api/kategori-transaksi?jenis=pengeluaran
Accept: application/json
~~~

Respons kategori **ILUSTRASI** HTTP 200 (nama kategori mengikuti data lokal):

```json
{
  "success": true,
  "message": "Kategori transaksi berhasil diambil.",
  "data": [{"id": 3, "nama": "Kegiatan", "jenis": "pengeluaran"}],
  "errors": null
}
```

Nominal ringkasan adalah **string desimal dua angka**, bukan float atau teks Rp. API tidak mengirim password, NIM, email anggota, atau bukti transfer. Respons 401 memakai success=false, data=null dan pesan Silakan login terlebih dahulu; 422 menyertakan errors per field.

Contoh request ringkasan **ILUSTRASI** untuk semua periode, tetap memakai cookie login:

~~~http
GET http://127.0.0.1:8000/api/transparansi/ringkasan?periode_id=semua
Accept: application/json
~~~

Baca data.saldo_awal/pemasukan/pengeluaran/saldo_akhir dan grafik_bulanan/alokasi_pengeluaran pada hasilnya. Jangan mengubah contoh menjadi saldo palsu ketika database kosong. Contoh respons tanpa sesi **ILUSTRASI** HTTP 401:

~~~json
{
  "success": false,
  "message": "Silakan login terlebih dahulu.",
  "data": null,
  "errors": null
}
~~~

| Status | Makna untuk pemula | Apa yang diperiksa |
| --- | --- | --- |
| 200 | Request baca berhasil. | Body JSON dan nominalnya, bukan status saja. |
| 302 | Login/logout form berhasil mengarahkan tujuan. | Header Location; matikan follow redirects agar terlihat. |
| 401 | Belum mempunyai sesi login yang berlaku untuk API. | Login di Postman dan cookie host yang benar. |
| 403 | Identitas ada tetapi role/status/izin tidak sesuai. | Role aktif dan akses yang memang diizinkan; jangan melewati middleware. |
| 404 | URL/objek tidak ditemukan, atau penguji memakai revisi lama sebelum API terintegrasi. | Path endpoint, checkpoint dan kelengkapan route. Kasus 11 memang mengharapkan 404. |
| 419 | Token form tidak cocok dengan sesi/kedaluwarsa. | CSRF dan cookie; ambil token baru setelah login untuk logout. |
| 422 | Parameter gagal validasi, bukan error database yang harus direset. | Body errors.jenis/errors.periode_id sesuai request. |
| 500 | Ada exception server. | Baca log privat bersama pengelola, jangan membagikan rahasia atau reset data. |

### Impor dan isi environment

1. Pastikan clone memuat checkpoint ad03cc5 atau pembaruan yang disepakati, database lokal sendiri sudah disiapkan, MySQL aktif, dan Artisan berjalan. Database existing tidak perlu dimigrasikan/di-seed lagi.
2. Postman → Import → pilih [postman/KasOrg-UTS.postman_collection.json](postman/KasOrg-UTS.postman_collection.json) dari folder clone. Update/replace salinan lama agar script sinkronisasi tidak tertukar.
3. Import [postman/KasOrg-Local.example.postman_environment.json](postman/KasOrg-Local.example.postman_environment.json), lalu pilih environment **KasOrg Local — contoh tanpa kredensial**.
4. Isi variabel berikut hanya pada nilai **lokal/privat**, bukan file contoh yang akan diunggah:

   | Variabel | Pengisian/pemakaian |
   | --- | --- |
   | base_url | URL server tanpa slash terakhir, contoh http://127.0.0.1:8000. |
   | email, password | Kredensial **Admin** secara privat; collection membandingkan Dashboard Admin. |
   | periode_id | Kosong untuk default, semua, atau ID yang benar-benar tersedia; dipakai bersama request 07/08. |
   | csrf_token | Otomatis dari form 03, diperbarui dari sesi aktif 08, dikirim dan dihapus pada 12. Jangan isi token buatan. |
   | api_saldo_akhir | Otomatis disimpan request 07 untuk dibandingkan dengan kartu Dashboard pada 08. |

5. Jangan mencampur localhost/127.0.0.1 atau port/protokol antar-request. Jika run lama berhenti setelah login, bersihkan cookie **hanya host development tersebut** melalui menu Cookies. Browser login tidak otomatis membuat Postman login.
6. Pertahankan **Automatically follow redirects OFF** dan cookie jar aktif pada 03–13. Request 01–02 memang menonaktifkan cookie untuk skenario guest; 13 harus memakai cookie hasil logout agar benar-benar menguji sesi berakhir.

Menandai password sebagai secret hanya menyamarkan tampilan. Jangan ekspor/sinkronkan environment yang sudah berisi password, cookie atau token ke repository/workspace bersama. **Paket utama cukup collection JSON, environment JSON contoh dan README Postman**; workspace YAML masih lokal, tidak ikut commit ad03cc5 dan tidak diperlukan untuk impor atau menjalankan collection.

### Belajar memakai Send sebelum menjalankan Runner

Untuk memahami prosesnya, pilih request yang sudah ada di collection, bukan membuat endpoint baru:

1. **Siapkan server:** buka terminal root proyek, jalankan php artisan serve --host=127.0.0.1 --port=8000 seperti tutorial instalasi; MySQL tetap aktif. Jangan menggunakan URL phpMyAdmin sebagai base_url.
2. Pilih **03 Ambil form login dan CSRF**, klik **Send**. Harapan: 200 HTML. Script Tests/Post-response membaca token asli dari form ke environment csrf_token; cookie jar menyimpan sesi awal. Tidak perlu menyalin token ke source atau menciptakan token sendiri. Jika 302, sesi run lama masih aktif: selesaikan logout atau bersihkan cookie host development itu.
3. Pilih **04 Login Laravel asli**. Pada Body → x-www-form-urlencoded, collection sudah berisi email={{email}}, password={{password}}, _token={{csrf_token}}. Isi kredensial hanya melalui environment lokal, klik Send. Harapan: **302** Location menuju /admin/dashboard dan cookie sesi login diperbarui.
4. Pilih **05** untuk kategori, **06** untuk filter, lalu **07** untuk ringkasan. Klik Send dan lihat Body → tampilan JSON/Pretty bila tersedia. GET ini menggunakan cookie jar, bukan Bearer Token. Request 07 menyimpan saldo akhir yang valid ke environment.
5. Pilih **08 Dashboard konsisten dengan API**, Send. Script membandingkan saldo kartu dengan request 07 dan mengambil **token sesi aktif sesudah login**. Token dari 03 tidak boleh diasumsikan masih berlaku untuk logout.
6. Pilih **12 Logout POST dan CSRF**, Send. Body sudah mengirim _token terbaru, cookie tetap aktif; harapan 302 ke /login. Script membuang csrf_token runtime setelah logout.
7. Pilih **13 Setelah logout**, Send tanpa mematikan cookie jar. Harapan 401: sesi hasil logout tidak dapat membuka API. Jika cookie sengaja dibuang untuk membuat 401, itu bukan bukti logout benar.

Urutan belajar Send ini menjelaskan sesi; untuk bukti collection lengkap, jalankan semua request berikut melalui Runner dari awal. Jangan menampilkan tab berisi password/cookie/token ketika merekam screenshot kelompok.

### Urutan request dan Collection Runner

Pilih collection → **Run collection / Runner**, pilih environment tadi, satu iteration tanpa CSV/JSON iteration override, lalu jalankan **01–13 berurutan**:

| No | Request setelah base_url | Status yang diharapkan / pemeriksaan |
| --- | --- | --- |
| 01 | GET /api/kategori-transaksi tanpa cookie | 401, penolakan JSON. |
| 02 | GET /api/transparansi/ringkasan tanpa cookie | 401. |
| 03 | GET /login | 200; ambil token CSRF dari HTML dan cookie sesi awal. |
| 04 | POST /login | Form-urlencoded email/password/_token dari environment; 302 ke Dashboard Admin. |
| 05 | GET /api/kategori-transaksi | 200; kategori asli dan envelope/privasi. |
| 06 | GET /api/kategori-transaksi?jenis=pengeluaran | 200; hanya kategori pengeluaran. |
| 07 | GET /api/transparansi/ringkasan?periode_id={{periode_id}} | 200; rumus desimal/grafik, simpan saldo akhir. |
| 08 | GET /admin/dashboard?periode_id={{periode_id}} | 200; saldo sama dengan API, ambil CSRF **sesi sesudah login**. |
| 09 | GET /api/kategori-transaksi?jenis=tidak-valid | 422; errors.jenis. |
| 10 | GET /api/transparansi/ringkasan?periode_id=abc | 422; errors.periode_id. |
| 11 | GET /api/tidak-ada | 404 JSON; kasus negatif, bukan endpoint yang harus dibuat. |
| 12 | POST /logout | Form-urlencoded _token aktif dari 08 + cookie login; 302 ke /login. |
| 13 | GET /api/kategori-transaksi dengan cookie setelah logout | 401; sesi lama tidak membuka API. |

Baca tab **Body** untuk JSON, status HTTP untuk hasil transport, dan **Test Results/Runner** untuk assertions. Respons 422/401/404 pada kasus negatif adalah hasil yang diharapkan, bukan otomatis kegagalan collection. HTTP 200 saja tidak membuktikan nominal benar; script 07 memeriksa rumus dan 08 membandingkan saldo Rupiah Dashboard hingga sen.

Target collection tersinkron: **13 request / 51 assertions / 0 gagal**. [README Postman](postman/README.md) memisahkan catatan historis integrator dari pengujian paket API **SQLite terisolasi** yang lulus CLI 13/51; keduanya **tidak dijalankan ulang dalam tugas README ini**. Postman GUI di laptop anggota belum dinyatakan lulus. Collection memakai Admin; dukungan Anggota/403 diuji terpisah dalam 20/20 pemeriksaan HTTP, bukan seluruhnya tercakup 13 request. Runner historis `tests/Manual/postman-api-smoke.php` masih lokal, terikat lingkungan integrator dan bukan langkah instalasi/test pemula. Jangan menjalankan runner Manual atau mode `KASORG_*_MARIADB` untuk mengikuti panduan ini.

<a id="pengujian"></a>

## 11. Pengujian dan demo UTS

<a id="hasil-uji"></a>

### 11.1. Hasil pengujian terisolasi — dua tahap berbeda

#### A. Paket portal — 9 November 2026

Sumber yang diuji adalah **HEAD f3aa6c4 + 34 path index**, kemudian dicatat/dipush sebagai **15e32bc**. Ekspor dilakukan menggunakan checkout-index ke folder baru terpisah, **bukan** menyalin seluruh working tree atau memakai database integrator. Fingerprint staged diff saat pengujian: `5466bb112e09fecd9e7d8acad3d7395d2a741a6c711df9437e8f392d59a5b032`.

| Pemeriksaan yang benar-benar dilakukan | Hasil dan batas |
| --- | --- |
| Ekspor sumber | 212 file index; tanpa .env aktif, vendor/node_modules lama, data runtime atau perubahan di luar staging. |
| Platform | PHP 8.2.4 dengan BCMath/PDO SQLite; Composer platform check lulus. Node 22.23.4/npm 10.9.9. |
| Composer install | Berhasil dari composer.lock; 118 instalasi, tanpa update dependency. |
| npm ci | Berhasil memasang 90 package dari package-lock.json pada salinan pengujian. |
| Vite build | npm run build berhasil dengan Vite 7.3.7; empat entry manifest dan file output diverifikasi. |
| Migration asli | **11/11 berhasil** pada file SQLite baru di folder pengujian; bukan hanya schema fixture RAM. |
| Seeder | Berhasil; dua akun demo aktif, password ter-hash/cocok masing-masing dan tidak saling berlaku. Tidak mencetak password/hash. |
| HTTP/database | **45/45 lulus**: login dua role, password salah, CSRF, token setelah login, navigasi, CRUD/persistensi, unique/validasi, status, soft delete, pagination 5+1, filter, perlindungan demo, 403, 404 dan logout. |
| Browser | Lulus pada cakupan Chrome headless yang diuji: login, modal/form tambah-edit-hapus, toggle mouse dan keyboard, logout/403; pemisahan aset Bootstrap/legacy; smoke desktop 1440×900 dan mobile 390×844. Bukan audit pixel seluruh halaman. |
| Integritas | Tidak ada pelanggaran FK SQLite; source salinan tidak diubah untuk meluluskan tes. Source proyek asli, staging dan 77 perubahan di luar paket tetap utuh; kasorg_dev tidak diakses. |

Hasil **45/45** di atas hanya untuk paket portal 15e32bc; API/Postman belum termasuk sumber yang diuji pada tahap itu. Integrasi API kini sudah dipush pada ad03cc5 dengan bukti tahap terpisah di bawah. Riwayat portal dan fingerprint tersebut tidak diganti seolah-olah merupakan pengujian commit terbaru secara menyeluruh.

<a id="hasil-uji-api"></a>

#### B. Paket API/Postman — 9 November 2026

Salinan pengujian berisi **checkpoint dasar 2e001ba + 10 path API/Postman lokal**, yang kemudian committed/dipush sebagai **ad03cc5**. Database pengujian memakai **SQLite terisolasi**, bukan MySQL/MariaDB aktif. Test/helper tambahan hanya disalin ke lingkungan pengujian untuk verifikasi, **tidak ikut paket sepuluh path**; suite lengkap belum otomatis diperoleh dari clone.

| Pemeriksaan yang benar-benar dilakukan sebelumnya | Hasil dan batas |
| --- | --- |
| Instalasi PHP | Composer install berhasil dari lockfile: 118 instalasi, tanpa update dependency. |
| Database terisolasi | 11 migration asli dan seeder dua akun demo berhasil pada SQLite baru. |
| Test API terpilih | **15 tes / 148 assertions lulus**, memakai SQLite RAM; validasi, session/CSRF, role, privasi, error dan konsistensi saldo desimal nonnol. Bukan seluruh suite regresi. |
| Collection melalui Postman CLI | **13 request / 51 assertions / 0 gagal**, exit 0, melalui HTTP Laravel dan SQLite hasil migration/seeder. Saldo awal kosong tetap diuji sebagai nol. Bukan Postman GUI di laptop anggota. |
| HTTP tambahan | **20/20 lulus**: dua role, login/CSRF, Dashboard, Anggota ke Admin 403, API, validasi periode dan logout. |
| Integritas | Fingerprint delapan tabel domain identik sebelum/sesudah HTTP; tidak ada pelanggaran FK SQLite. |

Percobaan CLI pertama gagal karena direktori kerja server pengujian salah; konfigurasi proses server diperbaiki, lalu run lengkap lulus tanpa mengubah source API atau menghapus assertion. Detail dan pemisahan bukti historis integrator ada di [postman/README.md](postman/README.md).

**Belum terbukti:** fresh clone nyata di laptop anggota, migration/seeder dan perilaku MySQL/MariaDB pada laptop itu, Postman GUI laptop anggota, seluruh alur keuangan/PDF, seluruh regresi aplikasi, serta kesiapan production. SQLite tidak membuktikan ENUM/DECIMAL/locking MySQL. Pengujian portal, API dan fixture lama tidak dijumlahkan sebagai satu run penuh. Tugas pembaruan README ini **tidak menjalankan ulang** build, migration, seeder, browser, HTTP, Postman atau tes aplikasi.

<a id="uji-crud"></a>

### 11.2. Panduan manual CRUD Data Anggota — jalankan di laptop sendiri

**Prasyarat:** instalasi baru dengan database uji sendiri, misalnya kasorg_local; server/MySQL/build siap. Jangan memakai kasorg_dev, akun asli organisasi atau dua demo utama sebagai target edit/hapus. Gunakan browser biasa; untuk perbandingan akses, keluar sebelum pindah akun atau gunakan profil browser terpisah.

Data uji yang disarankan (cek dahulu agar belum dipakai): nama **Anggota Uji UTS**, NIM **24990001**, email **uts.anggota01@example.test**, status **aktif**. Buat password sendiri secara privat: **minimal 12 karakter**, huruf besar/kecil, angka dan simbol, konfirmasi sama, maksimum **72 byte**. Ini aturan akun anggota baru; dua demo tetap memiliki pengecualian minimum 8 dari Seeder. Tidak ada password contoh publik.

Tabel ini adalah **rencana penerimaan di MySQL, belum klaim semua langkah telah lulus di laptop teman**.

| Test | Langkah dan input/kondisi | Hasil yang diharapkan | Screenshot yang disarankan | Jika hasil berbeda |
| --- | --- | --- | --- | --- |
| 1. Login Admin | Buka /login; isi admin@kasorg.test dan password Admin privat lalu Masuk. | Redirect /admin/dashboard; identitas/role Admin, bukan preview. | Dashboard setelah login, tanpa form password/cookie. | Catat URL/status; periksa akun aktif, target DB dan konfigurasi demo; jangan seed/reset sebagai tebakan. |
| 2. Dashboard | Periksa empat kartu, periode, grafik/empty state dan menu. | Halaman tanpa exception; nol tetap nol pada DB baru. | Dashboard dan filter periode. | Laporkan komponen kosong/error; jangan memasukkan uang palsu agar grafik muncul. |
| 3. Daftar Anggota | Klik Data Anggota di sidebar. | /admin/anggota menampilkan data, statistik, pencarian, filter, pagination dan Tambah; demo dilindungi. | Daftar awal dengan label akun dilindungi. | Periksa revisi/route/aset; jangan membuat route dummy atau menghilangkan menu. |
| 4. Create | Klik Tambah anggota; isi data uji di atas, status aktif, password kuat dan konfirmasi sama; Simpan. | Modal tertutup setelah berhasil, notifikasi sukses, row baru tampil sesuai filter/page. | Form identitas tanpa password terlihat dan hasil sukses. | Catat error Indonesia; jika tidak tampak, hilangkan filter atau cari NIM sebelum mengulang submit. |
| 5. Persistensi | Refresh, lalu cari NIM/email yang baru dibuat. | Data tetap ada setelah request baru, bukan hanya row HTML sementara. | Hasil sesudah refresh. | Laporkan URL/filter dan pesan; jangan mengubah tabel lewat SQL untuk memaksa data ada. |
| 6. Search/filter/page | Cari nama/NIM/email satu per satu; kosongkan q; pilih status dan jumlah per halaman, klik Filter. Jika ada >5 anggota uji, coba halaman berikutnya. | Hasil sesuai query/status; pagination tidak menghilangkan filter. | Pencarian spesifik dan, jika tersedia, page 2. | Laporkan query/status/per_page dan hasil; jangan membuat banyak akun sah hanya untuk tes. |
| 7. Update | Klik ikon pensil row uji; ubah nama menjadi Anggota Uji UTS Edit dan email menjadi uts.anggota01.edit@example.test; Simpan. | Tetap pada Data Anggota, sukses, identitas baru tampil; ID/NIM/role/password tidak ikut berubah. | Modal edit dan row terbaru tanpa rahasia. | Periksa error unique; edit profil bukan reset password. |
| 8. Persistensi update | Refresh; cari nama/email baru, lalu cari nama/email lama. | Nilai baru tersimpan; nilai lama tidak lagi menjadi hasil untuk row tersebut. | Hasil pencarian email baru. | Catat nilai sebelum/sesudah; jangan hanya menyimpulkan dari tampilan sebelum refresh. |
| 9. Nonaktifkan | Pada row uji, klik aksi Nonaktifkan. | Badge menjadi Nonaktif dan filter nonaktif menemukan row; row belum diarsipkan. | Badge/status nonaktif. | Catat aksi/status/response. Jika mencoba login akun uji itu, harus ditolak; hasil login ini perlu dicatat terpisah. |
| 10. Aktifkan kembali | Klik Aktifkan pada row uji. | Badge Aktif dan row masuk filter aktif. | Badge setelah aktivasi. | Pastikan yang diklik row uji, bukan demo; laporkan jika status tidak bertahan saat refresh. |
| 11. Delete/arsip | Klik ikon tempat sampah row uji dan setujui konfirmasi. | Sukses pengarsipan; backend menjalankan soft delete, status nonaktif dan deleted_at terisi. | Pesan arsip dan daftar setelah submit; bukti kolom terbatas melalui query baca di bawah bila diperlukan. | Jangan hard delete/SQL UPDATE. Bila gagal, laporkan pesan dan ID row uji secara privat. |
| 12. Tidak tampil lagi | Refresh dan cari NIM/email row yang diarsipkan; coba semua status maupun nonaktif. | Row arsip tidak tampil pada daftar biasa, tetapi record/FK tetap ada di DB. Filter nonaktif bukan daftar arsip. | Hasil pencarian kosong; jangan tampilkan kolom password database. | Laporkan jika row hilang permanen atau tetap tampak; jangan membuat fitur restore sendiri. |
| 13. Unique | Buka Tambah; gunakan NIM yang sama dengan row uji (termasuk yang sudah diarsipkan) dan email baru, lalu ulangi dengan email lama dan NIM baru yang belum dipakai. | Ditolak dengan pesan NIM/email unik; tidak membuat duplikat. | Error per field, tanpa password. | Catat field dan apakah jumlah data berubah. Jangan memakai identitas demo sebagai satu-satunya bukti unique karena demo juga dilindungi Policy/aturan khusus. |
| 14. Validasi password | Dengan identitas baru yang valid, coba password lemah; lalu password kuat dengan konfirmasi berbeda. Coba juga nama kosong, NIM nonangka dan format email salah. | Penyimpanan ditolak, error berbahasa Indonesia; modal/form sesuai terbuka lagi dan input identitas dipertahankan. | Error validasi dengan input password tetap tersamarkan. | Catat field/pesan dan status; validasi HTML saja belum membuktikan validasi server. |
| 15. Role/403 | Keluar Admin, login anggota@kasorg.test; buka /admin/anggota dan /admin/dashboard dari address bar. | Anggota ke Dashboard Anggota; akses Admin menghasilkan HTTP 403 dengan halaman kustom, tanpa data anggota Admin. | Halaman 403 dan status Network, tanpa cookie/token. | Laporkan jika 200/data terbuka; jangan menonaktifkan middleware untuk melanjutkan demo. |
| 16. Logout | Klik Keluar; buka ulang halaman anggota/Admin terlindungi. | Kembali /login dan request baru diarahkan ke login. Reload setelah Back tidak boleh mengizinkan operasi lama. | Login setelah logout atau redirect Network. | Catat host/session; jangan menganggap cache tampilan browser sebagai bukti sesi masih dapat mengubah data. |

**Bukti soft delete, opsional dan read-only:** pada phpMyAdmin pilih **database uji sendiri**, bukan kasorg_dev, lalu jalankan hanya SELECT berikut. Jangan menampilkan kolom password/hash atau menjalankan DELETE/UPDATE manual.

~~~sql
SELECT id, name, nim, email, status_akun, deleted_at
FROM users
WHERE nim = '24990001';
~~~

Sesudah Test 11, row seharusnya masih ada dengan deleted_at terisi. Ganti NIM sesuai fixture sendiri jika sudah terpakai. Untuk membuktikan server-side validation, penguji yang memahami HTTP boleh memeriksa payload/response di Network atau mengirim request uji ber-CSRF pada lingkungan uji; jangan mengubah source, melewati otorisasi atau mematikan validasi.

**Format laporan ke kelompok:** nama penguji/tanggal, checkpoint dari git rev-parse HEAD, XAMPP/Laragon + versi PHP/server DB, nomor Test, langkah/input nonrahasia, harapan, hasil/status HTTP aktual, dan screenshot tersensor. Jangan kirim .env, password, cookie, token, hash atau bukti transfer pribadi. Bila ada error, hentikan skenario terkait dan minta review; jangan reset database.

<a id="checklist-laptop"></a>

### 11.3. Checklist instalasi dan testing laptop anggota

Semua kotak sengaja belum dicentang; hasil SQLite integrator tidak mengisi bukti MySQL milik teman.

- [ ] Clone branch jovan/integrasi-laravel dan cocokkan checkpoint.
- [ ] PHP, ekstensi, Composer, Git, Node dan npm sesuai.
- [ ] composer install dan check-platform-reqs berhasil.
- [ ] .env lokal dibuat tanpa menimpa konfigurasi existing.
- [ ] Database MySQL baru kosong dibuat.
- [ ] Koneksi mengarah ke database sendiri, bukan kasorg_dev.
- [ ] Dua password demo diatur privat dan berbeda.
- [ ] APP_KEY dibuat sekali untuk instalasi baru.
- [ ] Migration berhasil pada target baru yang diverifikasi.
- [ ] Seeder berhasil dan dua akun demo tersedia.
- [ ] npm ci berhasil dari lockfile.
- [ ] npm run build berhasil; empat entry/output ada.
- [ ] Login Admin berhasil.
- [ ] Login Anggota berhasil.
- [ ] Dashboard dan navigasi tampil tanpa error.
- [ ] CRUD Anggota tersimpan dan tetap benar setelah refresh.
- [ ] Pencarian, filter dan pagination bekerja.
- [ ] Aktif/nonaktif serta soft delete bekerja.
- [ ] Validasi NIM/email/password berjalan.
- [ ] Anggota mendapat 403 pada akses Admin.
- [ ] Logout mengakhiri sesi.
- [ ] Dua endpoint tampil pada route:list --path=api untuk revisi ad03cc5 atau pembaruan yang disepakati.
- [ ] Collection JSON dan environment contoh dari repository diimpor; nilai Admin hanya diisi privat.
- [ ] Postman Runner menjalankan 01–13 dengan redirect OFF, cookie/CSRF sesuai panduan dan 51 assertions lulus pada MySQL sendiri.
- [ ] Status negatif 401/404/422, kesesuaian saldo API/Dashboard pada periode sama, dan penolakan sesi setelah logout diverifikasi.
- [ ] Screenshot bukti disimpan tanpa rahasia.
- [ ] Hasil testing dan kendala dilaporkan ke kelompok.

### 11.4. Pemeriksaan kode dan suite otomatis — bedakan distribusinya

Setelah instalasi sendiri siap, perintah baca berikut membantu memeriksa pendaftaran route; ini bukan pengganti uji CRUD:

~~~powershell
php artisan route:list
php artisan route:list --path=admin/anggota
php artisan route:list --path=api
~~~

Pada checkpoint **ad03cc5**, filter API seharusnya menunjukkan `GET|HEAD api/kategori-transaksi` dan `GET|HEAD api/transparansi/ringkasan`. Jika tidak, periksa branch/revisi dan kelengkapan source; jangan menambahkan route dummy. `/ui-preview/*` hanya untuk APP_ENV local, bukan pengganti login operasional. Pemeriksaan route bukan bukti seluruh operasi database berhasil.

[phpunit.xml](phpunit.xml) mengatur testing, SQLite :memory:, session/cache array dan queue sync. Test konfigurasi demo yang committed dapat dipelajari melalui [DemoAccountConfigurationTest](tests/Unit/DemoAccountConfigurationTest.php); test ini memblokir koneksi DB. Suite lengkap AuthenticationTest, AnggotaCrudTest, FinanceSchema dan runner browser masih lokal, **bukan hasil clone**.

**Catatan terbukti dari kode:** [ExampleTest](tests/Feature/ExampleTest.php) yang committed masih mengharapkan GET / berstatus 200, sedangkan [route root](routes/web.php) melakukan redirect. Pada source test lokal, AuthenticationTest masih memeriksa class Tailwind Login dan teks banner lama; AnggotaCrudTest juga memiliki assertion teks yang belum mengikuti catatan layout terbaru. Ini ketidaksesuaian assertion, bukan bukti CRUD rusak. Karena itu jangan menjanjikan seluruh suite sudah selaras/lulus. Perubahan test lokal dan suite lengkap perlu review/integrasi terpisah; README tidak memperbaiki atau menjalankan tes itu.

**Keamanan runner:** beberapa test keuangan/API lokal mempunyai mode opt-in `KASORG_*_MARIADB=1` yang secara eksplisit menargetkan kasorg_dev. Runner `tests/Manual/payment-concurrency.php` juga menulis fixture ke database itu, sedangkan `tests/Manual/postman-api-smoke.php` terikat konfigurasi demo/alat lokal dan belum sesuai collection Admin terbaru. Jangan menjalankannya sebagai langkah instalasi atau pengujian terisolasi. Rollback tidak berarti tidak menyentuh database; persiapan pengujian MySQL terpisah memerlukan review sendiri.

Contoh opsional setelah review target dan persetujuan testing, bukan langkah wajib menyalakan aplikasi:

~~~powershell
if (Test-Path -LiteralPath 'bootstrap/cache/config.php') {
    throw 'Ada cache konfigurasi; tinjau bersama pengelola sebelum tes.'
}
if (Get-ChildItem Env: | Where-Object { $_.Name -match '^KASORG_.*_MARIADB$' -and $_.Value -eq '1' }) {
    throw 'Mode MariaDB aktif; hentikan. Contoh ini tidak menguji database operasional.'
}
php vendor/phpunit/phpunit/phpunit --configuration phpunit.xml --filter DemoAccountConfigurationTest
~~~

Jalankan pada terminal pengujian terpisah, bukan memakai mode KASORG_*_MARIADB=1 atau runner Manual/concurrency tanpa izin. Pemeriksaan source/lint hanya membuktikan sintaks; build hanya membuktikan bundling; fixture hanya membuktikan data/interaksi uji; SQLite tidak membuktikan ENUM/DECIMAL/locking MySQL. Uji seluruh branch terintegrasi harus dilakukan sebelum PR/main/tag.

Bukti fixture frontend terdahulu (terpisah dari paket HTTP): Data Anggota 84 geometri/30 frontend; Dashboard Admin 180/72; Dashboard Anggota 408/219; Login 612/189; Jenis Iuran 960/276; pemeriksaan umum bundle-local 177 assertions. Viewport historis 1440×900, 1366×768, 390×844. Catatan suite 232 tes/2.942 assertions dan baseline runner ada di dokumen/source lokal, **bukan hasil ulang checkpoint clone**. Jangan membuang pre-bootstrap-views.json sebagai output sementara; runner/baseline belum ikut clone dan memerlukan persiapan khusus.

<a id="belajar-uts"></a>

### Cara memahami kode untuk UTS

Belajar dengan menelusuri **satu aksi**, bukan menghafal semua baris README. Buka file lewat [peta file](#peta-file), baca method yang disebut, lalu cocokkan URL, field form dan keluarannya.

| Yang dipelajari | File inti yang dibuka berurutan | Hal yang sebaiknya bisa dijelaskan |
| --- | --- | --- |
| Login dan role | routes/web.php → Auth/LoginRequest → AuthenticatedSessionController → EnsureRole/EnsureAccountIsActive → User/Role. | Hash diperiksa provider, session diregenerasi, dua role dan alasan 403. |
| CRUD Anggota | Route admin.anggota.* → Request Anggota → UserPolicy → Admin/AnggotaController → User → Blade anggota. | Validasi sebelum simpan, role server, unique, modal gagal/sukses, soft delete dan perlindungan demo. |
| Relasi database | Migration roles/users/tagihan/pembayaran/kas → Model terkait. | FK menjaga referensi, relasi memudahkan mengambil nama; migration bukan koneksi aktif. |
| Dashboard | RingkasanKasController::dashboard → RingkasanKasService → Model kas/periode → Blade dan partial grafik. | Sumber angka dan saldo presisi; tagihan belum dibayar bukan pemasukan. |
| API JSON | routes/api.php → EnsureApiJson → Request Api → KasController → ApiResponse → collection. | Session/cookie, 200/401/403/422, field aman dan pengujian Postman. |
| Validasi | Save/StoreAnggotaRequest dan KeuanganRequest. | rules berbeda dari messages dan authorize; error HTML vs JSON. |
| Error handling | bootstrap/app.php → errors/403,404,500 → layouts/guest. | Status tidak berubah menjadi 200; error 500 tidak membuka SQL/stack trace saat debug nonaktif. |

Contoh pertanyaan dosen — jawab sambil menunjukkan source yang mendukung:

| Pertanyaan | Penjelasan berdasarkan kode KasOrg |
| --- | --- |
| Di mana MVC-nya? | User/TagihanIuran adalah Model data/relasi; AnggotaController mengatur request; admin/anggota/index.blade.php menyajikan HTML. Request, Policy dan Service memisahkan validasi, izin dan aturan bisnis. |
| Mengapa validasi tidak cukup memakai required di HTML? | Browser bisa dimodifikasi. StoreAnggotaRequest memvalidasi di server; Controller memakai safe()/data tervalidasi, dan database tetap menjaga unique/FK. |
| Apa beda middleware dan Policy? | Middleware menyaring request secara umum (sudah login/aktif/role); UserPolicy menilai tindakan terhadap objek tertentu, termasuk larangan mengubah demo utama. |
| Mengapa password tidak dibandingkan langsung? | User menyimpan hash dengan cast hashed; Auth::attempt memakai provider Laravel untuk memeriksa kecocokan, bukan menyimpan password plaintext. |
| Kenapa ada migration, seeder dan Model? | Migration membentuk tabel/constraint, Seeder memberi data awal, Model membaca/menulis melalui Eloquent. Ketiganya bukan tiga database aktif yang berbeda. |
| Mengapa Dashboard dan API saldonya sama? | Keduanya memakai RingkasanKasService, transaksi sah/rentang tanggal yang sama dan BCMath; Postman 07/08 membandingkan saldo. Periode harus sama ketika mendemokan. |
| Apakah mengunggah bukti langsung menambah uang? | Tidak. submit membuat menunggu; verify yang disetujui membuat satu ledger dan pelunasan atomik. Bukti perlu dicocokkan Admin secara manual. |
| Apakah HTTP 200 atau build sukses berarti CRUD selesai? | Tidak. Build membuktikan bundle terbentuk, 200 membuktikan respons. Penyimpanan, validasi, izin, session dan regresi perlu tes terpisah. |
| Mengapa masih ada Tailwind? | Migrasi Bootstrap bertahap untuk MVP; entry/layout legacy dipisahkan agar fitur tambahan tidak rusak. Jangan mengklaim semua halaman sudah Bootstrap. |
| Mengapa SQL Kent berbeda? | Adaptasi memisahkan standar iuran, kewajiban, pengajuan dan ledger serta memakai role berelasi. Itu pilihan integrasi yang perlu review, bukan kewajiban Laravel menghapus rancangan Kent. |

Saat presentasi, tunjukkan satu contoh data uji yang disetujui dan jelaskan jalurnya sampai keluaran. Jika belum ada bukti tes pada revisi/mesin tersebut, katakan belum diverifikasi; jangan menghafal angka tes sebagai bukti seluruh aplikasi siap.

### Alur demo inti

**Rencana demo checkpoint saat ini: Login Admin → Dashboard → CRUD Anggota → validasi salah → logout → login Anggota/403 → Postman 01–13 dengan sesi Admin tersendiri → logout.** Source API/Postman sudah ikut clone ad03cc5; demo pada laptop anggota tetap harus diuji dahulu dengan database MySQL sendiri, bukan dianggap lulus dari bukti SQLite.

1. Konfirmasi source terintegrasi, server/database/aset siap, serta akun demo privat benar.
2. Login Admin; tunjukkan Dashboard dan periode/saldo nyata, termasuk nol bila belum ada transaksi sah.
3. Gunakan anggota uji terpisah yang disetujui untuk tambah/edit/status/arsip; tunjukkan validasi NIM/email/password salah tanpa menampilkan rahasia. Jangan mengubah dua demo utama atau data sah kelompok.
4. Login Anggota pada sesi yang tepat; akses halaman Admin harus 403. Jangan memakai URL role palsu.
5. Jalankan Postman **01–13** dari [paket repository](postman/README.md) dengan sesi Admin Postman sendiri untuk menunjukkan 200/422/401/404, CSRF/logout dan kesesuaian saldo. Hasil Runner pada mesin demo harus dicatat; jangan memakai endpoint palsu atau mengklaim lulus sebelum pengujian.
6. Logout melalui POST yang benar. Jelaskan satu alur Route → Request/Policy → Controller → Model/Service → database → Blade/JSON.

Jenis Iuran dapat ditunjukkan sebagai pendukung. Pembayaran, PDF, transaksi dan transparansi tetap dipertahankan tetapi tidak harus memakan seluruh waktu demo MVP. Demo tulis hanya memakai data pengujian yang disetujui; jangan merekayasa status pembayaran melalui SQL.

Penyerahan tetap memerlukan alur inti teruji di main/tag **mvp-rc**, bukti commit/branch/PR tiap anggota, laporan UTS PDF 15–30 halaman isi dan slide maksimal 16 memakai template dosen, serta pengungkapan AI pada lampiran. PDF transaksi aplikasi bukan laporan akademik. Baca [checklist UTS](docs/KASORG_UTS_CHECKLIST.md); deadline kelas/LMS dikonfirmasi kembali ke dosen. README tidak membuat rilis atau menyatakan deliverable ini selesai.

<a id="troubleshooting"></a>

## 12. Troubleshooting

Baca error pertama, catat revisi dan langkah penyebab, lalu diagnosis pada laptop/database sendiri. **Reset database bukan solusi umum.** Jangan membagikan log mentah, password, APP_KEY, hash, cookie/token atau bukti pembayaran.

| Masalah | Penyebab umum | Diagnosis | Solusi aman |
| --- | --- | --- | --- |
| PHP tidak dikenali | PATH/terminal menunjuk instalasi salah. | where.exe php, Get-Command php; cek file PHP XAMPP/Laragon. | Gunakan executable yang benar atau PATH terminal setelah persetujuan pemilik; buka terminal baru. |
| PHP terlalu lama | CLI berbeda dari PHP panel. | php -v dan php --ini; bandingkan >=8.2/lockfile. | Pilih PHP kompatibel; jangan menurunkan Laravel atau mengabaikan platform requirements. |
| Composer install gagal | Ekstensi/versi PHP, jaringan atau izin. | Baca error pertama; composer check-platform-reqs setelah dependency tersedia. | Perbaiki kebutuhan mesin bersama pemilik, ulang install dari lock; bukan composer update/setup otomatis. |
| Ekstensi hilang / could not find driver | php.ini CLI salah; pdo_mysql/BCMath/XML/Fileinfo belum aktif. | php -m dan php --ini pada PHP yang menjalankan Artisan/Composer. | Aktifkan ekstensi pada instalasi benar, restart proses; jangan ganti perhitungan uang ke float. |
| Node/npm tidak dikenali | PATH terminal lama/Node belum tersedia. | where.exe node, where.exe npm, node -v, npm.cmd -v. | Terminal baru atau PATH proses seperti bagian aset; npm.cmd bila npm.ps1 diblokir. |
| npm ci gagal | Node tidak kompatibel, jaringan, lockfile tidak sesuai atau file terkunci. | Cocokkan Node engine, package.json/lockfile dan pesan error. | Hentikan, review source/lock bersama tim; jangan hapus lock atau menjalankan upgrade/audit fix --force. |
| Vite build / manifest hilang | Dependency/build belum selesai atau entry/output tak lengkap. | npm.cmd run build; periksa public/build/manifest.json dan empat file output. | Build setelah npm ci berhasil. Bundle tidak ikut clone; fallback CDN bukan bukti build. |
| Class not found | vendor/autoload hilang atau source tak sesuai revisi. | Cek vendor/autoload.php, composer install dan keberadaan class pada Git checkpoint. | Pulihkan instalasi dependency sesuai lock/koordinasikan file asli; jangan membuat class dummy. |
| APP_KEY belum dibuat | Konfigurasi instalasi baru belum lengkap. | Periksa keberadaan key secara privat, jangan mencetaknya. | Instalasi baru: key:generate sekali. Existing: koordinasikan konfigurasi asli, jangan rotasi key. |
| SQLSTATE connection refused | MySQL belum aktif, host/port salah atau port dipakai server lain. | Panel XAMPP/Laragon dan host/port pada .env lokal sendiri. | Jalankan server yang benar/selaraskan port; jangan menutup proses asing tanpa izin. |
| Access denied | Akun/password/izin DB tidak cocok. | Uji kredensial secara privat di pengelola DB sendiri; pastikan server tujuan benar. | Perbaiki .env lokal sesuai akun sah, bukan mengasumsikan root tanpa password. |
| Unknown database | DB_DATABASE belum dibuat/salah server. | Periksa nama database kosong di phpMyAdmin/HeidiSQL. | Buat target baru yang disetujui; jangan rename/drop database existing atau mengimpor SQL historis. |
| Migration / foreign key gagal | Skema target tidak kosong/parsial, urutan atau kemampuan server berbeda. | Catat migration pertama yang gagal, versi DB, target dan constraint; jangan lanjut seed. | STOP dan review skema/backup bersama tim. Jangan mematikan FK, reset atau mengedit migration agar terlihat lulus. |
| Seeder password kosong/sama | Dua variabel belum diisi, environment salah atau cache lama. | Tinjau .env lokal privat, config/demo.php, local/testing dan urutan RoleSeeder. | Isi dua nilai berbeda minimal 8; seed hanya pada instalasi baru yang diverifikasi. Existing/seed parsial perlu review sebelum pengulangan. |
| Seeder konflik identitas | NIM/email demo dipakai row lain atau demo telah diarsipkan/diubah. | Tinjau pesan serta identitas tanpa membuka password/hash. | Hentikan; koordinasikan, jangan overwrite/hard delete untuk memaksa seed. |
| Login gagal setelah password demo .env diganti | Seeder tidak memperbarui password akun yang sudah ada. | Konfirmasi apakah akun dibuat pada seed sebelumnya; jangan mencetak password atau hash. | Gunakan kredensial yang dipakai saat akun dibuat atau koordinasikan pemulihan yang sah. Jangan seed/reset database untuk menebak password. |
| Login gagal | Email/password salah, target DB salah atau rate limit. | Cocokkan email demo dan password privat; perhatikan pesan percobaan login. | Tunggu batas bila perlu; config password environment tidak mereset hash akun existing. |
| Akun nonaktif ditolak | Aturan status_akun aktif memang diwajibkan. | Periksa status melalui Admin yang sah pada row yang tepat. | Bukan bug untuk dilewati; jangan mengubah demo atau SQL manual demi login. |
| HTTP 403 | Role/status/Policy menolak aksi. | Periksa akun dan URL tujuan. | Anggota ke Admin memang harus 403; jangan menonaktifkan authorization. |
| HTTP 404 | Path/objek tidak ada atau penguji memakai revisi lama/tidak lengkap. | Cek URL, route:list, checkpoint ad03cc5 dan model binding. | Gunakan route/objek sah; request Postman 11 memang menguji 404. Jangan route dummy. |
| HTTP 419 CSRF | Token kedaluwarsa atau cookie berbeda host/session. | Buka ulang form; gunakan satu host/protokol, cek session. | Pertahankan CSRF/cookie. Postman 03 mengambil token login; 08 memperbarui token sesi aktif untuk logout 12. |
| HTTP 500 | Exception, konfigurasi/ekstensi atau izin runtime. | Pengelola membaca log privat, catat error pertama tanpa membagikan SQL/stack trace/rahasia. | Perbaikan harus sesuai penyebab dan persetujuan, bukan reset DB atau membuka APP_DEBUG pada demo publik. |
| RouteNotFoundException | Checkout salah atau nama route/file tidak konsisten. | git rev-parse HEAD, git branch --show-current, php artisan route:list. | Pada ad03cc5 route portal dan API sudah committed; jika hilang pastikan revisi/source utuh. Jangan menyembunyikan menu atau membuat route palsu. |
| View not found | File Blade/include tidak lengkap atau salah revisi. | Cocokkan nama view dengan resources/views dan commit. | Koordinasikan file asli; jangan placeholder. Jangan menyalin seluruh working tree privat integrator. |
| CSS berantakan / modal tidak aktif | Aset gagal, public/hot ke server lain, reset ganda atau script belum dimuat. | Network/Console browser, manifest, empat output, route Bootstrap vs legacy. | Gunakan build lokal dan satu jalur CSS/JS per halaman; jangan tambah bundle CDN di atas Vite atau langsung redesign. |
| Port 8000 terpakai | Server lain masih berjalan. | Baca pesan Artisan dan konfirmasi pemilik proses. | Pakai port kosong, misalnya php artisan serve --host=127.0.0.1 --port=8001; set APP_URL=http://127.0.0.1:8001 dan buka /login pada port itu. Jangan mematikan proses tidak dikenal. |
| Permission storage/cache | PHP tidak dapat menulis folder runtime instalasinya. | Tinjau pemilik/ACL storage dan bootstrap/cache. | Beri izin seperlunya untuk pengguna/proses sendiri, bukan Everyone full control atau chmod 777 umum. |
| PDF gagal dirender | Dependency/font/gambar/ekstensi tidak tersedia, periode invalid atau batas 2.000 transaksi. | Periksa error privat, versi Dompdf dan periode; GD disarankan untuk gambar. | Gunakan dependency terkunci/lingkup lebih sempit; jangan aktifkan remote/PHP execution Dompdf atau mengklaim berhasil tanpa file valid. |
| Grafik kosong/angka nol | Belum ada transaksi sah atau filter periode berbeda. | Periksa periode/data kas yang sah pada DB sendiri. | Nol/empty state valid; jangan mengisi mock. Tagihan dan pembayaran menunggu/ditolak bukan pemasukan. |
| API/Postman tidak jalan | Revisi lama, server/base_url salah, cookie/CSRF, akun atau parameter tidak sesuai. | Cocokkan ad03cc5, route:list --path=api, environment privat dan urutan request. | Ikuti [panduan Postman](postman/README.md); login browser tidak memberi sesi Postman. 401 guest dan 422 kasus negatif memang diharapkan; jangan reset DB/seed ulang. |

Jika harus membaca log atau konfigurasi privat, lakukan bersama pemilik mesin dan sensor bukti. README ini tidak memberi izin mengubah data sah, database integrator, atau source tanpa review.

<a id="status-github"></a>

## 13. Status GitHub dan kerja kelompok

Repository: [Jopannnnnn/KasOrg](https://github.com/Jopannnnnn/KasOrg), branch **jovan/integrasi-laravel**. Pada **9 November 2026**, HEAD lokal dan SHA branch remote diperiksa secara read-only melalui `git ls-remote`: keduanya **ad03cc52e21ee8d2a0a7fa0f70071d36024b91fc**. Tidak ada fetch, staging, commit atau push dalam pembaruan README ini. Histori menuju checkpoint tersebut memuat:

| Urutan | Commit | Isi checkpoint |
| --- | --- | --- |
| 1 | cd9c499 | Database, Model, migration, seeder/factory dan konfigurasi data awal. |
| 2 | a13c712 | Login/logout session dan middleware role. |
| 3 | c829960 | Backend Dashboard dan RingkasanKasService. |
| 4 | 671fc8d | Tampilan Dashboard Admin/Anggota dan partial. |
| 5 | 634d551 | Sebagian route portal, identitas session, layout/sidebar/topbar dan preview Dashboard. |
| 6 | 2ae4013 | Backend CRUD Anggota, validasi, Policy dan perlindungan demo. |
| 7 | 7e76a17 | Fondasi Bootstrap 5/Vite, entry legacy dan lockfile npm. |
| 8 | 9985dfd | Komponen Bootstrap dan salinan komponen legacy. |
| 9 | 7346aa8 | Login Bootstrap, CSS login dan halaman error 403/404/500. |
| 10 | 9f9d90d | Aset CSS dan partial persiapan Dashboard Bootstrap; belum aktivasi layout. |
| 11 | 3bb754b | CSS, pagination dan preview Anggota; belum aktivasi CRUD operasional. |
| 12 | e1e3ddc | Backend Jenis Iuran; belum aktivasi route/tampilan operasional. |
| 13 | ccd4f1b | Backend Tagihan/Pembayaran/Verifikasi dan disk privat bukti. |
| 14 | 2218519 | Backend Transaksi Kas Manual — DALAM PENGEMBANGAN. |
| 15 | f3aa6c4 | Backend Laporan/PDF dan dependensi Dompdf — DALAM PENGEMBANGAN. |
| 16 | **15e32bc** | Paket integrasi portal 34 path (19 A, 15 M): route/layout/navigasi, Blade operasional, Bootstrap/legacy, README dan contoh MySQL. Snapshot diuji terisolasi dengan SQLite; belum final. |
| 17 | [2e001ba](https://github.com/Jopannnnnn/KasOrg/commit/2e001ba424d9ce851c482f38f747b778b89c1e23) | Pembaruan README utama untuk instalasi dan testing KasOrg; hanya README.md. Checkpoint dokumentasi sebelumnya, bukan integrasi API. |
| 18 | [**ad03cc5**](https://github.com/Jopannnnnn/KasOrg/commit/ad03cc52e21ee8d2a0a7fa0f70071d36024b91fc) | Paket API/Postman tepat **10 path (9 A, 1 M)**: tujuh path implementasi/registrasi API dan tiga file Postman. Sudah dipush; bukti uji SQLite terisolasi, belum MySQL/GUI laptop anggota. |

**Sejak 15e32bc, tetap tersedia pada ad03cc5:** route asli portal kedua role, lima route CRUD Anggota, layout Bootstrap/legacy, sidebar/topbar, Dashboard, halaman keuangan/partial/preview, aset pendukung, backend/domain/migration/seeder, Login/error, Composer/npm lockfile dan contoh konfigurasi MySQL. Sembilan named route sidebar yang dahulu tertunda sudah terdaftar. **Sejak ad03cc5:** dua endpoint API beserta registrasi/middleware/validasi/respons JSON dan paket utama Postman juga ikut clone; informasi lama “CRUD/layout/API belum committed” tidak berlaku untuk checkpoint terbaru.

**Belum ikut checkpoint:** sebagian suite regresi/helper dan runner visual/Manual, workspace YAML Postman, pemindahan arsip SQL historis serta dokumen tahap lokal. Itu bukan syarat tambahan untuk mengimpor collection JSON. Perubahan tetap dipertahankan untuk review/integrasi terpisah, bukan diam-diam dihapus. Main belum menerima paket terbaru; branch integrasi bukan rilis production.

<a id="distribusi-checkpoint"></a>

### Isi paket integrasi yang sudah dipush

Commit [15e32bc](https://github.com/Jopannnnnn/KasOrg/commit/15e32bc76f780dbd0560f3d8c58aae029e5966e4) memuat **34 path: 19 ditambahkan dan 15 dimodifikasi**. Pengelompokan berikut menjelaskan cakupannya, bukan daftar file baru yang masih harus dibuat:

| Kelompok dalam commit | Jumlah path | Isi dan fungsi |
| --- | --- | --- |
| Route dan layout | 4 | [routes/web.php](routes/web.php) operasional, [layouts/app](resources/views/layouts/app.blade.php), serta wrapper [sidebar](resources/views/components/sidebar.blade.php) dan [topbar](resources/views/components/topbar.blade.php). Named route portal asli, session pengguna dan pemilihan Bootstrap/legacy sudah masuk. |
| Halaman utama MVP | 3 | [Dashboard Admin](resources/views/admin/dashboard.blade.php), [Dashboard Anggota](resources/views/anggota/dashboard.blade.php), dan [Data Anggota](resources/views/admin/anggota/index.blade.php). Form CRUD terhubung ke backend yang committed sebelumnya. |
| Halaman operasional keuangan | 6 | Input Iuran, Verifikasi Pembayaran, Transaksi Kas, Tagihan Anggota, Laporan dan Transparansi. Halaman tetap tersedia dengan status DALAM PENGEMBANGAN/TESTING; bukan bukti seluruh alurnya sudah lulus. |
| Jenis Iuran Bootstrap | 4 | CSS Jenis Iuran, Blade daftar/form, serta partial modal-scripts Bootstrap. |
| Komponen keuangan | 4 | Modal legacy, filter, riwayat pembayaran dan script interaksi keuangan. |
| Partial transaksi/laporan | 5 | Form transaksi, filter periode laporan, grafik laporan, alokasi pengeluaran dan tabel kas. |
| Preview lokal | 6 | Preview Iuran, Verifikasi, Transaksi, Tagihan, Laporan dan Transparansi; hanya local, bukan autentikasi/CRUD operasional. |
| Panduan instalasi | 2 | README dan [.env.example](.env.example) versi checkpoint; contoh MySQL, database baru kasorg_local dan dua variabel password demo kosong. Bukan .env aktif atau salinan data integrator. |
| **Total** | **34** | Paket sudah dipush ke branch jovan/integrasi-laravel, bukan otomatis ke main. |

Fondasi lain **sudah masuk commit sebelumnya**: backend/auth/Model/migration/seeder, aset serta komponen Bootstrap, CSS/pagination Anggota, halaman Login/error, Composer lockfile dan [package-lock.json](package-lock.json). File tersebut tidak semuanya berubah lagi dalam paket 34 path. Dengan demikian, pernyataan lama bahwa route/layout/lockfile/CRUD masih belum tersedia di GitHub **tidak berlaku pada checkpoint ini**.

### Paket API/Postman ad03cc5 — tepat 10 path

Commit [ad03cc5](https://github.com/Jopannnnnn/KasOrg/commit/ad03cc52e21ee8d2a0a7fa0f70071d36024b91fc), berjudul **feat(api): integrasikan endpoint JSON dan Postman UTS**, menambahkan **9 path (A)** dan memodifikasi **1 path (M)**. Tidak memuat test tambahan, workspace YAML, README utama atau perubahan SQL.

| Status | Path yang sudah dipush | Fungsi |
| --- | --- | --- |
| M | [bootstrap/app.php](bootstrap/app.php) | Registrasi API dan penanganan error JSON; route web tetap dipertahankan. |
| A | [routes/api.php](routes/api.php) | Dua GET dengan session, auth, akun aktif dan role Admin/Anggota. |
| A | [app/Http/Controllers/Api/KasController.php](app/Http/Controllers/Api/KasController.php) | Kategori serta ringkasan kas melalui sumber perhitungan bersama. |
| A | [app/Http/Middleware/EnsureApiJson.php](app/Http/Middleware/EnsureApiJson.php) | Format JSON dan penolakan akses yang konsisten. |
| A | [app/Http/Requests/Api/KategoriTransaksiRequest.php](app/Http/Requests/Api/KategoriTransaksiRequest.php) | Validasi filter jenis kategori. |
| A | [app/Http/Requests/Api/RingkasanRequest.php](app/Http/Requests/Api/RingkasanRequest.php) | Validasi periode ringkasan API. |
| A | [app/Http/Responses/ApiResponse.php](app/Http/Responses/ApiResponse.php) | Envelope JSON sukses/error dan Cache-Control privat. |
| A | [postman/KasOrg-UTS.postman_collection.json](postman/KasOrg-UTS.postman_collection.json) | 13 request session/CSRF, status, privasi dan konsistensi saldo. |
| A | [postman/KasOrg-Local.example.postman_environment.json](postman/KasOrg-Local.example.postman_environment.json) | Template base_url/variabel dengan kredensial dan token kosong. |
| A | [postman/README.md](postman/README.md) | Setup lokal, impor JSON, Runner dan batas bukti pengujian. |

### Distribusi checkpoint terbaru dan yang masih lokal

| Kelompok | Kondisi GitHub ad03cc5 vs working tree integrator | Dampak bagi anggota |
| --- | --- | --- |
| Web, login, dashboard dan CRUD Anggota | Source runtime sudah committed; audit dependensi tidak menemukan file P0 tertinggal. | **Layak dicoba melalui fresh clone dan pengujian MySQL**, bukan sudah dinyatakan lulus MySQL di laptop teman. Instalasi dependency, konfigurasi privat, migration/seeder database baru dan build tetap diperlukan. |
| API JSON | Ketujuh path implementasi/registrasi sudah committed pada ad03cc5. | Dua endpoint ikut clone; setelah instalasi sendiri siap, periksa route dan lakukan pengujian login/JSON. SQLite terisolasi lulus, tetapi MySQL laptop teman belum terbukti. |
| Postman utama | Collection JSON, environment contoh dan README sudah committed. | Impor langsung dari repository dan jalankan 01–13 sesuai panduan; isi kredensial privat sendiri. Tidak membutuhkan file YAML atau runner Manual. |
| Workspace YAML Postman | Masih lokal dan bukan bagian paket utama. | Tidak otomatis ikut clone dan tidak diperlukan untuk mengimpor dua JSON. Jangan menyalin workspace privat tanpa review. |
| Suite otomatis | Test konfigurasi demo/scaffold dasar sudah tracked; suite Feature lengkap, helper dan runner tambahan masih lokal. | Hasil portal 45/45 dan API 15/148 adalah bukti pengujian terpisah sebelumnya, bukan jaminan seluruh suite clone lulus. Baca [catatan assertion dan runner](#pengujian). |
| Dokumen dan arsip historis | Dokumen tahap serta pemindahan SQL masih lokal; beberapa pedoman tracked memiliki revisi yang belum dipush. | Tautan ke path tracked membuka versi checkpoint, bukan revisi lokal terbaru. Path lokal tanpa tautan bukan syarat boot dan jangan dianggap hilang karena instalasi gagal. |

README portal pada 15e32bc dan pembaruan dokumentasi pada **2e001ba** sudah tersedia di GitHub; ad03cc5 menambahkan API/Postman tanpa mengubah README utama. **Sinkronisasi README yang sedang dibaca ini tetap perubahan lokal sampai mendapat persetujuan commit/push tersendiri.** Dokumentasi ini tidak mengubah checkpoint aplikasi, menyertakan perubahan lokal lain, atau mengirim file secara otomatis.

### Git untuk anggota kelompok

- **Commit** menyimpan snapshot perubahan terpilih di repository lokal; **push** mengirim commit ke GitHub. File yang belum committed tidak otomatis ikut push.
- **main** adalah tujuan integrasi kelompok/penyerahan yang harus ditinjau dan diuji; **jovan/integrasi-laravel** menampung integrasi pengembangan yang sekarang dapat dicoba sesuai batas bukti di atas.
- Pilih branch saat clone seperti tutorial XAMPP/Laragon. Jangan mengganti judul/scope atau mengubah atribusi/identitas author untuk menambah jumlah kontribusi.
- **Pull Request (PR)** adalah permintaan review untuk menggabungkan branch. Source API sudah terintegrasi di branch ini, tetapi pengujian MySQL/Postman GUI laptop anggota, regresi lengkap dan deliverable UTS belum seluruhnya selesai. Main/tag mvp-rc memerlukan keputusan serta pengujian kelompok tersendiri.

**Mengambil update di laptop anggota, hanya setelah koordinasi:**

~~~powershell
git status --short
git branch --show-current
git rev-parse HEAD
~~~

Jika status menunjukkan perubahan lokal, **STOP**: review/simpan pekerjaan bersama tim dahulu; jangan menimpa, reset, restore, clean atau stash otomatis. Jika working tree bersih dan branch sudah jovan/integrasi-laravel, setelah izin mengambil update:

~~~powershell
git fetch origin
git log --oneline --max-count=5 origin/jovan/integrasi-laravel
git pull --ff-only origin jovan/integrasi-laravel
~~~

Fetch memperbarui informasi remote; pull --ff-only hanya maju jika histori memungkinkan. Jika ditolak/divergen, hentikan dan koordinasikan, **bukan** force push/rebase/merge otomatis. Setelah update, baca perubahan dependency/schema; Composer install/npm ci/build dilakukan sesuai kebutuhan, sedangkan migration/seeder existing tidak dijalankan sembarangan. Jangan menyalin .env orang lain.

Setiap staging, commit, push, PR/merge atau perubahan data tim tetap perlu persetujuan yang sesuai. Fokus berikutnya adalah uji instalasi MySQL, CRUD serta API/Postman GUI di mesin anggota, kemudian review suite regresi, bukan memaksakan commit kecil yang membuat snapshot tidak konsisten. Tidak ada operasi Git tulis dalam tugas pembaruan README ini.

Atribusi dan bukti kontribusi harus sesuai pekerjaan sebenarnya: Refan UI/UX, Kent rancangan awal, Jovan integrasi; kontribusi dokumentasi/QA anggota lain dicatat dari bukti yang tersedia. Jangan menyimpulkan kontribusi hanya dari author satu commit besar atau membuat co-author yang tidak sesuai.

<a id="batas"></a>

## 14. Batas fitur dan dokumentasi lanjutan

### Keterbatasan yang tidak diselesaikan oleh dokumentasi

- **F04 / pengakuan lintas periode:** IuranService::verify memeriksa periode tagihan terbuka, tetapi tanggal ledger otomatis adalah hari persetujuan. Belum sepenuhnya memeriksa periode tanggal pengakuan yang berbeda/tertutup/overlap. Ini risiko aturan bisnis nyata yang perlu keputusan tim dan tes khusus, bukan otomatis selesai karena saldo antarhalaman sama.
- Timezone app default UTC, sedangkan tanggal cetak PDF WIB; batas hari/periode perlu kesepakatan. Jangan mengedit status/tanggal langsung di SQL untuk memaksa demo.
- Penerbitan tagihan massal, alur tunai langsung oleh Admin atas nama anggota, tutup buku/carry-forward lengkap, reset password mandiri, notifikasi nyata, audit histori nilai lama–baru dan arsip PDF permanen belum tersedia sebagai fitur lengkap.
- Badge/sidebar/notifikasi tertentu dan ilustrasi login masih simulasi; status iuran Data Anggota masih Belum terhubung. Ini berbeda dari data kas/tagihan/pembayaran operasional. Preview lokal memang mock dan tidak membuktikan backend.
- Bukti transfer privat berupa gambar JPEG/PNG/WebP maksimal 5 MB; penerimaan tunai/mutasi diperiksa **manual**, tidak ada payment gateway atau API bank.
- Laporan/PDF memakai data saat request dan periode terpilih, maksimal 2.000 transaksi untuk PDF; bukan arsip beku. Perubahan transaksi antara membuka HTML dan meminta PDF bisa mengubah isinya.
- Bootstrap belum seluruh halaman; font/ikon masih eksternal. Clean clone, E2E seluruh fitur setelah Bootstrap, pengujian GUI Postman terbaru, locking MariaDB multiproses serta deliverable main/tag/PR tidak boleh dinyatakan lulus hanya dari README/fixture/build.
- Arsip SQL historis Kent yang sudah memiliki versi di GitHub memerlukan **review privasi bersama pemilik data/kelompok** karena temuan data pengguna dan hash password. Integrasi API tidak menyelesaikan risiko publikasi tersebut; jangan membagikan ulang data/hash atau mengubah histori/arsip tanpa persetujuan penanganan terpisah.

### Rujukan — bedakan yang tersedia di branch dan yang masih lokal

Tautan di bawah hanya menunjuk path yang sudah tercatat pada checkpoint. Jika file tracked juga diubah secara lokal, pembaca GitHub memperoleh versi checkpoint, bukan otomatis versi lokal terbaru. Path dokumen tanpa tautan adalah catatan lokal yang belum dipush; disebut untuk menjaga konteks, **bukan syarat instalasi dan bukan tautan yang seharusnya tersedia pada clone**.

- Persyaratan: [ketentuan UTS](docs/referensi-dosen/Ketentuan-UTS-PWL.pdf), [kontrak kuliah](docs/referensi-dosen/Modul-01-Kontrak-Kuliah.pdf), [requirements/UIUX](docs/referensi-dosen/Modul-02-Requirements-UIUX.pdf), [Requirement Kelompok 4 DRAFT](docs/referensi-dosen/Requirement-KasOrg-Kelompok4-DRAFT.pdf), [checklist](docs/KASORG_UTS_CHECKLIST.md), [roadmap](docs/KASORG_ROADMAP_16_MINGGU.md).
- Memulai konteks: [AGENTS.md](AGENTS.md), [CODEX_START_HERE](docs/CODEX_START_HERE.md). Keduanya panduan, bukan bukti fitur selesai.
- Database: `docs/SETUP_DATABASE_DEVELOPMENT.md`, `docs/DRAFT_SKEMA_DATABASE_LARAVEL.md`, `docs/REVIEW_DATABASE_BERSAMA_KENT.md`, `docs/PENYELARASAN_DATABASE_G1.md`, `docs/DATA_DEMO_UTS.md`.
- Implementasi: `docs/AUTENTIKASI_TAHAP_4A.md`, `docs/CRUD_ANGGOTA_TAHAP_5A.md`, `docs/CRUD_JENIS_IURAN_TAHAP_5D4A.md`, `docs/VERIFIKASI_JENIS_IURAN_TAGIHAN_5D4A_1.md`, `docs/PEMBAYARAN_ULANG_TAHAP_5B.md`, [scope pembayaran](docs/KASORG_SCOPE_PEMBAYARAN.md).
- Keuangan: `docs/CRUD_TRANSAKSI_TAHAP_5D3.md`, `docs/INTEGRASI_DASHBOARD_TAHAP_5D2.md`, `docs/LAPORAN_KEUANGAN_TAHAP_5D4B.md`.
- API yang sudah ikut clone: [panduan Postman](postman/README.md), [collection 13 request](postman/KasOrg-UTS.postman_collection.json), [environment contoh tanpa kredensial](postman/KasOrg-Local.example.postman_environment.json). Catatan API/frontend/audit yang masih lokal: `docs/API_TAHAP_6A.md`, `docs/MIGRASI_BOOTSTRAP_MVP.md`, `docs/AUDIT_FINAL_UTS_KASORG.md`.
- [panduan.md](docs/panduan.md) milik anggota dokumentasi masih catatan awal, bukan pengganti panduan instalasi/laporan UTS lengkap.

Dokumen tahap/checklist adalah snapshot historis. Contohnya, audit mendahului error 404/500 kustom, panduan migrasi mendahului build sukses, dan checklist/catatan API awal masih menyebut 50 assertion serta beberapa status lama. [Panduan Postman yang sudah committed](postman/README.md) mencatat **51 assertions**, memisahkan bukti historis integrator dari uji SQLite terisolasi, dan menjelaskan setup MySQL masing-masing. Gunakan source checkpoint **ad03cc5** dan bukti tahap yang sesuai; jangan menganggap checklist lama, rencana penghapusan SQL atau hasil pada working tree sebagai kondisi GitHub terbaru.

**Batas pembaruan ini:** edit hanya README.md berdasarkan source/metadata dan hasil uji yang sudah tersedia. Tidak membuka .env aktif, mengakses MariaDB, membaca ulang/mengubah SQL historis, menjalankan migration/seeder/build/test/browser/runner, membuat file Temp, atau melakukan staging/commit/push. Sebelum demo pada laptop anggota, isi checklist instalasi, CRUD dan Postman pada MySQL secara nyata. Integrasi API sudah dipush; bukti SQLite bukan pengganti pengujian MySQL/GUI, regresi penuh, PR/main/tag atau kesiapan production.
