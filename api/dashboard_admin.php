<?php
session_start();
require_once 'db.php';

// wajib login + superadmin
requireSuperAdmin();

// pakai koneksi dari db.php
global $connection;
$db = $connection;

// Fetch summary stats
$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$aktifUsers    = $db->query("SELECT COUNT(*) FROM users WHERE status='aktif'")->fetchColumn();
$companies     = $db->query("SELECT COUNT(*) FROM perusahaan")->fetchColumn();
$totalKaryawan = $db->query("SELECT COUNT(*) FROM karyawan")->fetchColumn();

// Users per role
$roleRows = $db->query("SELECT role, COUNT(*) AS total FROM users GROUP BY role")->fetchAll();

$roleData = [];
foreach ($roleRows as $r) {
    $roleData[$r['role']] = $r['total'];
}

// Status users
$aktifCount    = $db->query("SELECT COUNT(*) FROM users WHERE status='aktif'")->fetchColumn();
$nonaktifCount = $db->query("SELECT COUNT(*) FROM users WHERE status='nonaktif'")->fetchColumn();

// session user
$adminNama = $_SESSION['nama'] ?? 'Admin';

// tanggal
$hari = [
    'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
    'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];

$bulan = [
    1=>'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'
];

$today = $hari[date('l')] . ', ' . date('d') . ' ' . $bulan[(int)date('m')] . ' ' . date('Y');

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Super Admin – ERUM</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    :root {
      --sidebar-w: 240px;
      --sidebar-bg: #12122a;
      --sidebar-hover: rgba(99,102,241,.12);
      --sidebar-active: rgba(99,102,241,.2);
      --primary: #6366f1;
      --primary-light: rgba(99,102,241,.15);
      --accent: #10b981;
      --dark: #0b0b1a;
      --main-bg: #f0f2ff;
      --card-bg: #ffffff;
      --text: #1e1b4b;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --header-h: 62px;
      --sidebar-label: #7c7ca0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--main-bg); color: var(--text); min-height: 100vh; display: flex; }

    /* SIDEBAR */
    .sidebar {
      width: var(--sidebar-w); min-height: 100vh; background: var(--sidebar-bg);
      display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100;
      transition: transform .3s; overflow: hidden;
    }
    .sidebar-logo {
      padding: 1.2rem 1.2rem 1rem;
      display: flex; align-items: center; gap: .6rem; border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .sidebar-logo .brand { font-family:'Sora',sans-serif; font-size:1.4rem; font-weight:800; letter-spacing:-0.5px; }
    .sidebar-logo .brand .e { color: var(--primary); }
    .sidebar-logo .brand .rum { color: #fff; }
    .sidebar-logo small { display:block; font-size:.68rem; color: #7c7ca0; font-weight:500; }
    .sidebar-scroll { flex: 1; overflow-y: auto; padding: .5rem 0 1rem; }
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius:4px; }
    .nav-label {
      font-size: .65rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
      color: var(--sidebar-label); padding: 1rem 1rem .4rem; margin-top: .2rem;
    }
    .nav-item {
      display: flex; align-items: center; gap: .7rem;
      padding: .52rem 1rem; color: #a0a0c8; text-decoration: none;
      font-size: .82rem; font-weight: 500; border-radius: 0;
      transition: background .2s, color .2s; cursor: pointer; position: relative;
    }
    .nav-item:hover { background: var(--sidebar-hover); color: #fff; }
    .nav-item.active { background: var(--sidebar-active); color: var(--primary); font-weight: 600; }
    .nav-item.active::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:var(--primary); border-radius:0 3px 3px 0; }
    .nav-item .nav-icon { width: 16px; text-align: center; font-size: .85rem; }
    .nav-item .chevron { margin-left: auto; font-size: .65rem; transition: transform .2s; }
    .nav-item.open .chevron { transform: rotate(90deg); }
    .nav-sub { display: none; padding-left: 2.5rem; }
    .nav-sub.show { display: block; }
    .nav-sub .nav-item { font-size: .79rem; padding: .42rem .8rem; }

    /* MAIN */
    .main-wrap { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

    /* HEADER */
    .topbar {
      height: var(--header-h); background: var(--card-bg); border-bottom: 1px solid var(--border);
      display: flex; align-items: center; padding: 0 1.5rem; gap: 1rem; position: sticky; top: 0; z-index: 50;
    }
    .topbar-toggle { background:none; border:none; font-size:1.1rem; color:var(--text-muted); cursor:pointer; display:none; }
    .topbar-title { font-size:.95rem; font-weight:700; flex:1; color:var(--text); }
    .topbar-icons { display:flex; align-items:center; gap:.8rem; }
    .topbar-icon-btn {
      width:36px; height:36px; border-radius:10px; background:var(--main-bg);
      border:1px solid var(--border); display:flex; align-items:center; justify-content:center;
      cursor:pointer; position:relative; color:var(--text-muted); font-size:.85rem; transition:all .2s;
    }
    .topbar-icon-btn:hover { background: var(--primary-light); color: var(--primary); }
    .notif-badge { position:absolute; top:4px; right:4px; width:8px; height:8px; background:#ef4444; border-radius:50%; border:2px solid #fff; }
    .topbar-avatar {
      width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--primary),#a855f7);
      display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:.8rem; cursor:pointer;
    }
    .topbar-user small { font-size:.7rem; color:var(--text-muted); display:block; }
    .topbar-user span { font-size:.82rem; font-weight:600; color:var(--text); }

    /* CONTENT */
    .main-content { flex:1; padding:1.5rem; }

    /* WELCOME BANNER */
    .welcome-banner {
      background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #7c3aed 100%);
      border-radius: 18px; padding: 1.8rem 2rem; color:#fff; display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; position:relative; overflow:hidden;
    }
    .welcome-banner::before { content:''; position:absolute; right:-60px; top:-60px; width:250px; height:250px; background:rgba(255,255,255,.06); border-radius:50%; }
    .welcome-banner::after { content:''; position:absolute; right:80px; bottom:-80px; width:180px; height:180px; background:rgba(255,255,255,.05); border-radius:50%; }
    .welcome-banner h2 { font-size:1.4rem; font-weight:800; margin-bottom:.2rem; }
    .welcome-banner p { font-size:.82rem; opacity:.8; }
    .welcome-banner .shield-icon { font-size:3rem; opacity:.3; position:relative; z-index:1; }

    /* STAT CARDS */
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
    .stat-card {
      background:var(--card-bg); border-radius:14px; padding:1.2rem; border:1px solid var(--border);
      display:flex; align-items:center; gap:1rem; transition:box-shadow .2s;
    }
    .stat-card:hover { box-shadow: 0 4px 20px rgba(99,102,241,.1); }
    .stat-icon {
      width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;
    }
    .stat-icon.blue { background: rgba(99,102,241,.12); color: var(--primary); }
    .stat-icon.green { background: rgba(16,185,129,.12); color: #10b981; }
    .stat-icon.purple { background: rgba(168,85,247,.12); color: #a855f7; }
    .stat-icon.orange { background: rgba(249,115,22,.12); color: #f97316; }
    .stat-val { font-size:1.6rem; font-weight:800; color:var(--text); line-height:1; }
    .stat-lbl { font-size:.75rem; color:var(--text-muted); margin-top:.2rem; }

    /* CHARTS ROW */
    .charts-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem; }
    .chart-card {
      background:var(--card-bg); border-radius:14px; padding:1.3rem; border:1px solid var(--border);
    }
    .chart-card h6 { font-size:.85rem; font-weight:700; color:var(--text); margin-bottom:1rem; }
    canvas { max-height:200px; }

    /* TABLE CARD */
    .table-card {
      background:var(--card-bg); border-radius:14px; border:1px solid var(--border); overflow:hidden;
    }
    .table-card-header {
      padding:1rem 1.3rem; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border);
    }
    .table-card-header h6 { font-size:.88rem; font-weight:700; color:var(--text); margin:0; }
    .badge-role { padding:.25rem .6rem; border-radius:6px; font-size:.7rem; font-weight:600; }
    .badge-sa { background:rgba(99,102,241,.15); color:var(--primary); }
    .badge-admin { background:rgba(16,185,129,.15); color:#10b981; }
    .badge-user { background:rgba(100,116,139,.12); color:#64748b; }
    .badge-aktif { background:rgba(16,185,129,.15); color:#10b981; }
    .badge-nonaktif { background:rgba(239,68,68,.12); color:#ef4444; }
    table { width:100%; border-collapse:collapse; }
    thead th { padding:.7rem 1.3rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); background:#f8f9ff; border-bottom:1px solid var(--border); }
    tbody td { padding:.75rem 1.3rem; font-size:.82rem; border-bottom:1px solid var(--border); }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover { background:#f8f9ff; }

    /* FOOTER */
    .site-footer { padding:.8rem 1.5rem; border-top:1px solid var(--border); background:var(--card-bg); text-align:center; font-size:.75rem; color:var(--text-muted); }

    /* RESPONSIVE */
    @media(max-width:991px){
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .main-wrap { margin-left:0; }
      .topbar-toggle { display:block; }
      .stats-grid { grid-template-columns:repeat(2,1fr); }
      .charts-row { grid-template-columns:1fr; }
    }
    @media(max-width:575px){
      .stats-grid { grid-template-columns:1fr 1fr; }
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="brand"><span class="e">E</span><span class="rum">RUM</span></div>
    <small>Enterprise Resource Planning</small>
  </div>
  <div class="sidebar-scroll">

    <div class="nav-label">Utama</div>
    <a class="nav-item active" href="dashboard_admin.php"><span class="nav-icon"><i class="fa fa-house"></i></span>Dashboard Utama</a>
    <a class="nav-item" href="analitik.php"><span class="nav-icon"><i class="fa fa-chart-bar"></i></span>Analitik Bisnis</a>
    <a class="nav-item" href="perusahaan.php"><span class="nav-icon"><i class="fa fa-building"></i></span>Master Perusahaan</a>

    <div class="nav-label">Sumber Daya Manusia</div>
    <a class="nav-item" href="karyawan.php"><span class="nav-icon"><i class="fa fa-users"></i></span>Data Karyawan</a>
    <a class="nav-item" href="presensi.php"><span class="nav-icon"><i class="fa fa-calendar-check"></i></span>Presensi Karyawan</a>
    <a class="nav-item" href="logbook.php"><span class="nav-icon"><i class="fa fa-book-open"></i></span>Validasi Logbook</a>
    <a class="nav-item" href="pengajuan.php"><span class="nav-icon"><i class="fa fa-file-alt"></i></span>Master Pengajuan</a>
    <a class="nav-item" href="rekapitulasi.php"><span class="nav-icon"><i class="fa fa-chart-line"></i></span>Rekapitulasi Kinerja</a>

    <div class="nav-label">Penggajian (Payroll)</div>
    <a class="nav-item nav-toggle" data-target="payroll-sub"><span class="nav-icon"><i class="fa fa-money-check-alt"></i></span>Master Gaji<i class="fa fa-chevron-right chevron"></i></a>
    <div class="nav-sub" id="payroll-sub">
      <a class="nav-item" href="master_gaji.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Komponen Gaji</a>
      <a class="nav-item" href="tunjangan.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Tunjangan</a>
    </div>
    <a class="nav-item" href="payroll.php"><span class="nav-icon"><i class="fa fa-file-invoice-dollar"></i></span>Payroll Bulanan</a>

    <div class="nav-label">Manajemen Gudang</div>
    <a class="nav-item nav-toggle" data-target="inv-sub"><span class="nav-icon"><i class="fa fa-boxes-stacked"></i></span>Master Inventori<i class="fa fa-chevron-right chevron"></i></a>
    <div class="nav-sub" id="inv-sub">
      <a class="nav-item" href="inventori.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Daftar Barang</a>
      <a class="nav-item" href="kategori.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Kategori</a>
    </div>
    <a class="nav-item nav-toggle" data-target="lokasi-sub"><span class="nav-icon"><i class="fa fa-warehouse"></i></span>Lokasi & Penyimpanan<i class="fa fa-chevron-right chevron"></i></a>
    <div class="nav-sub" id="lokasi-sub">
      <a class="nav-item" href="lokasi_gudang.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Lokasi Gudang</a>
    </div>
    <a class="nav-item nav-toggle" data-target="stok-sub"><span class="nav-icon"><i class="fa fa-arrow-right-arrow-left"></i></span>Pergerakan Stok<i class="fa fa-chevron-right chevron"></i></a>
    <div class="nav-sub" id="stok-sub">
      <a class="nav-item" href="stok_masuk.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Stok Masuk</a>
      <a class="nav-item" href="stok_keluar.php"><span class="nav-icon"><i class="fa fa-circle"></i></span>Stok Keluar</a>
    </div>

    <div class="nav-label">Transaksi & Keuangan</div>
    <a class="nav-item" href="vendor.php"><span class="nav-icon"><i class="fa fa-truck"></i></span>Data Vendor</a>
    <a class="nav-item" href="invoice_pembelian.php"><span class="nav-icon"><i class="fa fa-file-invoice"></i></span>Invoice Pembelian</a>
    <a class="nav-item" href="invoice_pengiriman.php"><span class="nav-icon"><i class="fa fa-paper-plane"></i></span>Invoice Pengiriman</a>

    <div class="nav-label">Sistem & Admin</div>
    <a class="nav-item" href="user_management.php"><span class="nav-icon"><i class="fa fa-user-shield"></i></span>User Management</a>
    <a class="nav-item" href="logbook_pekerjaan.php"><span class="nav-icon"><i class="fa fa-clipboard-list"></i></span>Logbook Pekerjaan</a>
    <a class="nav-item" href="konfigurasi_hari.php"><span class="nav-icon"><i class="fa fa-calendar-days"></i></span>Konfigurasi Hari</a>

    <div class="nav-label">Pengaturan</div>
    <a class="nav-item" href="informasi_perusahaan.php"><span class="nav-icon"><i class="fa fa-circle-info"></i></span>Informasi Perusahaan</a>
    <a class="nav-item" href="backup.php"><span class="nav-icon"><i class="fa fa-database"></i></span>Backup & Restore</a>
    <a class="nav-item" href="notifikasi_email.php"><span class="nav-icon"><i class="fa fa-envelope"></i></span>Notifikasi Email</a>

  </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
  <!-- TOPBAR -->
  <header class="topbar">
    <button class="topbar-toggle" id="sidebarToggle"><i class="fa fa-bars"></i></button>
    <div class="topbar-title">Dashboard Super Admin</div>
    <div class="topbar-icons">
      <div class="topbar-icon-btn"><i class="fa fa-calendar"></i></div>
      <div class="topbar-icon-btn"><i class="fa fa-bell"></i><span class="notif-badge"></span></div>
      <div class="topbar-icon-btn"><i class="fa fa-gear"></i></div>
      <div class="topbar-avatar"><?= strtoupper(substr($adminNama,0,2)) ?></div>
      <div class="topbar-user ms-1">
        <span><?= htmlspecialchars($adminNama) ?></span>
        <small><a href="logout.php" style="color:#ef4444;text-decoration:none;">Keluar</a></small>
      </div>
    </div>
  </header>

  <!-- CONTENT -->
  <main class="main-content">

    <!-- WELCOME BANNER -->
    <div class="welcome-banner">
      <div>
        <h2>Selamat Datang, <?= htmlspecialchars($adminNama) ?></h2>
        <p>Super Administrator &bull; <?= $today ?></p>
      </div>
      <i class="fa fa-shield-halved shield-icon"></i>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa fa-users"></i></div>
        <div><div class="stat-val"><?= $totalUsers ?></div><div class="stat-lbl">Total Users</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa fa-circle-check"></i></div>
        <div><div class="stat-val"><?= $aktifUsers ?></div><div class="stat-lbl">Users Aktif</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><i class="fa fa-building"></i></div>
        <div><div class="stat-val"><?= $companies ?></div><div class="stat-lbl">Companies</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa fa-gear"></i></div>
        <div><div class="stat-val">14</div><div class="stat-lbl">Modules</div></div>
      </div>
    </div>

    <!-- CHARTS -->
    <div class="charts-row">
      <div class="chart-card">
        <h6>Distribusi User per Role</h6>
        <canvas id="roleChart"></canvas>
      </div>
      <div class="chart-card">
        <h6>Status User</h6>
        <canvas id="statusChart"></canvas>
      </div>
    </div>

    <!-- USER TABLE -->
    <div class="table-card">
      <div class="table-card-header">
        <h6><i class="fa fa-users me-2 text-primary"></i>Daftar User Terdaftar</h6>
        <a href="user_management.php" style="font-size:.78rem;color:var(--primary);text-decoration:none;font-weight:600;">Lihat Semua <i class="fa fa-arrow-right ms-1"></i></a>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Bergabung</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          <?php
          $users = $db->query("SELECT id,nama,email,role,status,created_at FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
          foreach($users as $i=>$u):
          ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
            <td style="color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <?php
              $roleClass = ['superadmin'=>'badge-sa','user'=>'badge-user'][$u['role']] ?? 'badge-user';
              $roleLabel = ['superadmin'=>'Super Admin','user'=>'Karyawan'][$u['role']] ?? $u['role'];
              ?>
              <span class="badge-role <?= $roleClass ?>"><?= $roleLabel ?></span>
            </td>
            <td><span class="badge-role <?= $u['status']==='aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>"><?= ucfirst($u['status']) ?></span></td>
            <td style="color:var(--text-muted)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>

  <footer class="site-footer">Copyright © 2026 ERUM. All rights reserved.</footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
  // Sidebar toggle mobile
  $('#sidebarToggle').on('click', function(){
    $('#sidebar').toggleClass('open');
  });

  // Nav sub-toggle
  $('.nav-toggle').on('click', function(){
    var target = $(this).data('target');
    $('#'+target).toggleClass('show');
    $(this).toggleClass('open');
  });

  // Role chart
  var roleCtx = document.getElementById('roleChart').getContext('2d');
  new Chart(roleCtx, {
    type: 'doughnut',
    data: {
      labels: ['Super Admin','Admin','Manager','Staff'],
      datasets:[{
        data: [
          <?= $roleData['superadmin'] ?? 1 ?>,
          <?= ($roleData['admin'] ?? 0) + 2 ?>,
          <?= ($roleData['manager'] ?? 0) + 1 ?>,
          <?= ($roleData['user'] ?? 2) + 3 ?>
        ],
        backgroundColor: ['#6366f1','#10b981','#f59e0b','#3b82f6'],
        borderWidth: 0,
        hoverOffset: 6,
      }]
    },
    options: {
      cutout: '65%', plugins:{ legend:{ position:'bottom', labels:{ font:{size:11}, padding:12 } } },
      responsive: true, maintainAspectRatio: true,
    }
  });

  // Status chart
  var statusCtx = document.getElementById('statusChart').getContext('2d');
  new Chart(statusCtx, {
    type: 'pie',
    data: {
      labels: ['Aktif','Nonaktif'],
      datasets:[{
        data: [<?= $aktifCount ?>, <?= $nonaktifCount ?>],
        backgroundColor: ['#10b981','#ef4444'],
        borderWidth: 0,
        hoverOffset: 6,
      }]
    },
    options: {
      plugins:{ legend:{ position:'bottom', labels:{ font:{size:11}, padding:12 } } },
      responsive: true, maintainAspectRatio: true,
    }
  });
});
</script>
</body>
</html>
