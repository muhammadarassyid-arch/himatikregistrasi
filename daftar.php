<?php
/**
 * API Endpoint: Registrasi Calon Anggota Baru HIMATIK
 * Method: POST (multipart/form-data)
 */

define('API_REQUEST', true);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Gunakan POST.']);
    exit;
}

require_once __DIR__ . '/../db.php';

$nama_lengkap    = trim($_POST['nama_lengkap'] ?? '');
$nim             = trim($_POST['nim'] ?? '');
$email           = trim($_POST['email'] ?? '');
$no_whatsapp     = trim($_POST['no_whatsapp'] ?? '');
$angkatan        = trim($_POST['angkatan'] ?? '');
$kelas           = trim($_POST['kelas'] ?? '');
$divisi_utama    = trim($_POST['divisi_utama'] ?? '');
$divisi_cadangan = trim($_POST['divisi_cadangan'] ?? '');
$motivasi        = trim($_POST['motivasi'] ?? '');
$link_portofolio = trim($_POST['link_portofolio'] ?? '');

if (empty($nama_lengkap) || empty($nim) || empty($email) || empty($no_whatsapp) || empty($divisi_utama) || empty($motivasi)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Harap lengkapi semua bidang yang bertanda bintang (*).'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

try {
    // 0. Cek apakah pendaftaran masih dibuka
    $stmtStatus = $pdo->prepare("SELECT nilai FROM pengaturan WHERE kunci = 'status_pendaftaran' LIMIT 1");
    $stmtStatus->execute();
    $statusPendaftaran = strtolower(trim($stmtStatus->fetchColumn() ?: 'buka'));

    if ($statusPendaftaran === 'tutup') {
        $stmtMsg = $pdo->prepare("SELECT nilai FROM pengaturan WHERE kunci = 'pesan_penutupan' LIMIT 1");
        $stmtMsg->execute();
        $pesanTutup = $stmtMsg->fetchColumn() ?: 'Mohon maaf, pendaftaran calon anggota HIMATIK periode ini telah resmi ditutup oleh admin.';
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $pesanTutup
        ]);
        exit;
    }

    // 1. Cek duplikasi NIM
    $stmtCheck = $pdo->prepare("SELECT id, nomor_registrasi FROM pendaftar WHERE nim = ?");
    $stmtCheck->execute([$nim]);
    $existing = $stmtCheck->fetch();

    if ($existing) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => "NIM {$nim} sudah terdaftar sebelumnya dengan No. Registrasi {$existing['nomor_registrasi']}. Silakan cek status di menu Lacak Status NIM."
        ]);
        exit;
    }

    // 2. Folder Upload
    $uploadDirFoto = __DIR__ . '/../uploads/foto/';
    $uploadDirKtm  = __DIR__ . '/../uploads/ktm/';

    if (!is_dir($uploadDirFoto)) mkdir($uploadDirFoto, 0755, true);
    if (!is_dir($uploadDirKtm))  mkdir($uploadDirKtm, 0755, true);

    $fileFotoName = null;
    $fileKtmName  = null;

    // Upload Pas Foto
    if (isset($_FILES['file_foto']) && $_FILES['file_foto']['error'] === UPLOAD_ERR_OK) {
        $fotoTmp  = $_FILES['file_foto']['tmp_name'];
        $fotoExt  = strtolower(pathinfo($_FILES['file_foto']['name'], PATHINFO_EXTENSION));
        $allowedFoto = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fotoExt, $allowedFoto)) {
            $fileFotoName = 'foto_' . preg_replace('/[^a-zA-Z0-9]/', '', $nim) . '_' . time() . '.' . $fotoExt;
            move_uploaded_file($fotoTmp, $uploadDirFoto . $fileFotoName);
        }
    }

    // Upload KTM / KRS
    if (isset($_FILES['file_ktm']) && $_FILES['file_ktm']['error'] === UPLOAD_ERR_OK) {
        $ktmTmp  = $_FILES['file_ktm']['tmp_name'];
        $ktmExt  = strtolower(pathinfo($_FILES['file_ktm']['name'], PATHINFO_EXTENSION));
        $allowedKtm = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        if (in_array($ktmExt, $allowedKtm)) {
            $fileKtmName = 'ktm_' . preg_replace('/[^a-zA-Z0-9]/', '', $nim) . '_' . time() . '.' . $ktmExt;
            move_uploaded_file($ktmTmp, $uploadDirKtm . $fileKtmName);
        }
    }

    // 3. Generate Kode Registrasi Mengikuti Tahun Berjalan
    $nomor_registrasi = 'HIMATIK-' . date('Y') . '-' . rand(1000, 9999);

    // 4. Simpan ke MySQL
    $sql = "INSERT INTO pendaftar (
                nomor_registrasi, nama_lengkap, nim, email, no_whatsapp,
                angkatan, kelas, divisi_utama, divisi_cadangan, motivasi,
                link_portofolio, file_foto, file_ktm, status_seleksi, tahap_step, catatan_panitia
            ) VALUES (
                :nomor_registrasi, :nama_lengkap, :nim, :email, :no_whatsapp,
                :angkatan, :kelas, :divisi_utama, :divisi_cadangan, :motivasi,
                :link_portofolio, :file_foto, :file_ktm, 'Tahap Verifikasi Berkas', 2, 'Data berhasil disimpan. Berkas sedang dalam antrean verifikasi panitia.'
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nomor_registrasi' => $nomor_registrasi,
        ':nama_lengkap'    => $nama_lengkap,
        ':nim'             => $nim,
        ':email'           => $email,
        ':no_whatsapp'     => $no_whatsapp,
        ':angkatan'        => $angkatan,
        ':kelas'           => $kelas,
        ':divisi_utama'    => $divisi_utama,
        ':divisi_cadangan' => $divisi_cadangan,
        ':motivasi'        => $motivasi,
        ':link_portofolio' => $link_portofolio,
        ':file_foto'       => $fileFotoName,
        ':file_ktm'        => $fileKtmName,
    ]);

    echo json_encode([
        'success'           => true,
        'message'           => 'Pendaftaran berhasil dikirim dan tersimpan di database!',
        'registration_code' => $nomor_registrasi,
        'nim'               => $nim,
        'data'              => [
            'nama'   => $nama_lengkap,
            'nim'    => $nim,
            'divisi' => $divisi_utama
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem database: ' . $e->getMessage()
    ]);
}
