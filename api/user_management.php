<?php
require_once 'db.php';
requireSuperAdmin();
$db = getDB();

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'add') {
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = $_POST['role'] ?? 'user';
        if (!$nama || !$email || !$pass) { echo json_encode(['status'=>'error','message'=>'Kolom wajib diisi.']); exit; }
        $cek = $db->prepare("SELECT id FROM users WHERE email=?"); $cek->execute([$email]);
        if ($cek->fetch()) { echo json_encode(['status'=>'error','message'=>'Email sudah terdaftar.']); exit; }
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO users (nama,email,password,role) VALUES(?,?,?,?)")->execute([$nama,$email,$hash,$role]);
        echo json_encode(['status'=>'ok','message'=>'User berhasil ditambahkan.']); exit;
    }

    if ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $row = $db->prepare("SELECT status FROM users WHERE id=?"); $row->execute([$id]); $u=$row->fetch();
        $new = $u['status']==='aktif' ? 'nonaktif' : 'aktif';
        $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$id]);
        echo json_encode(['status'=>'ok','new_status'=>$new]); exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === $_SESSION['user_id']) { echo json_encode(['status'=>'error','message'=>'Tidak bisa hapus diri sendiri.']); exit; }
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        echo json_encode(['status'=>'ok']); exit;
    }
    exit;
}

