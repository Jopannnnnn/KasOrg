-- =====================================================================
-- SakuOrg - Sistem Kas Organisasi
-- Database  : MySQL 8.0.16+ (CHECK constraint & window function)
-- Charset   : utf8mb4
-- Catatan   : Semua nominal memakai DECIMAL(15,2) (jangan FLOAT).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS sakuorg
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE sakuorg;

SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW  IF EXISTS v_perlu_perhatian, v_alokasi_pengeluaran, v_ringkasan_periode, v_status_iuran, v_riwayat_kas;
DROP TABLE IF EXISTS laporan_pdf, notifikasi, audit_log, password_reset_tokens,
                     iuran, transaksi_kas, kategori_transaksi, periode, users;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- 1. USERS  (halaman: Login, Daftar, Verifikasi Akun, Data Anggota)
--    "Admin" pada mode preview = role bendahara.
--    Akun baru berstatus 'menunggu' sampai disetujui bendahara.
-- =====================================================================
CREATE TABLE users (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nim                 VARCHAR(20)  NOT NULL,                      -- NIM / ID
  nama                VARCHAR(100) NOT NULL,
  email               VARCHAR(150) NOT NULL,
  password_hash       VARCHAR(255) NOT NULL,                      -- bcrypt/argon2, BUKAN plaintext
  role                ENUM('bendahara','anggota') NOT NULL DEFAULT 'anggota',
  status_akun         ENUM('menunggu','aktif','ditolak','nonaktif') NOT NULL DEFAULT 'menunggu',
  alasan_penolakan    VARCHAR(255) NULL,
  diverifikasi_oleh   BIGINT UNSIGNED NULL,
  diverifikasi_at     DATETIME NULL,
  remember_token      VARCHAR(100) NULL,                          -- "Ingat saya"
  last_login_at       DATETIME NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- = Tanggal daftar
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at          DATETIME NULL,                              -- soft delete
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_nim   (nim),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_status (status_akun, role),
  CONSTRAINT fk_users_verifikator FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_users_email CHECK (email LIKE '%_@_%._%')
) ENGINE=InnoDB;

