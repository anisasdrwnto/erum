<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Email dan password wajib diisi.'
    ]);

    exit;
}

try {

    global $connection;

    $stmt = $connection->prepare("
        SELECT id, nama, email, password, role, status
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if (!$user) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Email tidak ditemukan.'
        ]);

        exit;
    }

    $valid = password_verify($password, $user['password']);

    // fallback plaintext
    if (!$valid && $password === $user['password']) {
        $valid = true;
    }

    if (!$valid) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Password salah.'
        ]);

        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama']    = $user['nama'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

    $redirect = ($user['role'] === 'superadmin')
    ? '/api/dashboard_admin.php'
    : '/api/dashboard.php';

    echo json_encode([
        'status' => 'ok',
        'redirect' => $redirect
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}