$adminNama = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Management – ERUM</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <style>
    :root { --primary:#6366f1; --accent:#10b981; --main-bg:#f0f2ff; --card-bg:#fff; --text:#1e1b4b; --text-muted:#64748b; --border:#e2e8f0; --sidebar-bg:#12122a; --sidebar-w:240px; }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--main-bg); color:var(--text); min-height:100vh; display:flex; }
    .sidebar { width:var(--sidebar-w); min-height:100vh; background:var(--sidebar-bg); position:fixed; top:0; left:0; z-index:100; padding:1.2rem 0; }
    .sidebar-logo { padding:.5rem 1.2rem 1rem; border-bottom:1px solid rgba(255,255,255,.06); margin-bottom:.5rem; }
    .brand { font-weight:800; font-size:1.4rem; letter-spacing:-0.5px; }
    .brand .e { color:var(--primary); } .brand .rum { color:#fff; }
    .nav-label { font-size:.62rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#7c7ca0; padding:.8rem 1rem .3rem; }
    .nav-item { display:flex; align-items:center; gap:.7rem; padding:.5rem 1rem; color:#a0a0c8; text-decoration:none; font-size:.81rem; font-weight:500; transition:.2s; }
    .nav-item:hover { background:rgba(99,102,241,.12); color:#fff; }
    .nav-item.active { background:rgba(99,102,241,.2); color:var(--primary); font-weight:600; }
    .main-wrap { margin-left:var(--sidebar-w); flex:1; }
    .topbar { height:62px; background:var(--card-bg); border-bottom:1px solid var(--border); display:flex; align-items:center; padding:0 1.5rem; gap:1rem; position:sticky; top:0; z-index:50; }
    .topbar-title { flex:1; font-weight:700; font-size:.95rem; }
    .avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--primary),#a855f7); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:.8rem; }
    .main-content { padding:1.5rem; }
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; }
    .page-header h4 { font-size:1.1rem; font-weight:800; }
    .btn-add { background:var(--primary); color:#fff; border:none; border-radius:10px; padding:.55rem 1.1rem; font-family:inherit; font-size:.83rem; font-weight:600; cursor:pointer; transition:.2s; }
    .btn-add:hover { opacity:.9; }
    .table-card { background:var(--card-bg); border-radius:14px; border:1px solid var(--border); overflow:hidden; }
    .search-bar { padding:1rem 1.3rem; border-bottom:1px solid var(--border); display:flex; gap:.7rem; }
    .search-input { flex:1; border:1px solid var(--border); border-radius:8px; padding:.5rem .9rem; font-family:inherit; font-size:.83rem; outline:none; }
    .search-input:focus { border-color:var(--primary); }
    table { width:100%; border-collapse:collapse; }
    thead th { padding:.7rem 1.3rem; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); background:#f8f9ff; border-bottom:1px solid var(--border); }
    tbody td { padding:.75rem 1.3rem; font-size:.82rem; border-bottom:1px solid var(--border); }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover { background:#f8f9ff; }
    .badge-role { padding:.22rem .6rem; border-radius:6px; font-size:.7rem; font-weight:600; }
    .badge-sa { background:rgba(99,102,241,.15); color:var(--primary); }
    .badge-user { background:rgba(100,116,139,.12); color:#64748b; }
    .badge-aktif { background:rgba(16,185,129,.15); color:#10b981; }
    .badge-nonaktif { background:rgba(239,68,68,.12); color:#ef4444; }
    .btn-sm-act { border:none; border-radius:7px; padding:.25rem .55rem; font-size:.75rem; cursor:pointer; font-family:inherit; font-weight:600; transition:.2s; }
    .btn-toggle { background:rgba(16,185,129,.12); color:#10b981; }
    .btn-toggle:hover { background:#10b981; color:#fff; }
    .btn-del { background:rgba(239,68,68,.12); color:#ef4444; }
    .btn-del:hover { background:#ef4444; color:#fff; }
    /* Modal */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:200; align-items:center; justify-content:center; }
    .modal-overlay.show { display:flex; }
    .modal-box { background:#fff; border-radius:18px; padding:2rem; width:100%; max-width:420px; }
    .modal-box h5 { font-size:1rem; font-weight:700; margin-bottom:1.2rem; }
    .form-group { margin-bottom:.9rem; }
    .form-label { font-size:.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:.35rem; }
    .form-input { width:100%; border:1px solid var(--border); border-radius:8px; padding:.6rem .9rem; font-family:inherit; font-size:.85rem; outline:none; }
    .form-input:focus { border-color:var(--primary); }
    select.form-input { cursor:pointer; }
    .modal-footer { display:flex; gap:.7rem; margin-top:1.2rem; }
    .btn-cancel { flex:1; padding:.6rem; background:#f1f5f9; border:none; border-radius:10px; font-family:inherit; font-weight:600; font-size:.85rem; cursor:pointer; }
    .btn-save { flex:1; padding:.6rem; background:var(--primary); color:#fff; border:none; border-radius:10px; font-family:inherit; font-weight:600; font-size:.85rem; cursor:pointer; }
    .alert-msg { padding:.6rem .9rem; border-radius:8px; font-size:.8rem; margin-bottom:.8rem; display:none; }
    .alert-ok { background:rgba(16,185,129,.12); color:#10b981; border:1px solid rgba(16,185,129,.3); }
    .alert-err { background:rgba(239,68,68,.1); color:#ef4444; border:1px solid rgba(239,68,68,.25); }
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><div class="brand"><span class="e">E</span><span class="rum">RUM</span></div></div>
  <div class="nav-label">Utama</div>
  <a class="nav-item" href="dashboard_admin.php"><i class="fa fa-house fa-fw"></i>Dashboard</a>
  <div class="nav-label">Sistem & Admin</div>
  <a class="nav-item active" href="user_management.php"><i class="fa fa-user-shield fa-fw"></i>User Management</a>
  <a class="nav-item" href="konfigurasi_hari.php"><i class="fa fa-calendar-days fa-fw"></i>Konfigurasi Hari</a>
  <div class="nav-label">Pengaturan</div>
  <a class="nav-item" href="informasi_perusahaan.php"><i class="fa fa-circle-info fa-fw"></i>Informasi Perusahaan</a>
  <a class="nav-item" href="backup.php"><i class="fa fa-database fa-fw"></i>Backup & Restore</a>
  <a class="nav-item" href="logout.php"><i class="fa fa-right-from-bracket fa-fw"></i>Keluar</a>
</aside>
<div class="main-wrap">
  <header class="topbar">
    <div class="topbar-title"><i class="fa fa-user-shield me-2 text-primary"></i>User Management</div>
    <div class="avatar"><?= strtoupper(substr($adminNama,0,2)) ?></div>
    <span style="font-size:.82rem;font-weight:600"><?= htmlspecialchars($adminNama) ?></span>
  </header>
  <main class="main-content">
    <div class="page-header">
      <h4>Daftar User</h4>
      <button class="btn-add" id="btnAddUser"><i class="fa fa-plus me-1"></i>Tambah User</button>
    </div>

    <div class="table-card">
      <div class="search-bar">
        <input type="text" class="search-input" id="searchUser" placeholder="Cari nama atau email..."/>
      </div>
      <table>
        <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead>
        <tbody id="userTbl">
          <?php
          $users = $db->query("SELECT id,nama,email,role,status,created_at FROM users ORDER BY created_at DESC")->fetchAll();
          foreach($users as $i=>$u):
          $rClass = $u['role']==='superadmin' ? 'badge-sa' : 'badge-user';
          $rLabel = $u['role']==='superadmin' ? 'Super Admin' : 'Karyawan';
          $sClass = $u['status']==='aktif' ? 'badge-aktif' : 'badge-nonaktif';
          ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
            <td style="color:#64748b"><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge-role <?= $rClass ?>"><?= $rLabel ?></span></td>
            <td><span class="badge-role <?= $sClass ?>" id="status-<?= $u['id'] ?>"><?= ucfirst($u['status']) ?></span></td>
            <td style="color:#64748b"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
            <td>
              <button class="btn-sm-act btn-toggle" onclick="toggleStatus(<?= $u['id'] ?>)"><i class="fa fa-toggle-on"></i></button>
              <button class="btn-sm-act btn-del" onclick="deleteUser(<?= $u['id'] ?>, '<?= addslashes($u['nama']) ?>')"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- ADD USER MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <h5><i class="fa fa-user-plus me-2 text-primary"></i>Tambah User Baru</h5>
    <div class="alert-msg" id="modalAlert"></div>
    <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" class="form-input" id="mNama" placeholder="Nama lengkap"/></div>
    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" id="mEmail" placeholder="email@contoh.com"/></div>
    <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-input" id="mPass" placeholder="Min. 8 karakter"/></div>
    <div class="form-group"><label class="form-label">Role</label>
      <select class="form-input" id="mRole">
        <option value="user">Karyawan</option>
        <option value="superadmin">Super Admin</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" id="btnCancelModal">Batal</button>
      <button class="btn-save" id="btnSaveUser">Simpan</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(function(){
  $('#btnAddUser').on('click',function(){ $('#addModal').addClass('show'); });
  $('#btnCancelModal').on('click',function(){ $('#addModal').removeClass('show'); $('#modalAlert').hide(); });
  $('#addModal').on('click',function(e){ if($(e.target).is('#addModal')) $(this).removeClass('show'); });

  // Search filter
  $('#searchUser').on('keyup',function(){
    var q=$(this).val().toLowerCase();
    $('#userTbl tr').each(function(){
      var txt=$(this).text().toLowerCase();
      $(this).toggle(txt.includes(q));
    });
  });

  // Save user
  $('#btnSaveUser').on('click',function(){
    var data={action:'add',nama:$('#mNama').val(),email:$('#mEmail').val(),password:$('#mPass').val(),role:$('#mRole').val()};
    $.ajax({ url:'user_management.php', method:'POST', data:data, dataType:'json',
      success:function(res){
        var cls=res.status==='ok'?'alert-ok':'alert-err';
        $('#modalAlert').attr('class','alert-msg '+cls).text(res.message).show();
        if(res.status==='ok') setTimeout(()=>location.reload(),1200);
      }
    });
  });
});

function toggleStatus(id){
  $.ajax({ url:'user_management.php', method:'POST', data:{action:'toggle_status',id:id}, dataType:'json',
    success:function(res){
      if(res.status==='ok'){
        var el=$('#status-'+id);
        if(res.new_status==='aktif'){ el.text('Aktif').attr('class','badge-role badge-aktif'); }
        else { el.text('Nonaktif').attr('class','badge-role badge-nonaktif'); }
      }
    }
  });
}

function deleteUser(id,nama){
  if(!confirm('Hapus user "'+nama+'"? Tindakan ini tidak bisa dibatalkan.')) return;
  $.ajax({ url:'user_management.php', method:'POST', data:{action:'delete',id:id}, dataType:'json',
    success:function(res){ if(res.status==='ok') location.reload(); else alert(res.message); }
  });
}
</script>
</body>
</html>
