<?php
/**
 * Hapus Data Pendaftar (Admin Only)
 */
session_start();

if (!isset($_SESSION['admin_himatik'])) {
    die("Akses ditolak.");
}

require_once __DIR__ . '/../db.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // Ambil info nama file foto & ktm untuk dihapus dari storage
    $stmt = $pdo->prepare("SELECT file_foto, file_ktm FROM pendaftar WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        if (!empty($row['file_foto']) && file_exists(__DIR__ . '/../uploads/foto/' . $row['file_foto'])) {
            unlink(__DIR__ . '/../uploads/foto/' . $row['file_foto']);
        }
        if (!empty($row['file_ktm']) && file_exists(__DIR__ . '/../uploads/ktm/' . $row['file_ktm'])) {
            unlink(__DIR__ . '/../uploads/ktm/' . $row['file_ktm']);
        }

        $stmtDel = $pdo->prepare("DELETE FROM pendaftar WHERE id = ?");
        $stmtDel->execute([$id]);
    }
}

header('Location: index.php?deleted=1');
exit;
