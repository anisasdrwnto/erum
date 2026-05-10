<?php
require_once 'db.php';
requireLogin();
// Redirect superadmin to their dashboard
if (isSuperAdmin()) { header('Location: dashboard_admin.php'); exit; }
$nama = $_SESSION['nama'];
$today = date('l, d F Y');
$todayId = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$today = $todayId[date('l')] . ', ' . date('d') . ' ' . ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Karyawan – ERUM</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet"/>
  <style>
    :root { --primary:#6366f1; --accent:#10b981; --dark:#0f0f1a; --card-bg:#1a1a2e; --input-bg:#16213e; --border:rgba(99,102,241,.2); --text:#e2e8f0; --text-muted:#94a3b8; }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--dark); color:var(--text); min-height:100vh; }
    .topbar { background:#12122a; border-bottom:1px solid var(--border); padding:.9rem 2rem; display:flex; align-items:center; justify-content:space-between; }
    .brand { font-family:'Sora',sans-serif; font-size:1.3rem; font-weight:800; }
    .brand .e { color:var(--primary); }
    .brand .rum { color:#fff; }
    .user-info { display:flex; align-items:center; gap:.8rem; }
    .avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--primary),#a855f7); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:.8rem; }
    .main { max-width:1000px; margin:0 auto; padding:2rem 1rem; }
    .welcome-banner { background:linear-gradient(135deg,#4338ca,#6366f1,#7c3aed); border-radius:18px; padding:1.8rem 2rem; color:#fff; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; }
    .welcome-banner h2 { font-size:1.3rem; font-weight:800; }
    .welcome-banner p { font-size:.82rem; opacity:.8; }
    .cards-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
    .card-item { background:var(--card-bg); border:1px solid var(--border); border-radius:14px; padding:1.3rem; transition:transform .2s; }
    .card-item:hover { transform:translateY(-2px); }
    .card-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin-bottom:.8rem; }
    .ci-blue { background:rgba(99,102,241,.15); color:var(--primary); }
    .ci-green { background:rgba(16,185,129,.15); color:#10b981; }
    .ci-orange { background:rgba(249,115,22,.15); color:#f97316; }
    .card-item h5 { font-size:.9rem; font-weight:700; margin-bottom:.4rem; }
    .card-item p { font-size:.8rem; color:var(--text-muted); }
    .btn-card { display:inline-block; margin-top:.8rem; padding:.4rem .9rem; background:rgba(99,102,241,.15); color:var(--primary); border-radius:8px; font-size:.78rem; font-weight:600; text-decoration:none; border:1px solid var(--border); transition:.2s; }
    .btn-card:hover { background:var(--primary); color:#fff; }
    @media(max-width:600px){ .cards-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<div class="topbar">
  <div class="brand"><span class="e">E</span><span class="rum">RUM</span></div>
  <div class="user-info">
    <div class="avatar"><?= strtoupper(substr($nama,0,2)) ?></div>
    <div>
      <div style="font-size:.82rem;font-weight:600"><?= htmlspecialchars($nama) ?></div>
      <div style="font-size:.7rem;color:var(--text-muted)"><a href="logout.php" style="color:#ef4444;text-decoration:none">Keluar</a></div>
    </div>
  </div>
</div>
<div class="main">
  <div class="welcome-banner">
    <div><h2>Halo, <?= htmlspecialchars($nama) ?>!</h2><p>Karyawan &bull; <?= $today ?></p></div>
    <i class="fa fa-user-tie" style="font-size:2.5rem;opacity:.25"></i>
  </div>
  <div class="cards-grid">
    <div class="card-item">
      <div class="card-icon ci-blue"><i class="fa fa-calendar-check"></i></div>
      <h5>Presensi Saya</h5>
      <p>Lihat dan input kehadiran harian kamu di sini.</p>
      <a href="presensi_user.php" class="btn-card">Buka</a>
    </div>
    <div class="card-item">
      <div class="card-icon ci-green"><i class="fa fa-book-open"></i></div>
      <h5>Logbook Saya</h5>
      <p>Catat kegiatan pekerjaan harian dan kirim ke atasan.</p>
      <a href="logbook_user.php" class="btn-card">Buka</a>
    </div>
    <div class="card-item">
      <div class="card-icon ci-orange"><i class="fa fa-file-alt"></i></div>
      <h5>Pengajuan Izin</h5>
      <p>Ajukan cuti, izin, atau sakit dengan mudah.</p>
      <a href="pengajuan_user.php" class="btn-card">Buka</a>
    </div>
  </div>
</div>
</body>
</html>
