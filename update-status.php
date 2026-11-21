<?php
/**
 * Action: Update Status Seleksi Pendaftar (Admin Only)
 * Status: Diterima / Tidak Diterima / Menunggu Pengumuman
 */
session_start();

if (!isset($_SESSION['admin_himatik'])) {
    die("Akses ditolak.");
}

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = (int) ($_POST['id'] ?? 0);
    $status_seleksi   = trim($_POST['status_seleksi'] ?? 'Menunggu Pengumuman');
    $catatan_panitia  = trim($_POST['catatan_panitia'] ?? '');

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE pendaftar SET 
                                status_seleksi = :status, 
                                catatan_panitia = :catatan 
                               WHERE id = :id");
        $stmt->execute([
            ':status'  => $status_seleksi,
            ':catatan' => $catatan_panitia,
            ':id'      => $id
        ]);
    }
}

header('Location: index.php?updated=1');
exit;
