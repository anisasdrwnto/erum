-- ============================================================
-- ERUM System - TiDB Compatible Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS erum_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE erum_db;

-- ============================================================
-- TABEL USERS (Login & Register)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          BIGINT       NOT NULL AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('superadmin','user') NOT NULL DEFAULT 'user',
    status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    foto        VARCHAR(255) DEFAULT NULL,
    telepon     VARCHAR(20)  DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_email (email),
    KEY idx_role  (role),
    KEY idx_status (status)
);

-- ============================================================
-- TABEL PERUSAHAAN / COMPANIES
-- ============================================================
CREATE TABLE IF NOT EXISTS perusahaan (
    id          BIGINT       NOT NULL AUTO_INCREMENT,
    nama        VARCHAR(150) NOT NULL,
    alamat      TEXT,
    telepon     VARCHAR(20),
    email       VARCHAR(150),
    website     VARCHAR(200),
    logo        VARCHAR(255),
    status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- ============================================================
-- TABEL KARYAWAN (SDM)
-- ============================================================
CREATE TABLE IF NOT EXISTS karyawan (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    user_id         BIGINT       DEFAULT NULL,
    perusahaan_id   BIGINT       DEFAULT NULL,
    nik             VARCHAR(20)  NOT NULL UNIQUE,
    nama            VARCHAR(100) NOT NULL,
    jabatan         VARCHAR(100),
    departemen      VARCHAR(100),
    tanggal_masuk   DATE,
    gaji_pokok      DECIMAL(15,2) DEFAULT 0,
    status          ENUM('aktif','nonaktif','cuti') NOT NULL DEFAULT 'aktif',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_perusahaan (perusahaan_id)
);

-- ============================================================
-- TABEL PRESENSI KARYAWAN
-- ============================================================
CREATE TABLE IF NOT EXISTS presensi (
    id              BIGINT    NOT NULL AUTO_INCREMENT,
    karyawan_id     BIGINT    NOT NULL,
    tanggal         DATE      NOT NULL,
    jam_masuk       TIME,
    jam_keluar      TIME,
    status          ENUM('hadir','izin','sakit','alpa') NOT NULL DEFAULT 'hadir',
    keterangan      TEXT,
    created_at      DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_karyawan_id (karyawan_id),
    KEY idx_tanggal (tanggal)
);

-- ============================================================
-- TABEL LOGBOOK PEKERJAAN
-- ============================================================
CREATE TABLE IF NOT EXISTS logbook (
    id              BIGINT    NOT NULL AUTO_INCREMENT,
    karyawan_id     BIGINT    NOT NULL,
    tanggal         DATE      NOT NULL,
    kegiatan        TEXT      NOT NULL,
    hasil           TEXT,
    durasi_jam      DECIMAL(5,2),
    status          ENUM('draft','submit','validasi','ditolak') NOT NULL DEFAULT 'draft',
    validator_id    BIGINT    DEFAULT NULL,
    validated_at    DATETIME  DEFAULT NULL,
    created_at      DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_karyawan_logbook (karyawan_id)
);

-- ============================================================
-- TABEL MASTER GAJI
-- ============================================================
CREATE TABLE IF NOT EXISTS master_gaji (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    karyawan_id     BIGINT       NOT NULL,
    bulan           TINYINT      NOT NULL,
    tahun           SMALLINT     NOT NULL,
    gaji_pokok      DECIMAL(15,2) DEFAULT 0,
    tunjangan       DECIMAL(15,2) DEFAULT 0,
    potongan        DECIMAL(15,2) DEFAULT 0,
    gaji_bersih     DECIMAL(15,2) DEFAULT 0,
    status          ENUM('draft','proses','selesai') NOT NULL DEFAULT 'draft',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_karyawan_gaji (karyawan_id),
    KEY idx_periode (bulan, tahun)
);

-- ============================================================
-- TABEL INVENTORI / MASTER BARANG
-- ============================================================
CREATE TABLE IF NOT EXISTS inventori (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    kode_barang     VARCHAR(50)  NOT NULL UNIQUE,
    nama_barang     VARCHAR(150) NOT NULL,
    kategori        VARCHAR(100),
    satuan          VARCHAR(30),
    stok            DECIMAL(15,3) DEFAULT 0,
    stok_minimum    DECIMAL(15,3) DEFAULT 0,
    harga_beli      DECIMAL(15,2) DEFAULT 0,
    harga_jual      DECIMAL(15,2) DEFAULT 0,
    lokasi_id       BIGINT       DEFAULT NULL,
    status          ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_kode (kode_barang)
);

-- ============================================================
-- TABEL LOKASI & PENYIMPANAN (GUDANG)
-- ============================================================
CREATE TABLE IF NOT EXISTS lokasi_gudang (
    id          BIGINT      NOT NULL AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    kode        VARCHAR(30)  NOT NULL UNIQUE,
    kapasitas   DECIMAL(15,3),
    alamat      TEXT,
    status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- ============================================================
-- TABEL PERGERAKAN STOK
-- ============================================================
CREATE TABLE IF NOT EXISTS pergerakan_stok (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    inventori_id    BIGINT       NOT NULL,
    tipe            ENUM('masuk','keluar','transfer','penyesuaian') NOT NULL,
    jumlah          DECIMAL(15,3) NOT NULL,
    stok_sebelum    DECIMAL(15,3),
    stok_sesudah    DECIMAL(15,3),
    referensi       VARCHAR(100),
    keterangan      TEXT,
    user_id         BIGINT       DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inventori (inventori_id),
    KEY idx_tipe (tipe)
);

-- ============================================================
-- TABEL VENDOR / SUPPLIER
-- ============================================================
CREATE TABLE IF NOT EXISTS vendor (
    id          BIGINT       NOT NULL AUTO_INCREMENT,
    kode        VARCHAR(30)  NOT NULL UNIQUE,
    nama        VARCHAR(150) NOT NULL,
    kontak      VARCHAR(100),
    telepon     VARCHAR(20),
    email       VARCHAR(150),
    alamat      TEXT,
    status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- ============================================================
-- TABEL INVOICE PEMBELIAN
-- ============================================================
CREATE TABLE IF NOT EXISTS invoice_pembelian (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    nomor_invoice   VARCHAR(50)  NOT NULL UNIQUE,
    vendor_id       BIGINT       NOT NULL,
    tanggal         DATE         NOT NULL,
    total           DECIMAL(15,2) DEFAULT 0,
    ppn             DECIMAL(15,2) DEFAULT 0,
    grand_total     DECIMAL(15,2) DEFAULT 0,
    status          ENUM('draft','proses','selesai','batal') NOT NULL DEFAULT 'draft',
    catatan         TEXT,
    user_id         BIGINT       DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vendor (vendor_id),
    KEY idx_tanggal_beli (tanggal)
);

-- ============================================================
-- TABEL DETAIL INVOICE PEMBELIAN
-- ============================================================
CREATE TABLE IF NOT EXISTS detail_invoice_pembelian (
    id                  BIGINT       NOT NULL AUTO_INCREMENT,
    invoice_id          BIGINT       NOT NULL,
    inventori_id        BIGINT       NOT NULL,
    jumlah              DECIMAL(15,3) NOT NULL,
    harga_satuan        DECIMAL(15,2) NOT NULL,
    subtotal            DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_invoice_beli (invoice_id)
);

-- ============================================================
-- TABEL INVOICE PENGIRIMAN / PENJUALAN
-- ============================================================
CREATE TABLE IF NOT EXISTS invoice_pengiriman (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    nomor_invoice   VARCHAR(50)  NOT NULL UNIQUE,
    pelanggan_nama  VARCHAR(150),
    pelanggan_kontak VARCHAR(100),
    tanggal         DATE         NOT NULL,
    total           DECIMAL(15,2) DEFAULT 0,
    ppn             DECIMAL(15,2) DEFAULT 0,
    grand_total     DECIMAL(15,2) DEFAULT 0,
    status          ENUM('draft','proses','dikirim','selesai','batal') NOT NULL DEFAULT 'draft',
    catatan         TEXT,
    user_id         BIGINT       DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tanggal_kirim (tanggal)
);

-- ============================================================
-- TABEL HPP (Harga Pokok Produksi)
-- ============================================================
CREATE TABLE IF NOT EXISTS hpp (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    nama_produk     VARCHAR(150) NOT NULL,
    bahan_baku      DECIMAL(15,2) DEFAULT 0,
    tenaga_kerja    DECIMAL(15,2) DEFAULT 0,
    overhead        DECIMAL(15,2) DEFAULT 0,
    hpp_per_unit    DECIMAL(15,2) DEFAULT 0,
    harga_jual      DECIMAL(15,2) DEFAULT 0,
    margin          DECIMAL(5,2)  DEFAULT 0,
    tanggal         DATE,
    user_id         BIGINT       DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- ============================================================
-- TABEL NOTIFIKASI
-- ============================================================
CREATE TABLE IF NOT EXISTS notifikasi (
    id          BIGINT    NOT NULL AUTO_INCREMENT,
    user_id     BIGINT    NOT NULL,
    judul       VARCHAR(200) NOT NULL,
    pesan       TEXT,
    tipe        VARCHAR(50) DEFAULT 'info',
    dibaca      TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_notif (user_id),
    KEY idx_dibaca (dibaca)
);

-- ============================================================
-- TABEL KONFIGURASI HARI KERJA
-- ============================================================
CREATE TABLE IF NOT EXISTS konfigurasi_hari (
    id          BIGINT    NOT NULL AUTO_INCREMENT,
    tanggal     DATE      NOT NULL UNIQUE,
    tipe        ENUM('libur_nasional','libur_perusahaan','hari_kerja_pengganti') NOT NULL,
    keterangan  VARCHAR(200),
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- ============================================================
-- TABEL PENGAJUAN (Cuti/Izin)
-- ============================================================
CREATE TABLE IF NOT EXISTS pengajuan (
    id              BIGINT    NOT NULL AUTO_INCREMENT,
    karyawan_id     BIGINT    NOT NULL,
    tipe            ENUM('cuti','izin','sakit','dinas_luar') NOT NULL,
    tanggal_mulai   DATE      NOT NULL,
    tanggal_selesai DATE      NOT NULL,
    alasan          TEXT,
    status          ENUM('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
    approver_id     BIGINT    DEFAULT NULL,
    approved_at     DATETIME  DEFAULT NULL,
    created_at      DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_karyawan_pengajuan (karyawan_id)
);

-- ============================================================
-- DUMMY DATA
-- ============================================================
-- Password = 'password123' hashed bcrypt
INSERT INTO users (nama, email, password, role, status) VALUES
('Super Admin ERUM', 'superadmin@erum.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'aktif'),
('Budi Santoso', 'budi@erum.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'aktif'),
('Siti Rahayu', 'siti@erum.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'aktif');

INSERT INTO perusahaan (nama, alamat, telepon, email, status) VALUES
('ERUM Indo Prima', 'Jl. Raya Sudirman No.1, Jakarta', '021-1234567', 'info@erumindo.id', 'aktif'),
('Toko Maju Bersama', 'Jl. Pahlawan No.10, Surabaya', '031-7654321', 'toko@majubersama.id', 'aktif');

INSERT INTO lokasi_gudang (nama, kode, kapasitas, alamat, status) VALUES
('Gudang Utama Jakarta', 'GDG-JKT-01', 10000, 'Jl. Industri No.5, Jakarta', 'aktif'),
('Gudang Surabaya', 'GDG-SBY-01', 5000, 'Jl. Rungkut No.22, Surabaya', 'aktif');
