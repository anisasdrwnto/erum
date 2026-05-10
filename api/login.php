<?php
require_once 'db.php';
sessionStart();
if (isLoggedIn()) {
    header('Location: ' . (isSuperAdmin() ? 'dashboard_admin.php' : 'dashboard.php'));
    exit;
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login – ERUM</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --primary: #6366f1;
      --primary-dark: #4f46e5;
      --accent: #10b981;
      --dark: #0f0f1a;
      --card-bg: #1a1a2e;
      --input-bg: #16213e;
      --border: rgba(99,102,241,0.25);
      --text: #e2e8f0;
      --text-muted: #94a3b8;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--dark);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      color: var(--text);
    }
    .bg-blobs {
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
    }
    .blob {
      position: absolute; border-radius: 50%; filter: blur(80px); opacity: .18;
    }
    .blob1 { width: 500px; height: 500px; background: #6366f1; top: -100px; left: -100px; animation: blobMove 8s ease-in-out infinite alternate; }
    .blob2 { width: 400px; height: 400px; background: #10b981; bottom: -80px; right: -80px; animation: blobMove 10s ease-in-out infinite alternate-reverse; }
    @keyframes blobMove { from { transform: translate(0,0) scale(1); } to { transform: translate(40px,30px) scale(1.08); } }
    .login-wrap {
      position: relative; z-index: 1; width: 100%; max-width: 440px; padding: 1rem;
    }
    .login-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2.5rem 2rem;
      box-shadow: 0 25px 60px rgba(0,0,0,.5);
      backdrop-filter: blur(10px);
    }
    .brand {
      font-family: 'Sora', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      text-align: center;
      margin-bottom: .25rem;
      letter-spacing: -1px;
    }
    .brand .e { color: var(--primary); }
    .brand .rum { color: #fff; }
    .sub-brand { text-align: center; color: var(--text-muted); font-size: .85rem; margin-bottom: 2rem; }
    .tab-switch {
      display: flex; background: var(--input-bg); border-radius: 12px; padding: 4px; margin-bottom: 1.5rem;
    }
    .tab-btn {
      flex: 1; padding: .5rem; border: none; background: transparent; color: var(--text-muted);
      border-radius: 10px; font-family: inherit; font-size: .9rem; font-weight: 600; cursor: pointer; transition: all .25s;
    }
    .tab-btn.active { background: var(--primary); color: #fff; }
    .form-group { margin-bottom: 1rem; }
    .form-label { font-size: .82rem; font-weight: 600; color: var(--text-muted); margin-bottom: .4rem; display: block; }
    .input-wrap { position: relative; }
    .input-wrap .icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: .9rem; z-index: 2; }
    .form-input {
      width: 100%; background: var(--input-bg); border: 1px solid var(--border);
      border-radius: 10px; padding: .7rem 1rem .7rem 2.6rem;
      color: var(--text); font-family: inherit; font-size: .92rem; outline: none; transition: border .2s;
    }
    .form-input:focus { border-color: var(--primary); }
    .form-input::placeholder { color: #475569; }
    select.form-input { cursor: pointer; }
    select.form-input option { background: var(--card-bg); }
    .eye-btn {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0;
    }
    .btn-submit {
      width: 100%; padding: .8rem; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      border: none; border-radius: 12px; color: #fff; font-family: inherit; font-weight: 700;
      font-size: 1rem; cursor: pointer; margin-top: .5rem; transition: opacity .2s, transform .15s;
    }
    .btn-submit:hover { opacity: .9; transform: translateY(-1px); }
    .alert-err {
      background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.4);
      border-radius: 10px; padding: .7rem 1rem; font-size: .85rem; color: #fca5a5;
      margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;
    }
    .alert-ok {
      background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.4);
      border-radius: 10px; padding: .7rem 1rem; font-size: .85rem; color: #6ee7b7;
      margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;
    }
    .divider { text-align: center; color: var(--text-muted); font-size: .8rem; margin: 1rem 0; }
    .spinner { width:18px; height:18px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; display:inline-block; vertical-align:middle; }
    @keyframes spin { to { transform: rotate(360deg); } }
    #registerForm { display: none; }
  </style>
</head>
<body>
<div class="bg-blobs"><div class="blob blob1"></div><div class="blob blob2"></div></div>
<div class="login-wrap">
  <div class="login-card">
    <div class="brand"><span class="e">E</span><span class="rum">RUM</span></div>
    <div class="sub-brand">Platform Manajemen UMKM Berbasis AI</div>

    <div class="tab-switch">
      <button class="tab-btn active" id="tabLogin">Masuk</button>
      <button class="tab-btn" id="tabRegister">Daftar</button>
    </div>

    <div id="alertBox"></div>

    <!-- LOGIN FORM -->
    <div id="loginForm">
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrap">
          <i class="fa fa-envelope icon"></i>
          <input type="email" class="form-input" id="loginEmail" placeholder="email@contoh.com"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="fa fa-lock icon"></i>
          <input type="password" class="form-input" id="loginPass" placeholder="Masukkan password"/>
          <button type="button" class="eye-btn" id="eyeLogin"><i class="fa fa-eye"></i></button>
        </div>
      </div>
      <button class="btn-submit" id="btnLogin">
        <span id="loginBtnText"><i class="fa fa-sign-in-alt me-2"></i>Masuk</span>
      </button>
    </div>

    <!-- REGISTER FORM -->
    <div id="registerForm">
      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <div class="input-wrap">
          <i class="fa fa-user icon"></i>
          <input type="text" class="form-input" id="regNama" placeholder="Nama lengkap kamu"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrap">
          <i class="fa fa-envelope icon"></i>
          <input type="email" class="form-input" id="regEmail" placeholder="email@contoh.com"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="fa fa-lock icon"></i>
          <input type="password" class="form-input" id="regPass" placeholder="Min. 8 karakter"/>
          <button type="button" class="eye-btn" id="eyeReg"><i class="fa fa-eye"></i></button>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password</label>
        <div class="input-wrap">
          <i class="fa fa-lock icon"></i>
          <input type="password" class="form-input" id="regPass2" placeholder="Ulangi password"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Role / Jabatan</label>
        <div class="input-wrap">
          <i class="fa fa-id-badge icon"></i>
          <select class="form-input" id="regRole">
            <option value="user">Karyawan (User)</option>
            <option value="superadmin">Pemilik Usaha (Super Admin)</option>
          </select>
        </div>
      </div>
      <button class="btn-submit" id="btnRegister">
        <span id="regBtnText"><i class="fa fa-user-plus me-2"></i>Daftar Sekarang</span>
      </button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(function(){
  // Tab switch
  $('#tabLogin').on('click', function(){
    $(this).addClass('active'); $('#tabRegister').removeClass('active');
    $('#loginForm').show(); $('#registerForm').hide(); $('#alertBox').html('');
  });
  $('#tabRegister').on('click', function(){
    $(this).addClass('active'); $('#tabLogin').removeClass('active');
    $('#registerForm').show(); $('#loginForm').hide(); $('#alertBox').html('');
  });

  // Eye toggle
  $('#eyeLogin').on('click', function(){
    let inp = $('#loginPass'); let type = inp.attr('type') === 'password' ? 'text' : 'password';
    inp.attr('type', type); $(this).find('i').toggleClass('fa-eye fa-eye-slash');
  });
  $('#eyeReg').on('click', function(){
    let inp = $('#regPass'); let type = inp.attr('type') === 'password' ? 'text' : 'password';
    inp.attr('type', type); $(this).find('i').toggleClass('fa-eye fa-eye-slash');
  });

  function showAlert(msg, type='err'){
    let cls = type==='ok' ? 'alert-ok' : 'alert-err';
    let ico = type==='ok' ? 'fa-check-circle' : 'fa-exclamation-circle';
    $('#alertBox').html(`<div class="${cls}"><i class="fa ${ico}"></i>${msg}</div>`);
  }

  // LOGIN
  $('#btnLogin').on('click', function(){
    let email = $('#loginEmail').val().trim(), pass = $('#loginPass').val();
    if(!email || !pass){ showAlert('Email dan password wajib diisi.'); return; }
    $('#loginBtnText').html('<span class="spinner"></span> Memproses...');
    $.ajax({
      url: 'proses_login.php', method: 'POST',
      data: { email, password: pass },
      dataType: 'json',
      success: function(res){
        if(res.status === 'ok'){
          showAlert('Login berhasil! Mengalihkan...', 'ok');
          setTimeout(()=>{ window.location = res.redirect; }, 800);
        } else {
          showAlert(res.message || 'Login gagal.');
          $('#loginBtnText').html('<i class="fa fa-sign-in-alt me-2"></i>Masuk');
        }
      },
      error: function(){ showAlert('Terjadi kesalahan server.'); $('#loginBtnText').html('<i class="fa fa-sign-in-alt me-2"></i>Masuk'); }
    });
  });

  // Enter key login
  $('#loginPass').on('keydown', function(e){ if(e.key==='Enter') $('#btnLogin').trigger('click'); });

  // REGISTER
  $('#btnRegister').on('click', function(){
    let nama = $('#regNama').val().trim(), email = $('#regEmail').val().trim(),
        pass = $('#regPass').val(), pass2 = $('#regPass2').val(), role = $('#regRole').val();
    if(!nama||!email||!pass||!pass2){ showAlert('Semua kolom wajib diisi.'); return; }
    if(pass.length < 8){ showAlert('Password minimal 8 karakter.'); return; }
    if(pass !== pass2){ showAlert('Konfirmasi password tidak sama.'); return; }
    $('#regBtnText').html('<span class="spinner"></span> Mendaftar...');
    $.ajax({
      url: 'proses_register.php', method: 'POST',
      data: { nama, email, password: pass, role },
      dataType: 'json',
      success: function(res){
        if(res.status === 'ok'){
          showAlert('Pendaftaran berhasil! Silakan login.', 'ok');
          $('#regBtnText').html('<i class="fa fa-user-plus me-2"></i>Daftar Sekarang');
          setTimeout(()=>{ $('#tabLogin').trigger('click'); }, 1500);
        } else {
          showAlert(res.message || 'Pendaftaran gagal.');
          $('#regBtnText').html('<i class="fa fa-user-plus me-2"></i>Daftar Sekarang');
        }
      },
      error: function(){ showAlert('Terjadi kesalahan server.'); $('#regBtnText').html('<i class="fa fa-user-plus me-2"></i>Daftar Sekarang'); }
    });
  });

  <?php if($error === 'logout'): ?>showAlert('Kamu telah keluar. Sampai jumpa!','ok');<?php endif; ?>
  <?php if($error === 'unauthorized'): ?>showAlert('Akses ditolak. Login sebagai Super Admin.');<?php endif; ?>
});
</script>
</body>
</html>