-- Lupa kata sandi
CREATE TABLE password_reset_tokens (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email       VARCHAR(150) NOT NULL,
  token_hash  VARCHAR(255) NOT NULL,
  expires_at  DATETIME NOT NULL,
  used_at     DATETIME NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_prt_email (email),
  CONSTRAINT fk_prt_email FOREIGN KEY (email) REFERENCES users(email)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 2. PERIODE  (pemilih "Maret 2026", saldo awal, tutup buku)
--    Satu baris per bulan. Saldo awal bulan berikutnya = saldo akhir
--    bulan sebelumnya (dibuat otomatis oleh sp_tutup_periode).
-- =====================================================================
CREATE TABLE periode (
  id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tahun                 SMALLINT UNSIGNED NOT NULL,
  bulan                 TINYINT UNSIGNED NOT NULL,
  tanggal_mulai         DATE GENERATED ALWAYS AS (DATE_ADD(MAKEDATE(tahun,1), INTERVAL (bulan-1) MONTH)) STORED,
  tanggal_selesai       DATE GENERATED ALWAYS AS (LAST_DAY(tanggal_mulai)) STORED,
  nominal_iuran         DECIMAL(15,2) NOT NULL DEFAULT 75000.00,  -- Rp 75.000 / anggota
  jatuh_tempo           DATE NULL,
  saldo_awal            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  saldo_akhir_snapshot  DECIMAL(15,2) NULL,                       -- terisi saat ditutup
  status                ENUM('terbuka','ditutup') NOT NULL DEFAULT 'terbuka',
  ditutup_oleh          BIGINT UNSIGNED NULL,
  ditutup_at            DATETIME NULL,
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_periode (tahun, bulan),
  KEY idx_periode_tanggal (tanggal_mulai, tanggal_selesai),
  CONSTRAINT fk_periode_penutup FOREIGN KEY (ditutup_oleh) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_periode_bulan   CHECK (bulan BETWEEN 1 AND 12),
  CONSTRAINT chk_periode_iuran   CHECK (nominal_iuran >= 0)
) ENGINE=InnoDB;

-- =====================================================================
-- 3. KATEGORI TRANSAKSI  (Iuran anggota, Donasi, Konsumsi, dst.)
--    UNIQUE (id, jenis) dipakai untuk composite FK agar kategori
--    pemasukan tidak bisa dipakai di transaksi pengeluaran.
-- =====================================================================
CREATE TABLE kategori_transaksi (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama        VARCHAR(60) NOT NULL,
  jenis       ENUM('pemasukan','pengeluaran') NOT NULL,
  is_sistem   TINYINT(1) NOT NULL DEFAULT 0,       -- 1 = tidak boleh dihapus
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategori_nama_jenis (nama, jenis),
  UNIQUE KEY uq_kategori_id_jenis   (id, jenis)
) ENGINE=InnoDB;

-- =====================================================================
-- 4. TRANSAKSI KAS  (halaman: Transaksi Kas, Transparansi Kas)
-- =====================================================================
CREATE TABLE transaksi_kas (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tanggal          DATE NOT NULL,
  jenis            ENUM('pemasukan','pengeluaran') NOT NULL,
  kategori_id      SMALLINT UNSIGNED NOT NULL,
  keterangan       VARCHAR(255) NOT NULL,
  nominal          DECIMAL(15,2) NOT NULL,
  sumber           ENUM('manual','iuran') NOT NULL DEFAULT 'manual',
  bukti_path       VARCHAR(255) NULL,               -- foto nota / bukti transfer
  perlu_ditinjau   TINYINT(1) NOT NULL DEFAULT 0,   -- "Transaksi perlu ditinjau"
  catatan_tinjauan VARCHAR(255) NULL,
  dibuat_oleh      BIGINT UNSIGNED NOT NULL,
  diubah_oleh      BIGINT UNSIGNED NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at       DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_trx_tanggal  (tanggal, deleted_at),
  KEY idx_trx_jenis    (jenis, tanggal),
  KEY idx_trx_kategori (kategori_id),
  KEY idx_trx_tinjau   (perlu_ditinjau, deleted_at),
  FULLTEXT KEY ft_trx_keterangan (keterangan),      -- kolom "Cari transaksi atau keterangan"
  CONSTRAINT fk_trx_kategori FOREIGN KEY (kategori_id, jenis)
    REFERENCES kategori_transaksi (id, jenis) ON UPDATE CASCADE,
  CONSTRAINT fk_trx_pembuat FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON UPDATE CASCADE,
  CONSTRAINT fk_trx_pengubah FOREIGN KEY (diubah_oleh) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_trx_nominal CHECK (nominal > 0)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. IURAN  (halaman: Input Iuran, status iuran anggota, dashboard anggota)
--    Satu baris = tagihan 1 anggota untuk 1 periode.
--    Saat dicatat lunas -> otomatis membuat transaksi pemasukan
--    (lihat sp_catat_iuran).
-- =====================================================================
CREATE TABLE iuran (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       BIGINT UNSIGNED NOT NULL,
  periode_id    BIGINT UNSIGNED NOT NULL,
  nominal       DECIMAL(15,2) NOT NULL,
  status        ENUM('belum_lunas','lunas') NOT NULL DEFAULT 'belum_lunas',
  metode        ENUM('tunai','transfer') NULL,
  tanggal_bayar DATE NULL,
  transaksi_id  BIGINT UNSIGNED NULL,
  dicatat_oleh  BIGINT UNSIGNED NULL,
  catatan       VARCHAR(255) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_iuran_user_periode (user_id, periode_id),   -- tidak boleh dobel
  UNIQUE KEY uq_iuran_transaksi    (transaksi_id),
  KEY idx_iuran_periode_status (periode_id, status),
  CONSTRAINT fk_iuran_user    FOREIGN KEY (user_id)      REFERENCES users(id)         ON UPDATE CASCADE,
  CONSTRAINT fk_iuran_periode FOREIGN KEY (periode_id)   REFERENCES periode(id)       ON UPDATE CASCADE,
  CONSTRAINT fk_iuran_trx     FOREIGN KEY (transaksi_id) REFERENCES transaksi_kas(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_iuran_pencatat FOREIGN KEY (dicatat_oleh) REFERENCES users(id)        ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_iuran_nominal CHECK (nominal > 0),
  CONSTRAINT chk_iuran_lunas CHECK (
    status = 'belum_lunas'
    OR (tanggal_bayar IS NOT NULL AND metode IS NOT NULL AND transaksi_id IS NOT NULL)
  )
) ENGINE=InnoDB;

-- =====================================================================
-- 6. NOTIFIKASI (ikon lonceng) & LAPORAN PDF (tombol Unduh PDF) & AUDIT
-- =====================================================================
CREATE TABLE notifikasi (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     BIGINT UNSIGNED NOT NULL,
  tipe        ENUM('verifikasi','iuran','transaksi','sistem') NOT NULL DEFAULT 'sistem',
  judul       VARCHAR(120) NOT NULL,
  pesan       VARCHAR(255) NOT NULL,
  tautan      VARCHAR(255) NULL,
  dibaca_at   DATETIME NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notif_user (user_id, dibaca_at, created_at),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE laporan_pdf (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  periode_id  BIGINT UNSIGNED NOT NULL,
  nama_file   VARCHAR(255) NOT NULL,
  dibuat_oleh BIGINT UNSIGNED NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_laporan_periode (periode_id),
  CONSTRAINT fk_laporan_periode FOREIGN KEY (periode_id)  REFERENCES periode(id) ON UPDATE CASCADE,
  CONSTRAINT fk_laporan_user    FOREIGN KEY (dibuat_oleh) REFERENCES users(id)   ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Jejak perubahan data keuangan (transparansi & akuntabilitas bendahara)
CREATE TABLE audit_log (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     BIGINT UNSIGNED NULL,
  aksi        VARCHAR(40) NOT NULL,               -- tambah / ubah / hapus / catat_iuran / tutup_periode
  tabel       VARCHAR(40) NOT NULL,
  record_id   BIGINT UNSIGNED NOT NULL,
  data_lama   JSON NULL,
  data_baru   JSON NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_tabel (tabel, record_id),
  KEY idx_audit_user  (user_id, created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- 7. TRIGGER
-- =====================================================================
DELIMITER $$

-- Transaksi pada periode yang sudah ditutup tidak boleh ditambah
CREATE TRIGGER trg_trx_bi BEFORE INSERT ON transaksi_kas FOR EACH ROW
BEGIN
  IF EXISTS (SELECT 1 FROM periode
             WHERE status = 'ditutup' AND NEW.tanggal BETWEEN tanggal_mulai AND tanggal_selesai) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Periode kas sudah ditutup, transaksi tidak dapat ditambah.';
  END IF;
END$$

-- ...maupun diubah / dihapus (soft delete juga = UPDATE)
CREATE TRIGGER trg_trx_bu BEFORE UPDATE ON transaksi_kas FOR EACH ROW
BEGIN
  IF EXISTS (SELECT 1 FROM periode
             WHERE status = 'ditutup'
               AND (OLD.tanggal BETWEEN tanggal_mulai AND tanggal_selesai
                 OR NEW.tanggal BETWEEN tanggal_mulai AND tanggal_selesai)) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Periode kas sudah ditutup, transaksi tidak dapat diubah.';
  END IF;
END$$

-- Audit otomatis ketika transaksi diubah / dihapus
CREATE TRIGGER trg_trx_au AFTER UPDATE ON transaksi_kas FOR EACH ROW
BEGIN
  INSERT INTO audit_log (user_id, aksi, tabel, record_id, data_lama, data_baru)
  VALUES (
    NEW.diubah_oleh,
    IF(NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL, 'hapus', 'ubah'),
    'transaksi_kas', NEW.id,
    JSON_OBJECT('tanggal', OLD.tanggal, 'kategori_id', OLD.kategori_id, 'keterangan', OLD.keterangan, 'nominal', OLD.nominal),
    JSON_OBJECT('tanggal', NEW.tanggal, 'kategori_id', NEW.kategori_id, 'keterangan', NEW.keterangan, 'nominal', NEW.nominal)
  );
END$$

-- =====================================================================
-- 8. STORED PROCEDURE
-- =====================================================================

-- Buat tagihan iuran untuk semua akun aktif pada satu periode
CREATE PROCEDURE sp_generate_iuran(IN p_periode_id BIGINT UNSIGNED)
BEGIN
  INSERT IGNORE INTO iuran (user_id, periode_id, nominal)
  SELECT u.id, p.id, p.nominal_iuran
  FROM users u
  JOIN periode p ON p.id = p_periode_id
  WHERE u.status_akun = 'aktif' AND u.deleted_at IS NULL;
END$$

-- Tombol "Catat iuran": tandai lunas + buat transaksi pemasukan otomatis (atomik)
CREATE PROCEDURE sp_catat_iuran(
  IN p_iuran_id BIGINT UNSIGNED,
  IN p_metode   ENUM('tunai','transfer'),
  IN p_tanggal  DATE,
  IN p_oleh     BIGINT UNSIGNED
)
BEGIN
  DECLARE v_status  VARCHAR(20);
  DECLARE v_nominal DECIMAL(15,2);
  DECLARE v_nama    VARCHAR(100);
  DECLARE v_bulan   TINYINT;
  DECLARE v_kat     SMALLINT UNSIGNED;
  DECLARE v_trx     BIGINT UNSIGNED;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  IF NOT EXISTS (SELECT 1 FROM users WHERE id = p_oleh AND role = 'bendahara' AND status_akun = 'aktif') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hanya bendahara yang dapat mencatat iuran.';
  END IF;

  SELECT i.status, i.nominal, u.nama, p.bulan
    INTO v_status, v_nominal, v_nama, v_bulan
  FROM iuran i
  JOIN users u   ON u.id = i.user_id
  JOIN periode p ON p.id = i.periode_id
  WHERE i.id = p_iuran_id
  FOR UPDATE;

  IF v_status IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Data iuran tidak ditemukan.';
  ELSEIF v_status = 'lunas' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Iuran ini sudah lunas.';
  END IF;

  SELECT id INTO v_kat FROM kategori_transaksi
   WHERE nama = 'Iuran anggota' AND jenis = 'pemasukan';

  INSERT INTO transaksi_kas (tanggal, jenis, kategori_id, keterangan, nominal, sumber, dibuat_oleh)
  VALUES (
    p_tanggal, 'pemasukan', v_kat,
    CONCAT('Iuran bulan ',
      ELT(v_bulan,'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus',
                  'September','Oktober','November','Desember'),
      ' — ', v_nama),
    v_nominal, 'iuran', p_oleh
  );
  SET v_trx = LAST_INSERT_ID();

  UPDATE iuran
     SET status = 'lunas', metode = p_metode, tanggal_bayar = p_tanggal,
         transaksi_id = v_trx, dicatat_oleh = p_oleh
   WHERE id = p_iuran_id;

  INSERT INTO audit_log (user_id, aksi, tabel, record_id, data_baru)
  VALUES (p_oleh, 'catat_iuran', 'iuran', p_iuran_id,
          JSON_OBJECT('transaksi_id', v_trx, 'metode', p_metode, 'nominal', v_nominal));

  COMMIT;
END$$

-- Tutup buku bulanan + buka periode berikutnya dengan saldo awal otomatis
CREATE PROCEDURE sp_tutup_periode(IN p_periode_id BIGINT UNSIGNED, IN p_oleh BIGINT UNSIGNED)
BEGIN
  DECLARE v_status VARCHAR(10);
  DECLARE v_awal, v_in, v_out, v_akhir DECIMAL(15,2);
  DECLARE v_mulai, v_selesai, v_next DATE;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SELECT status, saldo_awal, tanggal_mulai, tanggal_selesai
    INTO v_status, v_awal, v_mulai, v_selesai
  FROM periode WHERE id = p_periode_id FOR UPDATE;

  IF v_status IS NULL OR v_status <> 'terbuka' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Periode tidak ditemukan atau sudah ditutup.';
  END IF;

  SELECT COALESCE(SUM(CASE WHEN jenis = 'pemasukan'   THEN nominal END), 0),
         COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal END), 0)
    INTO v_in, v_out
  FROM transaksi_kas
  WHERE deleted_at IS NULL AND tanggal BETWEEN v_mulai AND v_selesai;

  SET v_akhir = v_awal + v_in - v_out;

  UPDATE periode
     SET status = 'ditutup', saldo_akhir_snapshot = v_akhir,
         ditutup_oleh = p_oleh, ditutup_at = NOW()
   WHERE id = p_periode_id;

  SET v_next = DATE_ADD(v_mulai, INTERVAL 1 MONTH);
  INSERT IGNORE INTO periode (tahun, bulan, nominal_iuran, jatuh_tempo, saldo_awal)
  SELECT YEAR(v_next), MONTH(v_next), nominal_iuran, DATE_ADD(jatuh_tempo, INTERVAL 1 MONTH), v_akhir
  FROM periode WHERE id = p_periode_id;

  INSERT INTO audit_log (user_id, aksi, tabel, record_id, data_baru)
  VALUES (p_oleh, 'tutup_periode', 'periode', p_periode_id,
          JSON_OBJECT('pemasukan', v_in, 'pengeluaran', v_out, 'saldo_akhir', v_akhir));

  COMMIT;
END$$

DELIMITER ;

-- =====================================================================
-- 9. VIEW (siap dipakai query dashboard / laporan / transparansi)
-- =====================================================================

-- Riwayat kas (tabel Transaksi Kas & Transparansi Kas)
CREATE VIEW v_riwayat_kas AS
SELECT t.id, t.tanggal, t.jenis, k.nama AS kategori, t.keterangan, t.nominal,
       t.sumber, t.perlu_ditinjau, t.created_at, t.updated_at
FROM transaksi_kas t
JOIN kategori_transaksi k ON k.id = t.kategori_id
WHERE t.deleted_at IS NULL;

-- Ringkasan per periode: saldo awal, pemasukan, pengeluaran, saldo akhir
-- (dipakai kartu dashboard, Laporan, Transparansi, grafik Arus Kas & Tren Saldo)
CREATE VIEW v_ringkasan_periode AS
SELECT p.id AS periode_id, p.tahun, p.bulan, p.status,
       p.saldo_awal,
       COALESCE(SUM(CASE WHEN t.jenis = 'pemasukan'   THEN t.nominal END), 0) AS total_pemasukan,
       COALESCE(SUM(CASE WHEN t.jenis = 'pengeluaran' THEN t.nominal END), 0) AS total_pengeluaran,
       p.saldo_awal
         + COALESCE(SUM(CASE WHEN t.jenis = 'pemasukan'   THEN t.nominal END), 0)
         - COALESCE(SUM(CASE WHEN t.jenis = 'pengeluaran' THEN t.nominal END), 0) AS saldo_akhir
FROM periode p
LEFT JOIN transaksi_kas t
       ON t.deleted_at IS NULL
      AND t.tanggal BETWEEN p.tanggal_mulai AND p.tanggal_selesai
GROUP BY p.id, p.tahun, p.bulan, p.status, p.saldo_awal;

-- Donut "Alokasi pengeluaran" / "Penggunaan dana"
CREATE VIEW v_alokasi_pengeluaran AS
SELECT p.id AS periode_id, k.nama AS kategori,
       SUM(t.nominal) AS total,
       ROUND(100 * SUM(t.nominal) / SUM(SUM(t.nominal)) OVER (PARTITION BY p.id), 1) AS persen
FROM transaksi_kas t
JOIN kategori_transaksi k ON k.id = t.kategori_id
JOIN periode p ON t.tanggal BETWEEN p.tanggal_mulai AND p.tanggal_selesai
WHERE t.deleted_at IS NULL AND t.jenis = 'pengeluaran'
GROUP BY p.id, k.id, k.nama;

-- Status iuran per anggota per periode (Data Anggota, Input Iuran, Dashboard Anggota)
CREATE VIEW v_status_iuran AS
SELECT i.id AS iuran_id, i.periode_id, p.tahun, p.bulan,
       u.id AS user_id, u.nim, u.nama, u.email,
       i.nominal, i.status, i.metode, i.tanggal_bayar
FROM iuran i
JOIN users u   ON u.id = i.user_id
JOIN periode p ON p.id = i.periode_id
WHERE u.deleted_at IS NULL;

-- Kartu "Perlu perhatian" di dashboard bendahara
CREATE VIEW v_perlu_perhatian AS
SELECT
  (SELECT COUNT(*) FROM users
    WHERE status_akun = 'menunggu' AND deleted_at IS NULL)                  AS akun_menunggu_verifikasi,
  (SELECT COUNT(*) FROM iuran i JOIN periode p ON p.id = i.periode_id
    WHERE i.status = 'belum_lunas' AND p.status = 'terbuka')                AS anggota_belum_lunas,
  (SELECT COUNT(*) FROM transaksi_kas
    WHERE perlu_ditinjau = 1 AND deleted_at IS NULL)                        AS transaksi_perlu_ditinjau;

-- =====================================================================
-- 10. SEED DATA (disesuaikan dengan tampilan: saldo Rp 18.750.000,
--     pemasukan Rp 4.275.000, pengeluaran Rp 2.180.000)
--     Hash di bawah = bcrypt untuk kata sandi "password" (HANYA DEMO).
-- =====================================================================
INSERT INTO kategori_transaksi (id, nama, jenis, is_sistem) VALUES
 (1, 'Iuran anggota',     'pemasukan',   1),
 (2, 'Donasi',            'pemasukan',   0),
 (3, 'Pendapatan lainnya','pemasukan',   0),
 (4, 'Kegiatan',          'pengeluaran', 0),
 (5, 'Konsumsi',          'pengeluaran', 0),
 (6, 'Perlengkapan',      'pengeluaran', 0),
 (7, 'Lainnya',           'pengeluaran', 0);

SET @pw = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO users (id, nim, nama, email, password_hash, role, status_akun, diverifikasi_at, created_at) VALUES
 (1, '25170004', 'Jovan Putra Wijaya',    'jovan@kampus.ac.id',      @pw, 'bendahara', 'aktif',    NOW(), '2026-01-05 09:00:00'),
 (2, '25170001', 'Juniarti Siadari',      'juniarti@kampus.ac.id',   @pw, 'anggota',   'aktif',    NOW(), '2026-01-05 09:10:00'),
 (3, '25170027', 'Kent Muhammad Rafli',   'kent@kampus.ac.id',       @pw, 'anggota',   'aktif',    NOW(), '2026-01-05 09:20:00'),
 (4, '25170025', 'Refan Rhizqi Wintoro',  'refan@kampus.ac.id',      @pw, 'anggota',   'aktif',    NOW(), '2026-01-05 09:30:00'),
 (5, '25170010', 'Raka Putra',            'raka@kampus.ac.id',       @pw, 'anggota',   'aktif',    NOW(), '2026-01-06 10:00:00'),
 (6, '25170042', 'Alya Nuraini',          'alya@kampus.ac.id',       @pw, 'anggota',   'menunggu', NULL,  '2026-03-18 08:00:00'),
 (7, '25170045', 'Bima Pratama',          'bima@kampus.ac.id',       @pw, 'anggota',   'menunggu', NULL,  '2026-03-17 08:00:00'),
 (8, '25170051', 'Salsa Amanda',          'salsa@kampus.ac.id',      @pw, 'anggota',   'menunggu', NULL,  '2026-03-16 08:00:00');

UPDATE users SET diverifikasi_oleh = 1 WHERE status_akun = 'aktif' AND id <> 1;

-- Periode: Februari ditutup, Maret terbuka (saldo awal Maret Rp 16.655.000)
INSERT INTO periode (id, tahun, bulan, nominal_iuran, jatuh_tempo, saldo_awal, saldo_akhir_snapshot, status, ditutup_oleh, ditutup_at) VALUES
 (1, 2026, 2, 75000, '2026-02-10', 16655000, 16655000, 'ditutup', 1, '2026-03-01 00:05:00');
INSERT INTO periode (id, tahun, bulan, nominal_iuran, jatuh_tempo, saldo_awal) VALUES
 (2, 2026, 3, 75000, '2026-03-10', 16655000);

-- Tagihan iuran Maret untuk semua akun aktif, lalu catat 3 yang sudah bayar
CALL sp_generate_iuran(2);

SET @iu_raka     = (SELECT id FROM iuran WHERE user_id = 5 AND periode_id = 2);
SET @iu_jovan    = (SELECT id FROM iuran WHERE user_id = 1 AND periode_id = 2);
SET @iu_juniarti = (SELECT id FROM iuran WHERE user_id = 2 AND periode_id = 2);

CALL sp_catat_iuran(@iu_jovan,    'tunai',    '2026-03-05', 1);
CALL sp_catat_iuran(@iu_juniarti, 'transfer', '2026-03-06', 1);
CALL sp_catat_iuran(@iu_raka,     'transfer', '2026-03-18', 1);

-- Transaksi manual lain (total Maret: pemasukan 4.275.000 | pengeluaran 2.180.000)
INSERT INTO transaksi_kas (tanggal, jenis, kategori_id, keterangan, nominal, perlu_ditinjau, dibuat_oleh) VALUES
 ('2026-03-10', 'pemasukan',   1, 'Rekap iuran anggota lainnya (data awal)', 2550000, 1, 1),
 ('2026-03-15', 'pemasukan',   2, 'Donasi alumni organisasi',                1500000, 0, 1),
 ('2026-03-17', 'pengeluaran', 5, 'Konsumsi rapat pengurus',                  425000, 0, 1),
 ('2026-03-12', 'pengeluaran', 6, 'Cetak banner kegiatan',                    680000, 0, 1),
 ('2026-03-14', 'pengeluaran', 4, 'Dana kegiatan organisasi',                 700000, 0, 1),
 ('2026-03-11', 'pengeluaran', 7, 'Pengeluaran operasional lainnya',          375000, 1, 1);

INSERT INTO notifikasi (user_id, tipe, judul, pesan, tautan) VALUES
 (1, 'verifikasi', 'Akun menunggu verifikasi', '3 pendaftar baru menunggu persetujuan Anda.', '/verifikasi-akun'),
 (3, 'iuran',      'Iuran belum tercatat',     'Iuran Maret 2026 sebesar Rp 75.000 belum tercatat.', '/dashboard');

-- =====================================================================
-- CONTOH QUERY
-- =====================================================================
-- Saldo kas terkini (kartu biru Transparansi Kas):
--   SELECT saldo_akhir FROM v_ringkasan_periode WHERE tahun = 2026 AND bulan = 3;
-- Tren saldo 6 bulan terakhir:
--   SELECT tahun, bulan, saldo_akhir FROM v_ringkasan_periode ORDER BY tahun DESC, bulan DESC LIMIT 6;
-- Anggota belum lunas bulan ini:
--   SELECT nim, nama FROM v_status_iuran WHERE periode_id = 2 AND status = 'belum_lunas';
-- Cari transaksi:
--   SELECT * FROM v_riwayat_kas WHERE keterangan LIKE '%rapat%' ORDER BY tanggal DESC;
-- Login (verifikasi password dilakukan di aplikasi, bukan di SQL):
--   SELECT id, password_hash, role FROM users
--    WHERE email = ? AND status_akun = 'aktif' AND deleted_at IS NULL;
