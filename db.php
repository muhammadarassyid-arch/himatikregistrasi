<?php
/**
 * Konfigurasi Koneksi Database MySQL (PDO)
 * Sistem Pendaftaran Online Anggota HIMATIK
 */

// Konfigurasi Kredensial MySQL (Default XAMPP / Laragon)
$db_host = 'localhost';
$db_name = 'himatik_db';
$db_user = 'root';
$db_pass = ''; // Kosongkan jika default XAMPP, atau sesuaikan dengan password MySQL Anda

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    if (defined('API_REQUEST')) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal terhubung ke database MySQL. Pastikan MySQL sudah aktif dan database `himatik_db` sudah diimpor.',
            'error'   => $e->getMessage()
        ]);
        exit;
    } else {
        die("Koneksi Database Gagal: " . $e->getMessage());
    }
}
