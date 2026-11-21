<?php
/**
 * Cetak Kartu Bukti Pendaftaran Calon Anggota HIMATIK
 * Himpunan Mahasiswa Teknik Informatika
 * Format siap cetak (Ctrl+P / Simpan sebagai PDF)
 */

require_once __DIR__ . '/db.php';

$nim = trim($_GET['nim'] ?? '');
$reg = trim($_GET['reg'] ?? '');

if (empty($nim) && empty($reg)) {
    die("NIM atau Nomor Registrasi tidak diberikan.");
}

$stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE nim = :nim OR nomor_registrasi = :reg LIMIT 1");
$stmt->execute([':nim' => $nim, ':reg' => $reg]);
$pendaftar = $stmt->fetch();

if (!$pendaftar) {
    die("Data calon anggota tidak ditemukan. Pastikan Anda telah terdaftar.");
}

// Format status
$rawStatus = trim($pendaftar['status_seleksi'] ?? '');
if (stripos($rawStatus, 'Diterima') !== false || stripos($rawStatus, 'Lolos') !== false) {
    $statusText = 'DITERIMA';
    $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
} elseif (stripos($rawStatus, 'Tidak') !== false) {
    $statusText = 'TIDAK DITERIMA';
    $statusClass = 'bg-rose-100 text-rose-800 border-rose-300';
} else {
    $statusText = 'MENUNGGU PENGUMUMAN';
    $statusClass = 'bg-cyan-100 text-cyan-800 border-cyan-300';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - <?= htmlspecialchars($pendaftar['nama_lengkap']) ?> (<?= htmlspecialchars($pendaftar['nomor_registrasi']) ?>)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                margin: 0 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 flex flex-col items-center justify-center min-h-screen">

    <!-- Action Toolbar (Hidden during Print) -->
    <div class="no-print max-w-2xl w-full mb-6 flex items-center justify-between bg-slate-900 text-white px-6 py-3.5 rounded-2xl shadow-lg border border-slate-800">
        <a href="index.html" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1.5">
            &larr; Kembali ke Portal
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-md transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- MAIN PRINT CARD -->
    <div class="print-card max-w-2xl w-full bg-white rounded-3xl p-8 sm:p-10 shadow-2xl border-2 border-slate-200 relative overflow-hidden">
        
        <!-- Header Kop HIMATIK dengan Logo Resmi -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center p-1 border border-slate-300 shadow-sm">
                    <img src="assets/logo-himatik.png" alt="Logo Resmi HIMATIK" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="font-extrabold text-xl tracking-tight text-slate-950">HIMATIK</h1>
                    <p class="text-xs font-bold text-slate-700 tracking-wider">HIMPUNAN MAHASISWA TEKNIK INFORMATIKA</p>
                    <p class="text-[11px] text-slate-500">Pengurus & Tim Seleksi Periode <?= date('Y') ?>/<?= date('Y') + 1 ?></p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-cyan-100 text-cyan-800 border border-cyan-300 font-bold text-xs rounded-full uppercase">
                    KARTU BUKTI DAFTAR
                </span>
                <p class="font-mono text-xs text-slate-500 mt-1"><?= date('d/m/Y H:i', strtotime($pendaftar['created_at'])) ?> WIB</p>
            </div>
        </div>

        <!-- Registration Number Banner -->
        <div class="my-6 p-4 rounded-2xl bg-slate-50 border border-slate-300 flex items-center justify-between">
            <div>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-semibold">Nomor Registrasi Resmi</p>
                <p class="font-mono text-xl font-extrabold text-cyan-700 tracking-wider"><?= htmlspecialchars($pendaftar['nomor_registrasi']) ?></p>
            </div>
            <!-- Simulated QR Code Icon -->
            <div class="w-14 h-14 bg-white border border-slate-300 rounded-xl p-1.5 flex flex-col items-center justify-center text-[9px] font-mono font-bold text-slate-800 text-center shadow-sm">
                <svg class="w-8 h-8 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v4h-4v-4zm-4 0h2v2h-2v-2zm0 4h2v2h-2v-2zm4 2h2v2h-2v-2zm-2 2h4v2h-4v-2z"/>
                </svg>
                VERIFIED
            </div>
        </div>

        <!-- Candidate Information Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 pb-6 border-b border-slate-200">
            
            <!-- Left Info Data -->
            <div class="sm:col-span-8 space-y-3.5 text-xs">
                <div>
                    <span class="text-slate-500 font-medium block">Nama Lengkap:</span>
                    <span class="text-slate-950 font-bold text-sm"><?= htmlspecialchars($pendaftar['nama_lengkap']) ?></span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-slate-500 font-medium block">NIM:</span>
                        <span class="font-mono font-bold text-slate-900"><?= htmlspecialchars($pendaftar['nim']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Angkatan / Kelas:</span>
                        <span class="font-semibold text-slate-900"><?= htmlspecialchars($pendaftar['angkatan']) ?> / <?= htmlspecialchars($pendaftar['kelas'] ?? '-') ?></span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-slate-500 font-medium block">Email:</span>
                        <span class="font-semibold text-slate-900"><?= htmlspecialchars($pendaftar['email']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">No. WhatsApp:</span>
                        <span class="font-semibold text-slate-900"><?= htmlspecialchars($pendaftar['no_whatsapp']) ?></span>
                    </div>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block">Pilihan Divisi (3 Divisi):</span>
                    <p class="font-bold text-cyan-800">1. <?= htmlspecialchars($pendaftar['divisi_utama']) ?></p>
                    <p class="font-medium text-slate-700">2. <?= htmlspecialchars($pendaftar['divisi_cadangan']) ?></p>
                </div>
            </div>

            <!-- Right Photo Placeholder / Actual Photo -->
            <div class="sm:col-span-4 flex flex-col items-center justify-center">
                <div class="w-32 h-40 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 overflow-hidden flex items-center justify-center text-center p-2 shadow-inner">
                    <?php if (!empty($pendaftar['file_foto']) && file_exists(__DIR__ . '/uploads/foto/' . $pendaftar['file_foto'])): ?>
                        <img src="uploads/foto/<?= htmlspecialchars($pendaftar['file_foto']) ?>" alt="Pas Foto" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="space-y-1 text-slate-400">
                            <svg class="w-10 h-10 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-[10px] block font-semibold">Foto 3x4</span>
                        </div>
                    <?php endif; ?>
                </div>
                <span class="text-[10px] text-slate-400 mt-2 font-mono">Pas Foto Calon Anggota</span>
            </div>

        </div>

        <!-- Status Box: DITERIMA / TIDAK DITERIMA / MENUNGGU -->
        <div class="mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-slate-600 font-semibold">Hasil Status Seleksi:</span>
                <span class="font-extrabold px-3.5 py-1 rounded-full border <?= $statusClass ?>">
                    <?= $statusText ?>
                </span>
            </div>
            <?php if (!empty($pendaftar['catatan_panitia'])): ?>
                <div class="pt-2 border-t border-slate-200">
                    <span class="text-slate-500 text-[11px] block font-semibold">Catatan Admin:</span>
                    <p class="text-slate-800 text-xs mt-0.5"><?= htmlspecialchars($pendaftar['catatan_panitia']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pernyataan & Tanda Tangan -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex justify-between items-end text-xs">
            <div class="max-w-xs text-[10px] text-slate-500 leading-relaxed">
                <p class="font-bold text-slate-700">Catatan Penting:</p>
                <p>1. Kartu ini merupakan bukti resmi pendaftaran online HIMATIK.</p>
                <p>2. Cek status hasil kelulusan secara berkala di portal.</p>
            </div>
            <div class="text-center font-mono">
                <p class="text-[10px] text-slate-500 mb-12">Calon Anggota Terdaftar,</p>
                <p class="font-bold text-slate-900 underline"><?= htmlspecialchars($pendaftar['nama_lengkap']) ?></p>
                <p class="text-[10px] text-slate-500">NIM: <?= htmlspecialchars($pendaftar['nim']) ?></p>
            </div>
        </div>

    </div>

    <footer class="no-print mt-6 text-xs text-slate-400 text-center">
        &copy; <?= date('Y') ?> HIMATIK Online Registration System. Digenerate secara digital.
    </footer>

</body>
</html>
