<?php
/**
 * Action: Update Biaya Pendaftaran oleh Admin
 */
session_start();

if (!isset($_SESSION['admin_himatik'])) {
    die("Akses ditolak. Silakan login sebagai admin.");
}

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $biaya   = (int) ($_POST['biaya_pendaftaran'] ?? 0);
    $catatan = trim($_POST['catatan_pembayaran'] ?? '');

    if ($biaya < 0) $biaya = 0;

    try {
        // Menggunakan ON DUPLICATE KEY UPDATE nilai = VALUES(nilai) agar tidak terjadi duplikasi placeholder pada PDO
        $stmt1 = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES ('biaya_pendaftaran', :biaya, 'Biaya pendaftaran dalam Rupiah (0 = Gratis)') ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
        $stmt1->execute([':biaya' => (string) $biaya]);

        $stmt2 = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES ('catatan_pembayaran', :catatan, 'Keterangan tambahan biaya') ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
        $stmt2->execute([':catatan' => $catatan]);

        header('Location: index.php?biaya_updated=1');
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?error=' . urlencode('Gagal menyimpan biaya: ' . $e->getMessage()));
        exit;
    }
}

header('Location: index.php');
exit;
