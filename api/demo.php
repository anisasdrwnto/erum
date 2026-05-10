<?php
// demo.php – ERUM Demo Page (PHP backend simulation)

$fitur = $_GET['fitur'] ?? 'all';

// Simulated data (replace with real DB in production)
$hppData = [
  ['nama' => 'Nasi Kotak', 'bahan' => 12000, 'tk' => 3000, 'oh' => 2000, 'hpp' => 17000, 'jual' => 25000],
  ['nama' => 'Ayam Goreng', 'bahan' => 18000, 'tk' => 4000, 'oh' => 3000, 'hpp' => 25000, 'jual' => 35000],
  ['nama' => 'Teh Manis', 'bahan' => 2000, 'tk' => 500, 'oh' => 500, 'hpp' => 3000, 'jual' => 5000],
];

$trendData = [
  ['minggu' => 'M-4', 'qty' => 42, 'label' => 'Lalu'],
  ['minggu' => 'M-3', 'qty' => 55, 'label' => 'Lalu'],
  ['minggu' => 'M-2', 'qty' => 48, 'label' => 'Lalu'],
  ['minggu' => 'M-1', 'qty' => 70, 'label' => 'Lalu'],
  ['minggu' => 'M+1', 'qty' => 88, 'label' => 'Prediksi'],
  ['minggu' => 'M+2', 'qty' => 95, 'label' => 'Prediksi'],
];

$maxTrend = max(array_column($trendData, 'qty'));

// Handle AJAX subscribe (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
  header('Content-Type: application/json');
  // In production: save to DB or call email service
  echo json_encode(['status' => 'ok', 'message' => 'Subscribed!']);
  exit;
}

