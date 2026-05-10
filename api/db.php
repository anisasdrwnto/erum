<?php
// config/db.php - Konfigurasi koneksi database TiDB/MySQL
define('DB_HOST', 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com');
define('DB_PORT', '4000');       // Default TiDB port (MySQL: 3306)
define('DB_NAME', 'erum_db');
define('DB_USER', 'm6CeAMKqTKdvTTf.root');
define('DB_PASS', 'oV4bfsGvFPMws4om');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Session helper
function sessionStart(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    sessionStart();
    return isset($_SESSION['user_id']);
}

function isSuperAdmin(): bool {
    sessionStart();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
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
