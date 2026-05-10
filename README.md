# ERUM System – Dokumentasi Setup

## Struktur File

```
erum_system/
├── config/
│   └── db.php                 # Konfigurasi database & helper session
├── index.php                  # Landing page (konversi dari HTML)
├── login.php                  # Halaman login + register (jQuery AJAX)
├── proses_login.php           # Backend proses login → JSON
├── proses_register.php        # Backend proses register → JSON
├── logout.php                 # Destroy session & redirect
├── dashboard_admin.php        # Dashboard Super Admin (role: superadmin)
├── dashboard.php              # Dashboard Karyawan (role: user)
├── user_management.php        # CRUD User Management (Super Admin only)
├── database.sql               # Skema TiDB + dummy data
└── README.md                  # File ini
```

---

## Setup & Instalasi

### 1. Database (TiDB / MySQL)
```bash
# Import skema ke TiDB
mysql -h 127.0.0.1 -P 4000 -u root < database.sql

# Atau MySQL standar
mysql -u root -p < database.sql
```

### 2. Konfigurasi Koneksi
Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '4000');     // TiDB default port; MySQL: 3306
define('DB_NAME', 'erum_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. PHP Server (Development)
```bash
cd erum_system/
php -S localhost:8080
```
Buka: http://localhost:8080

### 4. Apache / Nginx
Letakkan folder `erum_system/` di dalam `htdocs/` (XAMPP) atau `www/` (LAMP).

---

## Akun Default (Dummy Data)

| Email | Password | Role |
|-------|----------|------|
| superadmin@erum.id | password | superadmin |
| budi@erum.id | password | user |
| siti@erum.id | password | user |

> ⚠️ Ganti password default setelah setup!

---

## Alur Login

```
index.php (Landing)
    ↓ klik Login/Daftar
login.php (jQuery AJAX)
    ↓ POST
proses_login.php → JSON response
    ↓ berhasil
    ├── role = superadmin → dashboard_admin.php
    └── role = user       → dashboard.php
```

## Role & Akses

| Fitur | Superadmin | User/Karyawan |
|-------|-----------|---------------|
| Dashboard Admin | ✅ | ❌ |
| User Management | ✅ | ❌ |
| Data Karyawan | ✅ | ❌ |
| Presensi | ✅ | Hanya milik sendiri |
| Logbook | ✅ (validasi) | ✅ (input) |
| Payroll | ✅ | ❌ |
| Inventori | ✅ | ❌ |

---

## Tabel Database (TiDB)

| Tabel | Keterangan |
|-------|-----------|
| `users` | Login, register, role (superadmin/user) |
| `perusahaan` | Data perusahaan / companies |
| `karyawan` | Data SDM karyawan |
| `presensi` | Absensi harian |
| `logbook` | Catatan kegiatan kerja |
| `master_gaji` | Komponen gaji per bulan |
| `inventori` | Master barang/stok |
| `lokasi_gudang` | Lokasi & penyimpanan |
| `pergerakan_stok` | Stok masuk/keluar/transfer |
| `vendor` | Data supplier |
| `invoice_pembelian` | Invoice beli dari vendor |
| `invoice_pengiriman` | Invoice jual ke pelanggan |
| `hpp` | Harga Pokok Produksi |
| `notifikasi` | Notifikasi sistem |
| `konfigurasi_hari` | Hari libur & kerja |
| `pengajuan` | Cuti, izin, sakit |

---

## Teknologi

- **Frontend**: jQuery 3.7.1, Bootstrap 5.3.2, Font Awesome 6.5, Chart.js 4.4
- **Backend**: PHP 8.x (PDO)
- **Database**: TiDB (kompatibel MySQL 8)
- **Font**: Plus Jakarta Sans, Sora (Google Fonts)
