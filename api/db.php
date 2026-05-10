<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

// DATABASE CONFIG
$host   = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$user   = 'm6CeAMKqTKdvTTf.root';
$pass   = 'oV4bfsGvFPMws4om';
$dbname = 'erum_db';
$port   = 4000;

try {

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $connection = new PDO($dsn, $user, $pass, [

        // SSL wajib untuk TiDB Cloud
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,

        // path CA bawaan Linux/Vercel
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 8,
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    die(json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . $e->getMessage()
    ]));
}


// =========================
// SESSION HELPER
// =========================

function isLoggedIn(): bool {

    return isset($_SESSION['user_id']);
}

function isSuperAdmin(): bool {

    return isset($_SESSION['role']) &&
           $_SESSION['role'] === 'superadmin';
}

function requireLogin(): void {

    if (!isLoggedIn()) {

        header('Location: login.php');
        exit;
    }
}

function requireSuperAdmin(): void {

    requireLogin();

    if (!isSuperAdmin()) {

        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}
?>