// Handle HPP calculation AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'calc_hpp') {
  header('Content-Type: application/json');
  $bahan = floatval($_POST['bahan'] ?? 0);
  $tk    = floatval($_POST['tk'] ?? 0);
  $oh    = floatval($_POST['oh'] ?? 0);
  $jual  = floatval($_POST['jual'] ?? 0);
  $qty   = max(1, intval($_POST['qty'] ?? 1));
  
  $hpp    = ($bahan + $tk + $oh) / $qty;
  $margin = $jual > 0 ? round((($jual - $hpp) / $jual) * 100, 1) : 0;
  
  echo json_encode([
    'hpp'    => number_format($hpp, 0, ',', '.'),
    'margin' => $margin,
    'profit' => number_format($jual - $hpp, 0, ',', '.'),
  ]);
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Demo ERUM – Coba Fitur Gratis</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .demo-page { padding-top: 90px; min-height: 100vh; background: var(--bg); }
    .demo-header { background: linear-gradient(135deg,#1e3a8a,#4c1d95); color:#fff; padding: 52px 0 40px; text-align:center; }
    .demo-header h1 { font-family: var(--font-head); font-size: 2.4rem; font-weight:800; }
    .demo-tabs-nav { background:#fff; border-bottom:2px solid var(--border); position:sticky; top:72px; z-index:100; }
    .demo-tabs-nav .nav-link { color:var(--text-muted); font-weight:600; border-radius:0; border-bottom:2px solid transparent; margin-bottom:-2px; }
    .demo-tabs-nav .nav-link.active { color:var(--primary); border-bottom-color:var(--primary); }
    .demo-section { padding:52px 0; }
    
    /* HPP Calculator */
    .calc-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow); padding:32px; border:1px solid var(--border); }
    .calc-label { font-weight:600; font-size:.88rem; margin-bottom:6px; color:var(--text-muted); }
    .calc-input { border:2px solid var(--border); border-radius:12px; padding:10px 16px; font-size:1rem; width:100%; transition:border-color .2s; outline:none; }
    .calc-input:focus { border-color:var(--primary); }
    .calc-result-box { background:linear-gradient(135deg,#EFF6FF,#F5F3FF); border-radius:14px; padding:24px; text-align:center; }
    .calc-result-box .label { font-size:.85rem; color:var(--text-muted); margin-bottom:4px; }
    .calc-result-box .value { font-family:var(--font-head); font-size:2rem; font-weight:800; color:var(--primary); }
    .result-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border); font-size:.9rem; }
    .result-row:last-child { border:none; }
    .result-row strong { color:var(--primary); }

    /* Trend Chart */
    .trend-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow); padding:28px; border:1px solid var(--border); }
    .bar-chart-h { display:flex; align-items:flex-end; gap:12px; height:200px; margin:24px 0; }
    .hbar { flex:1; border-radius:8px 8px 0 0; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; cursor:pointer; transition:opacity .2s; position:relative; }
    .hbar:hover { opacity:.8; }
    .hbar .hbar-val { position:absolute; top:-22px; font-size:.78rem; font-weight:700; color:var(--text); }
    .hbar .hbar-label { font-size:.72rem; color:var(--text-muted); margin-top:6px; text-align:center; }
    .hbar.past-bar { background:linear-gradient(180deg,#93C5FD,#BFDBFE); }
    .hbar.pred-bar-item { background:linear-gradient(180deg,#7C3AED,#2563EB); }
    .restock-alert { background:#FFFBEB; border:1px solid #FDE68A; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:12px; font-size:.9rem; color:#92400E; margin-top:16px; }

    /* Pricing demo */
    .addon-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border); }
    .addon-row .price { font-weight:700; color:var(--primary); }
    .total-box { background:linear-gradient(135deg,#1e3a8a,#4c1d95); color:#fff; border-radius:14px; padding:20px 24px; text-align:center; margin-top:20px; }
    .total-box .total-label { font-size:.85rem; opacity:.8; }
    .total-box .total-val { font-family:var(--font-head); font-size:2rem; font-weight:800; }

    .badge-lalu { background:#BFDBFE; color:#1e3a8a; font-size:.7rem; padding:2px 8px; border-radius:50px; }
    .badge-pred { background:linear-gradient(135deg,#7C3AED,#2563EB); color:#fff; font-size:.7rem; padding:2px 8px; border-radius:50px; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top" id="mainNav" style="background:#fff;border-bottom:1px solid var(--border);">
  <div class="container">
    <a class="navbar-brand" href="index.html">
      <span class="brand-e">E</span><span class="brand-rum">RUM</span>
    </a>
    <div class="ms-auto">
      <a href="index.html" class="btn btn-outline-secondary btn-sm me-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
      <a href="index.html#harga" class="btn btn-demo">Pilih Paket</a>
    </div>
  </div>
</nav>

<!-- DEMO HEADER -->
<div class="demo-header">
  <div class="container">
    <span class="badge bg-light text-primary mb-3 px-3 py-2">Demo Interaktif</span>
    <h1>Coba ERUM Sekarang</h1>
    <p class="mt-2" style="opacity:.8;max-width:500px;margin:auto">Eksplorasi tiga fitur utama ERUM secara langsung. Tidak perlu daftar.</p>
  </div>
</div>

<!-- DEMO TABS NAV -->
<div class="demo-tabs-nav">
  <div class="container">
    <ul class="nav nav-tabs border-0" id="demoTabs">
      <li class="nav-item">
        <button class="nav-link <?= $fitur === 'hpp' || $fitur === 'all' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-hpp">
          <i class="fa fa-calculator me-2 text-primary"></i>Kalkulator HPP
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link <?= $fitur === 'ai' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-ai">
          <i class="fa fa-brain me-2" style="color:var(--accent)"></i>AI Prediksi Tren
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link <?= $fitur === 'harga' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-harga">
          <i class="fa fa-coins me-2 text-success"></i>Simulasi Harga
        </button>
      </li>
    </ul>
  </div>
</div>

<!-- DEMO CONTENT -->
<div class="demo-section">
  <div class="container">
    <div class="tab-content" id="demoTabContent">

      <!-- TAB 1: HPP Calculator -->
      <div class="tab-pane fade <?= $fitur === 'hpp' || $fitur === 'all' ? 'show active' : '' ?>" id="tab-hpp">
        <div class="row g-4">
          <div class="col-lg-7">
            <div class="calc-card">
              <h4 class="mb-1" style="font-family:var(--font-head);font-weight:800"><i class="fa fa-calculator text-primary me-2"></i>Kalkulator HPP Otomatis</h4>
              <p class="text-muted mb-4" style="font-size:.88rem">Masukkan biaya produksi dan harga jual. ERUM hitung HPP & margin secara instan.</p>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="calc-label">Nama Produk</label>
                  <input type="text" class="calc-input" id="prodName" placeholder="cth: Nasi Goreng" value="Nasi Goreng"/>
                </div>
                <div class="col-md-6">
                  <label class="calc-label">Jumlah Produksi (unit)</label>
                  <input type="number" class="calc-input" id="prodQty" placeholder="10" value="10" min="1"/>
                </div>
                <div class="col-md-4">
                  <label class="calc-label">Bahan Baku (Rp)</label>
                  <input type="number" class="calc-input" id="prodBahan" placeholder="150000" value="150000"/>
                </div>
                <div class="col-md-4">
                  <label class="calc-label">Tenaga Kerja (Rp)</label>
                  <input type="number" class="calc-input" id="prodTK" placeholder="50000" value="50000"/>
                </div>
                <div class="col-md-4">
                  <label class="calc-label">Overhead / Lain-lain (Rp)</label>
                  <input type="number" class="calc-input" id="prodOH" placeholder="30000" value="30000"/>
                </div>
                <div class="col-md-6">
                  <label class="calc-label">Harga Jual per Unit (Rp)</label>
                  <input type="number" class="calc-input" id="prodJual" placeholder="30000" value="30000"/>
                </div>
                <div class="col-12">
                  <button class="btn btn-primary-cta w-100 mt-2" id="hitungHPP">
                    <i class="fa fa-calculator me-2"></i>Hitung HPP Sekarang
                  </button>
                </div>
              </div>
            </div>

            <!-- Daftar produk PHP -->
            <div class="calc-card mt-4">
              <h6 style="font-weight:700"><i class="fa fa-list text-primary me-2"></i>Contoh Data Produk</h6>
              <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size:.88rem">
                  <thead class="table-light">
                    <tr><th>Produk</th><th>Bahan</th><th>TK</th><th>Overhead</th><th>HPP/unit</th><th>Harga Jual</th><th>Margin</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($hppData as $d):
                      $margin = round((($d['jual'] - $d['hpp']) / $d['jual']) * 100, 1);
                    ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($d['nama']) ?></strong></td>
                      <td>Rp <?= number_format($d['bahan'],0,',','.') ?></td>
                      <td>Rp <?= number_format($d['tk'],0,',','.') ?></td>
                      <td>Rp <?= number_format($d['oh'],0,',','.') ?></td>
                      <td><strong class="text-primary">Rp <?= number_format($d['hpp'],0,',','.') ?></strong></td>
                      <td>Rp <?= number_format($d['jual'],0,',','.') ?></td>
                      <td><span class="badge bg-success"><?= $margin ?>%</span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="calc-card text-center" id="hppResultCard" style="display:none">
              <div class="calc-result-box">
                <div class="label">HPP per Unit</div>
                <div class="value" id="res-hpp">–</div>
              </div>
              <div class="mt-3">
                <div class="result-row"><span>Total Biaya Produksi</span><strong id="res-total">–</strong></div>
                <div class="result-row"><span>Harga Jual</span><strong id="res-jual">–</strong></div>
                <div class="result-row"><span>Keuntungan per Unit</span><strong id="res-profit" class="text-success">–</strong></div>
                <div class="result-row"><span>Margin Keuntungan</span><strong id="res-margin">–</strong></div>
              </div>
              <div class="mt-3" id="res-bar-wrap">
                <div class="mb-1 d-flex justify-content-between" style="font-size:.8rem"><span>Margin</span><span id="res-bar-pct">0%</span></div>
                <div class="bar-track" style="height:12px"><div class="bar-fill" id="res-bar" style="width:0%"></div></div>
              </div>
              <button class="btn btn-outline-cta w-100 mt-3" id="printHPP"><i class="fa fa-print me-2"></i>Cetak Laporan</button>
            </div>
            <div class="calc-card text-center" id="hppPlaceholder">
              <i class="fa fa-calculator fa-3x text-primary mb-3 d-block opacity-25"></i>
              <p class="text-muted">Isi form di kiri dan klik <strong>Hitung HPP</strong> untuk melihat hasil.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: AI Prediksi Tren -->
      <div class="tab-pane fade <?= $fitur === 'ai' ? 'show active' : '' ?>" id="tab-ai">
        <div class="row g-4">
          <div class="col-lg-8">
            <div class="trend-card">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                  <h4 style="font-family:var(--font-head);font-weight:800"><i class="fa fa-brain me-2" style="color:var(--accent)"></i>Grafik Prediksi Tren Penjualan</h4>
                  <p class="text-muted mb-0" style="font-size:.88rem">Data historis + prediksi AI untuk 2 minggu ke depan</p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap" style="font-size:.8rem">
                  <span class="badge-lalu">■ Data Lalu</span>
                  <span class="badge-pred">■ Prediksi AI</span>
                </div>
              </div>
              <div class="bar-chart-h">
                <?php foreach ($trendData as $d):
                  $pct = round(($d['qty'] / $maxTrend) * 100);
                  $cls = $d['label'] === 'Prediksi' ? 'pred-bar-item' : 'past-bar';
                ?>
                <div class="hbar <?= $cls ?>" style="height:<?= $pct ?>%" title="<?= $d['minggu'] ?>: <?= $d['qty'] ?> unit">
                  <span class="hbar-val"><?= $d['qty'] ?></span>
                  <span class="hbar-label"><?= $d['minggu'] ?><br/><small><?= $d['label'] ?></small></span>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="restock-alert">
                <i class="fa fa-robot fa-lg" style="color:var(--accent)"></i>
                <div>
                  <strong>Rekomendasi AI:</strong> Berdasarkan tren penjualan, ERUM merekomendasikan restock <strong>100 unit bahan baku utama</strong> sebelum akhir pekan untuk memenuhi prediksi permintaan Minggu+1.
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="trend-card">
              <h6 style="font-weight:800"><i class="fa fa-bell text-warning me-2"></i>Notifikasi Restock</h6>
              <div id="notifList">
                <div class="notif-item p-3 mb-2 rounded-3" style="background:#FFF7ED;border:1px solid #FED7AA">
                  <div style="font-size:.82rem;font-weight:700;color:#C2410C">⚠️ Stok Tepung Hampir Habis</div>
                  <div style="font-size:.78rem;color:#92400E;margin-top:4px">Sisa: 5 kg / Kebutuhan: 40 kg. Restock SEGERA.</div>
                </div>
                <div class="notif-item p-3 mb-2 rounded-3" style="background:#ECFDF5;border:1px solid #A7F3D0">
                  <div style="font-size:.82rem;font-weight:700;color:#065F46">✅ Minyak Goreng – Cukup</div>
                  <div style="font-size:.78rem;color:#047857;margin-top:4px">Stok 20 liter, cukup untuk 2 minggu ke depan.</div>
                </div>
                <div class="notif-item p-3 mb-2 rounded-3" style="background:#EFF6FF;border:1px solid #BFDBFE">
                  <div style="font-size:.82rem;font-weight:700;color:#1D4ED8">📦 Beras – Restock dalam 3 Hari</div>
                  <div style="font-size:.78rem;color:#1E40AF;margin-top:4px">Sisa: 15 kg. Prediksi habis Kamis depan.</div>
                </div>
              </div>
              <button class="btn btn-primary-cta w-100 mt-3" id="refreshNotif">
                <i class="fa fa-sync me-2"></i>Refresh Notifikasi
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: Simulasi Harga -->
      <div class="tab-pane fade <?= $fitur === 'harga' ? 'show active' : '' ?>" id="tab-harga">
        <div class="row g-4 justify-content-center">
          <div class="col-lg-6">
            <div class="calc-card">
              <h4 style="font-family:var(--font-head);font-weight:800"><i class="fa fa-coins text-success me-2"></i>Konfigurasi Fitur & Harga</h4>
              <p class="text-muted mb-4" style="font-size:.88rem">Pilih fitur yang kamu butuhkan. Bayar hanya untuk yang kamu aktifkan.</p>
              <div id="addonList">
                <div class="addon-row">
                  <div>
                    <strong><i class="fa fa-calculator text-primary me-2"></i>HPP Otomatis</strong>
                    <div style="font-size:.8rem;color:var(--text-muted)">Perhitungan HPP unlimited produk</div>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="price">Rp 49.000</span>
                    <label class="switch"><input type="checkbox" class="addon-toggle" data-price="49000" checked><span class="slider"></span></label>
                  </div>
                </div>
                <div class="addon-row">
                  <div>
                    <strong><i class="fa fa-brain me-2" style="color:var(--accent)"></i>AI Prediksi Tren</strong>
                    <div style="font-size:.8rem;color:var(--text-muted)">Prediksi + notifikasi restock otomatis</div>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="price">Rp 30.000</span>
                    <label class="switch"><input type="checkbox" class="addon-toggle" data-price="30000"><span class="slider"></span></label>
                  </div>
                </div>
                <div class="addon-row">
                  <div>
                    <strong><i class="fa fa-chart-bar text-warning me-2"></i>Laporan Lanjutan</strong>
                    <div style="font-size:.8rem;color:var(--text-muted)">Laporan bulanan, export Excel/PDF</div>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="price">Rp 20.000</span>
                    <label class="switch"><input type="checkbox" class="addon-toggle" data-price="20000"><span class="slider"></span></label>
                  </div>
                </div>
                <div class="addon-row">
                  <div>
                    <strong><i class="fa fa-users text-info me-2"></i>Multi Pengguna</strong>
                    <div style="font-size:.8rem;color:var(--text-muted)">Tambah karyawan/akun hingga 10 user</div>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="price">Rp 25.000</span>
                    <label class="switch"><input type="checkbox" class="addon-toggle" data-price="25000"><span class="slider"></span></label>
                  </div>
                </div>
              </div>
              <div class="total-box mt-4">
                <div class="total-label">Total Per Bulan</div>
                <div class="total-val" id="demoTotalPrice">Rp 49.000</div>
                <div style="font-size:.78rem;opacity:.7;margin-top:4px">Hemat hingga 20% jika berlangganan tahunan</div>
              </div>
              <a href="index.html#harga" class="btn btn-primary-cta w-100 mt-3">
                <i class="fa fa-arrow-right me-2"></i>Mulai Berlangganan Sekarang
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- FOOTER mini -->
<footer class="site-footer py-4 mt-5">
  <div class="container text-center" style="color:rgba(255,255,255,.5);font-size:.85rem">
    © 2026 <strong style="color:#fff">ERUM</strong> — Platform Manajemen UMKM Indonesia. <a href="index.html" style="color:var(--primary)">Kembali ke Beranda</a>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
  /* ── HPP Calculator (calls PHP via AJAX) ── */
  $('#hitungHPP').on('click', function(){
    const bahan = parseFloat($('#prodBahan').val()) || 0;
    const tk    = parseFloat($('#prodTK').val()) || 0;
    const oh    = parseFloat($('#prodOH').val()) || 0;
    const jual  = parseFloat($('#prodJual').val()) || 0;
    const qty   = parseInt($('#prodQty').val()) || 1;

    const btn = $(this);
    btn.html('<i class="fa fa-spinner fa-spin me-2"></i>Menghitung...').prop('disabled',true);

    $.post('demo.php', { action:'calc_hpp', bahan, tk, oh, jual, qty }, function(res){
      btn.html('<i class="fa fa-calculator me-2"></i>Hitung HPP Sekarang').prop('disabled',false);
      const data = typeof res === 'string' ? JSON.parse(res) : res;
      $('#res-hpp').text('Rp ' + data.hpp);
      $('#res-total').text('Rp ' + parseInt(bahan+tk+oh).toLocaleString('id-ID'));
      $('#res-jual').text('Rp ' + parseInt(jual).toLocaleString('id-ID'));
      $('#res-profit').text('Rp ' + data.profit);
      $('#res-margin').text(data.margin + '%');
      $('#res-bar-pct').text(data.margin + '%');
      $('#res-bar').css('width','0%').animate({'width': Math.min(data.margin,100)+'%'}, 800);
      $('#hppPlaceholder').hide();
      $('#hppResultCard').show();
    }).fail(function(){
      // Client-side fallback (for static hosting)
      const hpp    = (bahan + tk + oh) / Math.max(1, qty);
      const margin = jual > 0 ? Math.round(((jual - hpp) / jual) * 100 * 10)/10 : 0;
      const profit = jual - hpp;
      btn.html('<i class="fa fa-calculator me-2"></i>Hitung HPP Sekarang').prop('disabled',false);
      $('#res-hpp').text('Rp ' + Math.round(hpp).toLocaleString('id-ID'));
      $('#res-total').text('Rp ' + parseInt(bahan+tk+oh).toLocaleString('id-ID'));
      $('#res-jual').text('Rp ' + parseInt(jual).toLocaleString('id-ID'));
      $('#res-profit').text('Rp ' + Math.round(profit).toLocaleString('id-ID'));
      $('#res-margin').text(margin + '%');
      $('#res-bar-pct').text(margin + '%');
      $('#res-bar').css('width','0%').animate({'width': Math.min(margin,100)+'%'}, 800);
      $('#hppPlaceholder').hide();
      $('#hppResultCard').show();
    });
  });

  /* Print HPP Report */
  $('#printHPP').on('click', function(){
    window.print();
  });

  /* ── Refresh Notifikasi ── */
  $('#refreshNotif').on('click', function(){
    const btn = $(this);
    btn.html('<i class="fa fa-spinner fa-spin me-2"></i>Memperbarui...').prop('disabled',true);
    setTimeout(function(){
      btn.html('<i class="fa fa-sync me-2"></i>Refresh Notifikasi').prop('disabled',false);
      const notif = `
        <div class="notif-item p-3 mb-2 rounded-3" style="background:#FFF7ED;border:1px solid #FED7AA">
          <div style="font-size:.82rem;font-weight:700;color:#C2410C">⚠️ Gula Pasir Hampir Habis</div>
          <div style="font-size:.78rem;color:#92400E;margin-top:4px">Sisa: 2 kg / Kebutuhan: 20 kg. Restock SEGERA.</div>
        </div>
        <div class="notif-item p-3 mb-2 rounded-3" style="background:#ECFDF5;border:1px solid #A7F3D0">
          <div style="font-size:.82rem;font-weight:700;color:#065F46">✅ Beras – Sudah Diperbarui</div>
          <div style="font-size:.78rem;color:#047857;margin-top:4px">Restock 25 kg masuk. Stok aman 3 minggu.</div>
        </div>`;
      $('#notifList').hide().html(notif).fadeIn(400);
    }, 1200);
  });

  /* ── Addon price calculator ── */
  function updateAddonTotal(){
    let total = 0;
    $('.addon-toggle:checked').each(function(){
      total += parseInt($(this).data('price'));
    });
    $('#demoTotalPrice').text('Rp ' + total.toLocaleString('id-ID'));
  }
  $('.addon-toggle').on('change', updateAddonTotal);
  updateAddonTotal();

  /* Navbar scroll */
  $(window).on('scroll', function(){
    if($(this).scrollTop()>50) $('#mainNav').addClass('scrolled');
    else $('#mainNav').removeClass('scrolled');
  });
});
</script>
</body>
</html>
