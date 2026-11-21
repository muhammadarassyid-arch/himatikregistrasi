<?php
/**
 * API Endpoint: Lacak Status Seleksi Mahasiswa Berdasarkan NIM
 * Status Sederhana: Diterima / Tidak Diterima / Menunggu Pengumuman
 * Method: GET / POST
 */

define('API_REQUEST', true);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

$nim = trim($_GET['nim'] ?? $_POST['nim'] ?? '');

if (empty($nim)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Harap masukkan NIM untuk melakukan pencarian.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE nim = ? LIMIT 1");
    $stmt->execute([$nim]);
    $pendaftar = $stmt->fetch();

    if (!$pendaftar) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "Data pendaftaran dengan NIM {$nim} tidak ditemukan. Pastikan Anda sudah mengisi formulir pendaftaran."
        ]);
        exit;
    }

    $rawStatus = trim($pendaftar['status_seleksi'] ?? '');
    
    // Simplifikasi Status: Cek "Tidak" TERLEBIH DAHULU karena "Tidak Diterima" mengandung kata "Diterima"
    if (stripos($rawStatus, 'Tidak') !== false || stripos($rawStatus, 'Gagal') !== false) {
        $statusLabel = 'Tidak Diterima';
        $badgeClass = 'bg-rose-500/20 border-rose-500/40 text-rose-300 font-bold';
        $statusKey = 'tidak';
    } elseif (stripos($rawStatus, 'Diterima') !== false || stripos($rawStatus, 'Lolos') !== false) {
        $statusLabel = 'Diterima';
        $badgeClass = 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300 font-bold';
        $statusKey = 'diterima';
    } else {
        $statusLabel = 'Menunggu Pengumuman';
        $badgeClass = 'bg-cyan-500/15 border-cyan-500/30 text-cyan-300 font-semibold';
        $statusKey = 'menunggu';
    }

    echo json_encode([
        'success' => true,
        'data'    => [
            'nomor_registrasi' => $pendaftar['nomor_registrasi'],
            'nama'             => $pendaftar['nama_lengkap'],
            'nim'              => $pendaftar['nim'],
            'divisi1'          => $pendaftar['divisi_utama'],
            'divisi2'          => $pendaftar['divisi_cadangan'],
            'tgl'              => date('d F Y', strtotime($pendaftar['created_at'])),
            'status'           => $statusLabel,
            'status_key'       => $statusKey,
            'badgeClass'       => $badgeClass,
            'jadwal'           => $pendaftar['jadwal_interview'] ?? 'Belum Ada',
            'catatan'          => $pendaftar['catatan_panitia'] ?? 'Silakan pantau status pendaftaran secara berkala.'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem database: ' . $e->getMessage()
    ]);
}
