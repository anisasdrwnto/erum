<?php
session_start();
require_once 'db.php';

// kalau sudah login langsung arahkan
if (isLoggedIn()) {
    header('Location: ' . (isSuperAdmin() ? 'api/dashboard_admin.php' : 'api/dashboard.php'));
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <style>
    body { background:#0f0f1a; color:white; font-family:Arial; }
    .box { max-width:420px; margin:80px auto; background:#1a1a2e; padding:30px; border-radius:15px; }
    .form-control { margin-bottom:12px; }
    button { width:100%; padding:10px; }
  </style>
</head>

<body>

<div class="box">
  <h3 class="text-center">LOGIN ERUM</h3>

  <div id="alert"></div>

  <input type="email" id="email" class="form-control" placeholder="Email">
  <input type="password" id="password" class="form-control" placeholder="Password">

  <button id="loginBtn" class="btn btn-primary">
    Login
  </button>
</div>

<script>
$('#loginBtn').click(function(){

    let email = $('#email').val();
    let password = $('#password').val();

    if(!email || !password){
        $('#alert').html('<div class="alert alert-danger">Isi semua field</div>');
        return;
    }

    $.ajax({
        url: '/proses_login.php',
        method: 'POST',
        dataType: 'json',
        data: { email, password },
        success: function(res){

            if(res.status === 'ok'){

                $('#alert').html('<div class="alert alert-success">Login sukses</div>');

                setTimeout(() => {
                    window.location.href = res.redirect;
                }, 500);

            } else {
                $('#alert').html('<div class="alert alert-danger">'+res.message+'</div>');
            }
        },
        error: function(){
            $('#alert').html('<div class="alert alert-danger">Server error</div>');
        }
    });
});
</script>

</body>
</html>