<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

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

    echo json_encode([
        'status' => 'ok',
        'redirect' => ($user['role'] === 'superadmin')
            ? '/api/dashboard_admin.php'
            : '/api/dashboard.php'
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}