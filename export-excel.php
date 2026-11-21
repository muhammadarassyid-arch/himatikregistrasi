<?php
/**
 * Export Data Pendaftar HIMATIK ke File CSV / Excel
 * Kompatibel langsung dengan Microsoft Excel, Google Sheets, LibreOffice Calc
 */
session_start();

if (!isset($_SESSION['admin_himatik'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

require_once __DIR__ . '/../db.php';

$filter_divisi   = $_GET['divisi'] ?? '';
$filter_status   = $_GET['status'] ?? '';
$filter_angkatan = $_GET['angkatan'] ?? '';
$search_query    = $_GET['q'] ?? '';

$sql = "SELECT * FROM pendaftar WHERE 1=1";
$params = [];

if (!empty($filter_divisi)) {
    $sql .= " AND (divisi_utama = :divisi OR divisi_cadangan = :divisi)";
    $params[':divisi'] = $filter_divisi;
}

if (!empty($filter_status)) {
    $sql .= " AND status_seleksi = :status";
    $params[':status'] = $filter_status;
}

if (!empty($filter_angkatan)) {
    $sql .= " AND angkatan = :angkatan";
    $params[':angkatan'] = $filter_angkatan;
}

if (!empty($search_query)) {
    $sql .= " AND (nama_lengkap LIKE :q OR nim LIKE :q OR nomor_registrasi LIKE :q)";
    $params[':q'] = "%{$search_query}%";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Atur Header agar browser men-download file CSV
$filename = 'rekap_pendaftar_himatik_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Buka output stream
$output = fopen('php://output', 'w');

// Masukkan UTF-8 BOM agar Excel membaca karakter dengan baik
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header Kolom CSV
fputcsv($output, [
    'No',
    'Nomor Registrasi',
    'NIM',
    'Nama Lengkap',
    'Email',
    'No. WhatsApp',
    'Angkatan',
    'Kelas',
    'Divisi Pilihan 1',
    'Divisi Pilihan 2',
    'Status Seleksi',
    'Tahap Seleksi',
    'Jadwal Interview',
    'Motivasi Singkat',
    'Link Portofolio',
    'Catatan Panitia',
    'Tanggal Pendaftaran'
]);

$no = 1;
foreach ($data as $row) {
    fputcsv($output, [
        $no++,
        $row['nomor_registrasi'],
        "'" . $row['nim'], // Diberi apostrof agar Excel tidak mengubah NIM menjadi notasi scientific
        $row['nama_lengkap'],
        $row['email'],
        "'" . $row['no_whatsapp'],
        $row['angkatan'],
        $row['kelas'] ?? '-',
        $row['divisi_utama'],
        $row['divisi_cadangan'],
        $row['status_seleksi'],
        'Step ' . $row['tahap_step'],
        $row['jadwal_interview'] ?? '-',
        $row['motivasi'],
        $row['link_portofolio'] ?? '-',
        $row['catatan_panitia'] ?? '-',
        $row['created_at']
    ]);
}

fclose($output);
exit;
