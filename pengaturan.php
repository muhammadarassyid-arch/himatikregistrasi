<?php
/**
 * API: Pengaturan Sistem (Biaya Pendaftaran)
 * Method: GET
 */

define('API_REQUEST', true);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

try {
    $stmt = $pdo->query("SELECT kunci, nilai, keterangan FROM pengaturan");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['kunci']] = $row['nilai'];
    }

    $biaya = isset($settings['biaya_pendaftaran']) ? (int) $settings['biaya_pendaftaran'] : 0;
    $catatan = $settings['catatan_pembayaran'] ?? 'Gratis tanpa biaya';
    $status_pendaftaran = strtolower(trim($settings['status_pendaftaran'] ?? 'buka'));
    $pesan_penutupan = $settings['pesan_penutupan'] ?? 'Pendaftaran calon anggota HIMATIK periode ini telah resmi ditutup.';
    $is_buka = ($status_pendaftaran !== 'tutup');

    $is_gratis = ($biaya <= 0);
    $biaya_formatted = $is_gratis ? 'Rp 0' : 'Rp ' . number_format($biaya, 0, ',', '.');
    $keterangan_biaya = $is_gratis ? 'Gratis Tanpa Biaya' : 'Biaya Pendaftaran Mahasiswa';

    echo json_encode([
        'success'            => true,
        'biaya'              => $biaya,
        'biaya_formatted'    => $biaya_formatted,
        'keterangan_biaya'   => $keterangan_biaya,
        'is_gratis'          => $is_gratis,
        'catatan'            => $catatan,
        'status_pendaftaran' => $status_pendaftaran,
        'is_buka'            => $is_buka,
        'pesan_penutupan'    => $pesan_penutupan
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success'          => false,
        'biaya'            => 0,
        'biaya_formatted'  => 'Rp 0',
        'keterangan_biaya' => 'Gratis Tanpa Biaya',
        'is_gratis'        => true,
        'catatan'          => 'Gratis tanpa biaya'
    ]);
}
