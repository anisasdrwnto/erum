<?php
session_start();

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode tidak valid.'
    ]);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email dan password wajib diisi.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Format email tidak valid.'
    ]);
    exit;
}

try {

    // ❌ INI YANG SALAH: getDB()
    // ✔️ GANTI KE $connection dari db.php

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
            'message' => 'Email tidak terdaftar.'
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

    $valid = password_verify($password, $user['password']);

    // fallback kalau password masih plaintext (legacy)
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

    // SESSION
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama']    = $user['nama'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

    $redirect = ($user['role'] === 'superadmin')
        ? '/api/dashboard_admin.php'
        : '/dashboard.php';

    echo json_encode([
        'status' => 'ok',
        'redirect' => $redirect,
        'role' => $user['role']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Kesalahan server: ' . $e->getMessage()
    ]);
}