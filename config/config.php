<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Outdoor Rental');

// Deteksi otomatis BASE_URL (mendukung Localhost, Port Forwarding, DevTunnels, Ngrok, Cloudflare Tunnel)
if (!defined('BASE_URL')) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    $protocol = $isHttps ? "https://" : "http://";

    // Deteksi host (prioritaskan proxy header jika menggunakan tunneling)
    $httpHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($httpHost, ',') !== false) {
        $httpHost = trim(explode(',', $httpHost)[0]);
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $subDir = preg_replace('#/(admin|pages|config|includes)$#i', '', $scriptDir);
    $subDir = ($subDir === '/' || $subDir === '.' || $subDir === '') ? '' : rtrim($subDir, '/');

    define('BASE_URL', $protocol . $httpHost . $subDir);
}

$host = "sql106.infinityfree.com";
$user = "if0_42804887";
$pass = "U3AhtjnLt1UXfRD";
$db   = "if0_42804887_db_outdoor_rental";

// $host = "localhost";
// $user = "root";
// $pass = "root";
// $db   = "outdoor_rental";

try {
    $conn = mysqli_connect($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    // Fallback jika MySQL dikonfigurasi tanpa password
    try {
        $conn = mysqli_connect($host, $user, "", $db);
        $pass = "";
    } catch (mysqli_sql_exception $e2) {
        die("Koneksi database gagal: " . $e2->getMessage());
    }
}

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
