<?php
// proses_register.php
require_once 'db.php';
header('Content-Type: application/json');
sessionStart();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak valid.']);
    exit;
}

$nama     = trim($_POST['nama']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';
$role     = $_POST['role']          ?? 'user';

// Validasi
if (!$nama || !$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password minimal 8 karakter.']);
    exit;
}
if (!in_array($role, ['user', 'superadmin'])) {
    $role = 'user';
}

try {
    $db = getDB();

    // Cek email duplikat
    $cek = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar. Silakan login.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $ins  = $db->prepare("INSERT INTO users (nama, email, password, role, status) VALUES (?, ?, ?, ?, 'aktif')");
    $ins->execute([$nama, $email, $hash, $role]);

    echo json_encode(['status' => 'ok', 'message' => 'Pendaftaran berhasil!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Kesalahan server: ' . $e->getMessage()]);
}
