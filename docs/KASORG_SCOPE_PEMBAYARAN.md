# Sinkronisasi Scope — Pembayaran Iuran KasOrg

**Status: USULAN fitur tambahan dari percakapan project, belum secara lengkap tercantum pada dokumen `Requirement-KasOrg-Kelompok4-DRAFT.pdf`.** Dokumen UTS melarang penggantian scope tanpa persetujuan dosen. Perlu pencatatan keputusan kelompok/dosen sebelum menjadikan fitur ini MUST resmi.

## Pernyataan kebutuhan

Ada dua metode pembayaran iuran:

- **Tunai langsung ke bendahara:** anggota mengajukan atau datang langsung; bendahara mengonfirmasi **setelah uang diterima**.
- **Transfer manual ke rekening organisasi:** anggota mengunggah screenshot bukti; bendahara mencocokkan **mutasi rekening secara manual**, lalu setujui/tolak dengan alasan.

Bukan payment gateway; bukan sinkronisasi mutasi otomatis.

## Perbedaan entitas

- `jenis_iuran`: nama, nominal standar, aturan periode.
- `tagihan_iuran`: kewajiban anggota untuk periode/jenis tertentu.
- `pembayaran_iuran`: percobaan pembayaran, metode, bukti, status, verifikator.
- `transaksi_kas`: pencatatan kas yang sah.

Audit `database/sakuorg.sql` terlebih dahulu; jangan berasumsi tabel baru telah tersedia. Revisi migration dan model perlu persetujuan karena skema SQL kelompok sebelumnya berbeda dari Laravel bawaan.

## Acceptance criteria yang dapat diuji

1. Anggota aktif hanya boleh mengajukan pembayaran untuk tagihan miliknya.
2. Transfer harus memiliki bukti gambar valid; disimpan pada storage privat.
3. `pending` / `rejected` tidak membuat transaksi pemasukan maupun menaikkan saldo.
4. Bendahara dapat melihat detail dan memverifikasi **setelah memeriksa uang/mutasi**.
5. Reject mencatat alasan; anggota dapat mengajukan ulang tanpa menggandakan tagihan.
6. Approval membuat **tepat satu** transaksi pemasukan dan menandai tagihan lunas secara atomik; klik/submit ulang tidak menyebabkan saldo ganda.
7. Anggota lain tidak bisa melihat bukti milik orang lain; akses ilegal menghasilkan 403.
8. Dashboard, daftar transaksi dan laporan menggunakan sumber data transaksi yang sama.
9. Seluruh verifikasi mempunyai jejak audit waktu dan petugas.

## Catatan untuk Codex

Saat diminta implementasi, buat desain migration non-destruktif dahulu dan jelaskan dampaknya. Sediakan feature test approval ganda, nominal salah, file invalid, akses role, pengajuan ulang, dan saldo. Jangan membuat integrasi bank otomatis.
