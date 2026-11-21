<?php
/**
 * Update Status Pendaftaran (Buka / Tutup) oleh Admin
 * Mengatur apakah formulir pendaftaran di landing page dapat diakses mahasiswa
 */
session_start();

if (!isset($_SESSION['admin_himatik'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../db.php';

$status = strtolower(trim($_POST['status_pendaftaran'] ?? 'buka'));
if ($status !== 'buka' && $status !== 'tutup') {
    $status = 'buka';
}

$pesan = trim($_POST['pesan_penutupan'] ?? '');
if (empty($pesan)) {
    $pesan = ($status === 'tutup') 
        ? 'Pendaftaran calon anggota HIMATIK periode ini telah resmi ditutup. Terima kasih atas antusiasme Anda!' 
        : 'Pendaftaran calon anggota HIMATIK periode ini resmi dibuka.';
}

try {
    $stmt1 = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES ('status_pendaftaran', :status, 'Status pendaftaran: buka atau tutup') ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
    $stmt1->execute([':status' => $status]);

    $stmt2 = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES ('pesan_penutupan', :pesan, 'Pesan yang tampil saat pendaftaran ditutup') ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
    $stmt2->execute([':pesan' => $pesan]);

    header('Location: index.php?status_pendaftaran_updated=' . urlencode($status));
    exit;
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode('Gagal mengubah status pendaftaran: ' . $e->getMessage()));
    exit;
}
