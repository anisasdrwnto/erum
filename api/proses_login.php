<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/db.php';


// =========================
// AMBIL INPUT PALING AMAN
// =========================

// coba dari JSON
$json = json_decode(file_get_contents("php://input"), true);

// fallback FORM POST
$email =
    trim(
        $json['email']
        ?? $_POST['email']
        ?? ''
    );

$password =
    trim(
        $json['password']
        ?? $_POST['password']
        ?? ''
    );


// DEBUG
// echo json_encode([
//     'json' => $json,
//     '_POST' => $_POST,
//     'email' => $email,
//     'password' => $password
// ]);
// exit;


if (empty($email) || empty($password)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Email dan password wajib diisi.'
    ]);

    exit;
}

try {

    global $connection;

    $stmt = $connection->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Email tidak ditemukan.'
        ]);

        exit;
    }

    if ($user['status'] !== 'aktif') {

        echo json_encode([
            'status' => 'error',
            'message' => 'Akun tidak aktif.'
        ]);

        exit;
    }

    $valid = false;

    // password hash
    if (password_verify($password, $user['password'])) {
        $valid = true;
    }

    // fallback plaintext
    if ($password === $user['password']) {
        $valid = true;
    }

    if (!$valid) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Password salah.'
        ]);

        exit;
    }

    // SESSION LOGIN
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'status' => 'ok',
        'redirect' =>
            ($user['role'] === 'superadmin')
            ? '/dashboard_admin.php'
            : '/dashboard.php'
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}