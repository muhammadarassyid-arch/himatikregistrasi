<?php
/**
 * Dashboard Admin HIMATIK
 * Kelola Data Calon Anggota, Status Seleksi (Diterima / Tidak Diterima), dan Pengaturan Biaya Pendaftaran
 */
session_start();

if (!isset($_SESSION['admin_himatik'])) {
    header('Location: login.php');
    exit;
}

$admin_user = $_SESSION['admin_himatik'];

require_once __DIR__ . '/../db.php';

// Ambil Pengaturan Biaya & Status Pendaftaran Saat Ini
$stmt_settings = $pdo->query("SELECT kunci, nilai FROM pengaturan");
$settings = [];
while ($s = $stmt_settings->fetch()) {
    $settings[$s['kunci']] = $s['nilai'];
}
$current_biaya = isset($settings['biaya_pendaftaran']) ? (int) $settings['biaya_pendaftaran'] : 0;
$current_catatan_biaya = $settings['catatan_pembayaran'] ?? 'Gratis tanpa biaya';
$current_status_pendaftaran = strtolower(trim($settings['status_pendaftaran'] ?? 'buka'));
$is_pendaftaran_buka = ($current_status_pendaftaran !== 'tutup');
$current_pesan_penutupan = $settings['pesan_penutupan'] ?? 'Pendaftaran calon anggota HIMATIK periode ini telah resmi ditutup. Terima kasih atas partisipasi Anda.';

// Ambil filter divisi, status, angkatan, pencarian
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
$pendaftar_list = $stmt->fetchAll();

// Hitung Statistik
$stat_total    = $pdo->query("SELECT COUNT(*) FROM pendaftar")->fetchColumn();
$stat_diterima = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status_seleksi = 'Diterima' OR status_seleksi LIKE '%Lolos%'")->fetchColumn();
$stat_tidak    = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status_seleksi = 'Tidak Diterima' OR status_seleksi LIKE '%Tidak%'")->fetchColumn();
$stat_menunggu = $stat_total - ($stat_diterima + $stat_tidak);
if ($stat_menunggu < 0) $stat_menunggu = 0;

// Helper URL WhatsApp
function formatWaLink($no_wa, $nama, $reg, $status) {
    $clean = preg_replace('/[^0-9]/', '', $no_wa);
    if (substr($clean, 0, 1) === '0') {
        $clean = '62' . substr($clean, 1);
    } elseif (substr($clean, 0, 2) !== '62') {
        $clean = '62' . $clean;
    }
    $statusText = ($status === 'Diterima') ? 'DITERIMA' : (($status === 'Tidak Diterima') ? 'TIDAK DITERIMA' : 'SEDANG DIPROSES');
    $pesan = "Halo {$nama} ({$reg}), kami dari Admin HIMATIK menginformasikan bahwa status seleksi Anda adalah: *{$statusText}*.";
    return "https://wa.me/{$clean}?text=" . urlencode($pesan);
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin HIMATIK - Pendaftaran Online</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { cyan: '#00A3E0', dark: '#090D16', navy: '#0F172A', card: '#131B2E' }
                    },
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'], mono: ['"JetBrains Mono"', 'monospace'] }
                }
            }
        }
    </script>
</head>
<body class="bg-[#090D16] text-slate-100 font-sans min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <header class="border-b border-slate-800 bg-[#0F172A]/90 backdrop-blur-md sticky top-0 z-30 px-4 sm:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-900 border border-cyan-500/30 flex items-center justify-center p-1 overflow-hidden shadow-md">
                <img src="../assets/logo-himatik.png" alt="Logo HIMATIK" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-extrabold text-white text-base tracking-tight flex items-center gap-2">
                    <span>PANEL ADMIN HIMATIK</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-mono">Official</span>
                </h1>
                <p class="text-xs text-slate-400">Login sebagai: <span class="text-cyan-300 font-semibold"><?= htmlspecialchars($admin_user['nama']) ?></span></p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="../index.html" target="_blank" class="hidden sm:flex text-xs font-semibold px-3.5 py-2 rounded-xl bg-slate-800 text-cyan-400 hover:bg-slate-700 transition items-center gap-1.5">
                <span>🌐 Lihat Website</span>
            </a>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="text-xs font-semibold px-3.5 py-2 rounded-xl bg-rose-500/15 text-rose-300 hover:bg-rose-500/25 border border-rose-500/30 transition">
                🚪 Logout
            </a>
        </div>
    </header>

    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 flex-grow">

        <!-- Flash Notice -->
        <?php if (isset($_GET['updated'])): ?>
            <div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-semibold flex items-center justify-between">
                <span>✓ Status seleksi mahasiswa berhasil diperbarui!</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">✕</button>
            </div>
        <?php elseif (isset($_GET['status_pendaftaran_updated'])): ?>
            <div class="p-4 rounded-2xl <?= $_GET['status_pendaftaran_updated'] === 'tutup' ? 'bg-rose-500/20 border-rose-500/40 text-rose-300' : 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300' ?> border text-xs font-semibold flex items-center justify-between">
                <span>✓ Status pendaftaran berhasil diperbarui: <?= $_GET['status_pendaftaran_updated'] === 'tutup' ? '🔴 PENDAFTARAN DITUTUP' : '🟢 PENDAFTARAN DIBUKA' ?>!</span>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">✕</button>
            </div>
        <?php elseif (isset($_GET['biaya_updated'])): ?>
            <div class="p-4 rounded-2xl bg-cyan-500/20 border border-cyan-500/40 text-cyan-300 text-xs font-semibold flex items-center justify-between">
                <span>✓ Biaya pendaftaran berhasil diperbarui dan langsung tampil di landing page!</span>
                <button onclick="this.parentElement.remove()" class="text-cyan-400 hover:text-white">✕</button>
            </div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="p-4 rounded-2xl bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-semibold flex items-center justify-between">
                <span>✓ Data pendaftar berhasil dihapus dari sistem.</span>
                <button onclick="this.parentElement.remove()" class="text-amber-400 hover:text-white">✕</button>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold flex items-center justify-between">
                <span>✕ <?= htmlspecialchars($_GET['error']) ?></span>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">✕</button>
            </div>
        <?php endif; ?>

        <!-- GRID PENGATURAN ADMIN: STATUS PENDAFTARAN & BIAYA -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- KARTU 1: BUKA / TUTUP PENDAFTARAN -->
            <div class="p-6 rounded-3xl bg-[#131B2E] border <?= $is_pendaftaran_buka ? 'border-emerald-500/30' : 'border-rose-500/40' ?> shadow-xl flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full <?= $is_pendaftaran_buka ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' ?> text-[11px] font-bold font-mono">
                            <?= $is_pendaftaran_buka ? '🟢 PENDAFTARAN DIBUKA' : '🔴 PENDAFTARAN DITUTUP' ?>
                        </span>
                    </div>
                    <h2 class="text-base font-bold text-white mt-2.5">Kontrol Pendaftaran Calon Anggota</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Status saat ini: 
                        <strong class="<?= $is_pendaftaran_buka ? 'text-emerald-400' : 'text-rose-400' ?>">
                            <?= $is_pendaftaran_buka ? 'Aktif Menerima Pendaftaran' : 'Pendaftaran Ditutup untuk Publik' ?>
                        </strong>
                    </p>
                </div>

                <form action="update-status-pendaftaran.php" method="POST" class="space-y-3 pt-2 border-t border-slate-800">
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Pesan Notifikasi Penutupan (Tampil di Website)</label>
                        <input type="text" name="pesan_penutupan" value="<?= htmlspecialchars($current_pesan_penutupan) ?>" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 focus:border-cyan-400 text-white text-xs outline-none" placeholder="Pesan saat pendaftaran ditutup...">
                    </div>
                    <div>
                        <?php if ($is_pendaftaran_buka): ?>
                            <input type="hidden" name="status_pendaftaran" value="tutup">
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin MENUTUP pendaftaran calon anggota? Mahasiswa tidak akan dapat mengisi formulir lagi.')" class="w-full py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                                <span>🔒 Tutup Pendaftaran Sekarang</span>
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="status_pendaftaran" value="buka">
                            <button type="submit" onclick="return confirm('Buka kembali pendaftaran calon anggota sekarang?')" class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                                <span>🔓 Buka Pendaftaran Sekarang</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- KARTU 2: ATUR BIAYA PENDAFTARAN -->
            <div class="p-6 rounded-3xl bg-[#131B2E] border border-cyan-500/30 shadow-xl flex flex-col justify-between space-y-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 text-[11px] font-bold font-mono">
                        ⚙️ BIAYA PENDAFTARAN
                    </span>
                    <h2 class="text-base font-bold text-white mt-2.5">Atur Biaya Pendaftaran Calon Anggota</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Biaya saat ini di website: 
                        <strong class="text-cyan-300 font-mono text-sm"><?= $current_biaya <= 0 ? 'Rp 0 (Gratis)' : 'Rp ' . number_format($current_biaya, 0, ',', '.') ?></strong>
                        &bull; <?= htmlspecialchars($current_catatan_biaya) ?>
                    </p>
                </div>

                <form action="update-biaya.php" method="POST" class="space-y-3 pt-2 border-t border-slate-800">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Nominal Biaya (Rp)</label>
                            <input type="number" name="biaya_pendaftaran" value="<?= $current_biaya ?>" min="0" step="1000" placeholder="0 = Gratis" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 focus:border-cyan-400 text-white font-mono text-xs outline-none" required>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Keterangan / Instruksi</label>
                            <input type="text" name="catatan_pembayaran" value="<?= htmlspecialchars($current_catatan_biaya) ?>" placeholder="Contoh: Gratis / Transfer Bank" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 focus:border-cyan-400 text-white text-xs outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-md transition">
                        Simpan Biaya
                    </button>
                </form>
            </div>

        </div>

        <!-- 3 Stat Cards (Diterima / Tidak Diterima / Menunggu) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-[#131B2E] border border-slate-800 shadow-lg">
                <p class="text-xs text-slate-400">Total Pendaftar</p>
                <p class="text-2xl font-bold font-mono text-white mt-1"><?= $stat_total ?></p>
            </div>
            <div class="p-5 rounded-2xl bg-[#131B2E] border border-slate-800 shadow-lg">
                <p class="text-xs text-slate-400">Menunggu Pengumuman</p>
                <p class="text-2xl font-bold font-mono text-cyan-400 mt-1"><?= $stat_menunggu ?></p>
            </div>
            <div class="p-5 rounded-2xl bg-[#131B2E] border border-slate-800 shadow-lg">
                <p class="text-xs text-slate-400">Diterima</p>
                <p class="text-2xl font-bold font-mono text-emerald-400 mt-1"><?= $stat_diterima ?></p>
            </div>
            <div class="p-5 rounded-2xl bg-[#131B2E] border border-slate-800 shadow-lg">
                <p class="text-xs text-slate-400">Tidak Diterima</p>
                <p class="text-2xl font-bold font-mono text-rose-400 mt-1"><?= $stat_tidak ?></p>
            </div>
        </div>

        <!-- Toolbar Pencarian & Filter Divisi/Status -->
        <div class="p-5 rounded-2xl bg-[#131B2E] border border-slate-800 space-y-4 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-bold text-white text-sm">Filter Calon Anggota</h3>
                    <p class="text-xs text-slate-400">Filter berdasarkan 3 Divisi (Pemrograman, Mikro, Jaringan) atau Status Hasil Seleksi.</p>
                </div>
                <!-- Tombol Export Excel -->
                <a href="export-excel.php?divisi=<?= urlencode($filter_divisi) ?>&status=<?= urlencode($filter_status) ?>&angkatan=<?= urlencode($filter_angkatan) ?>&q=<?= urlencode($search_query) ?>" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 shadow-md transition self-start sm:self-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Download Rekap Excel (.CSV)</span>
                </a>
            </div>

            <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <div class="sm:col-span-4">
                    <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Cari Nama, NIM, atau No. Registrasi..." class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white outline-none focus:border-cyan-400">
                </div>
                <div class="sm:col-span-3">
                    <select name="divisi" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white outline-none focus:border-cyan-400">
                        <option value="">Semua Divisi</option>
                        <option value="Pemrograman" <?= $filter_divisi == 'Pemrograman' ? 'selected' : '' ?>>Divisi Pemrograman</option>
                        <option value="Mikro" <?= $filter_divisi == 'Mikro' ? 'selected' : '' ?>>Divisi Mikro</option>
                        <option value="Jaringan" <?= $filter_divisi == 'Jaringan' ? 'selected' : '' ?>>Divisi Jaringan</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <select name="angkatan" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white outline-none focus:border-cyan-400">
                        <option value="">Semua Angkatan</option>
                        <?php 
                        $curYear = (int) date('Y');
                        for ($y = $curYear; $y >= $curYear - 4; $y--): ?>
                            <option value="<?= $y ?>" <?= $filter_angkatan == (string)$y ? 'selected' : '' ?>>Angkatan <?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white outline-none focus:border-cyan-400">
                        <option value="">Semua Status</option>
                        <option value="Menunggu Pengumuman" <?= $filter_status == 'Menunggu Pengumuman' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Diterima" <?= $filter_status == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                        <option value="Tidak Diterima" <?= $filter_status == 'Tidak Diterima' ? 'selected' : '' ?>>Tidak Diterima</option>
                    </select>
                </div>
                <div class="sm:col-span-1 flex gap-2">
                    <button type="submit" title="Terapkan Filter" class="w-full py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center justify-center">
                        ✓
                    </button>
                    <?php if (!empty($filter_divisi) || !empty($filter_status) || !empty($filter_angkatan) || !empty($search_query)): ?>
                        <a href="index.php" title="Reset Filter" class="px-2.5 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 text-xs flex items-center justify-center">
                            ✕
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Data Pendaftar -->
        <div class="rounded-2xl bg-[#131B2E] border border-slate-800 overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-white text-sm">Daftar Calon Anggota (<?= count($pendaftar_list) ?> Mahasiswa Ditemukan)</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900 text-slate-400 uppercase font-mono text-[11px] border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3">No. Reg / NIM</th>
                            <th class="px-5 py-3">Nama Mahasiswa</th>
                            <th class="px-5 py-3">Pilihan Divisi</th>
                            <th class="px-5 py-3">Status Seleksi</th>
                            <th class="px-5 py-3">Berkas</th>
                            <th class="px-5 py-3 text-right">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (empty($pendaftar_list)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                                    Belum ada data calon anggota yang cocok dengan filter pencarian.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendaftar_list as $row): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-5 py-4 font-mono">
                                        <span class="text-cyan-400 font-bold"><?= htmlspecialchars($row['nomor_registrasi']) ?></span>
                                        <div class="text-[11px] text-slate-400"><?= htmlspecialchars($row['nim']) ?></div>
                                        <div class="text-[10px] text-slate-500"><?= htmlspecialchars($row['angkatan']) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-white text-sm"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                        <div class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($row['email']) ?></div>
                                        <div class="mt-1 flex items-center gap-2">
                                            <a href="<?= formatWaLink($row['no_whatsapp'], $row['nama_lengkap'], $row['nomor_registrasi'], $row['status_seleksi']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border border-emerald-500/30 font-semibold text-[10px] transition">
                                                <span>💬 Hubungi WA</span>
                                                <span class="text-[9px] font-mono">(<?= htmlspecialchars($row['no_whatsapp']) ?>)</span>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-1 rounded bg-cyan-500/15 text-cyan-300 font-bold border border-cyan-500/30">
                                            1. <?= htmlspecialchars($row['divisi_utama']) ?>
                                        </span>
                                        <div class="text-[11px] text-slate-400 mt-1 font-medium">2. <?= htmlspecialchars($row['divisi_cadangan']) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php
                                        $curStatus = $row['status_seleksi'];
                                        if (stripos($curStatus, 'Diterima') !== false || stripos($curStatus, 'Lolos') !== false) {
                                            $badgeClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 font-bold';
                                            $dispStatus = 'Diterima';
                                        } elseif (stripos($curStatus, 'Tidak') !== false) {
                                            $badgeClass = 'bg-rose-500/20 text-rose-300 border-rose-500/40 font-bold';
                                            $dispStatus = 'Tidak Diterima';
                                        } else {
                                            $badgeClass = 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30 font-semibold';
                                            $dispStatus = 'Menunggu Pengumuman';
                                        }
                                        ?>
                                        <span class="px-3 py-1 rounded-full border text-[11px] inline-block <?= $badgeClass ?>">
                                            <?= $dispStatus ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <?php if ($row['file_foto']): ?>
                                                <a href="../uploads/foto/<?= htmlspecialchars($row['file_foto']) ?>" target="_blank" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 text-[11px] font-medium border border-slate-700">Foto</a>
                                            <?php else: ?>
                                                <span class="text-slate-600">-</span>
                                            <?php endif; ?>
                                            <?php if ($row['file_ktm']): ?>
                                                <a href="../uploads/ktm/<?= htmlspecialchars($row['file_ktm']) ?>" target="_blank" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 text-[11px] font-medium border border-slate-700">KTM/KRS</a>
                                            <?php else: ?>
                                                <span class="text-slate-600">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="../cetak-bukti.php?nim=<?= urlencode($row['nim']) ?>" target="_blank" title="Cetak Kartu Peserta" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">
                                                🖨️
                                            </a>
                                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)" class="px-3 py-1.5 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 font-semibold text-xs border border-cyan-500/40">
                                                Ubah Status
                                            </button>
                                            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data calon anggota <?= htmlspecialchars($row['nama_lengkap']) ?>?')" title="Hapus Data" class="px-2 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 text-xs">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal Ubah Status Seleksi (Diterima / Tidak Diterima / Menunggu) -->
    <div id="editModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-[#0F172A] border border-cyan-500/40 rounded-3xl p-6 sm:p-7 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-sm" id="modalTitle">Ubah Status Hasil Seleksi</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form action="update-status.php" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="id" id="modalId">

                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-slate-400 text-[11px] block">Mahasiswa:</span>
                    <p class="font-bold text-white text-sm mt-0.5" id="modalNamaNim">-</p>
                    <p class="text-slate-400 text-[11px] mt-1" id="modalDivisiInfo">-</p>
                </div>

                <div>
                    <label class="block text-slate-300 mb-1 font-semibold">Tentukan Status Seleksi *</label>
                    <select name="status_seleksi" id="modalStatus" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-semibold outline-none focus:border-cyan-400">
                        <option value="Diterima" class="text-emerald-400">Diterima (Lolos Seleksi)</option>
                        <option value="Tidak Diterima" class="text-rose-400">Tidak Diterima (Tidak Lolos)</option>
                        <option value="Menunggu Pengumuman" class="text-cyan-400">Menunggu Pengumuman</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 mb-1 font-semibold">Catatan Admin untuk Mahasiswa</label>
                    <textarea name="catatan_panitia" id="modalCatatan" rows="3" placeholder="Pesan ini akan langsung dibaca mahasiswa saat cek status lewat NIM..." class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white outline-none focus:border-cyan-400"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="border-t border-slate-800/80 py-4 bg-[#060910] text-center text-xs text-slate-500">
        &copy; 2026 Panel Admin HIMATIK. Sistem Informasi Pendaftaran.
    </footer>

    <script>
        function openEditModal(data) {
            document.getElementById('modalId').value = data.id;
            document.getElementById('modalNamaNim').innerText = `${data.nama_lengkap} (${data.nim}) - ${data.nomor_registrasi}`;
            document.getElementById('modalDivisiInfo').innerText = `Pilihan 1: ${data.divisi_utama} | Pilihan 2: ${data.divisi_cadangan}`;
            
            let s = data.status_seleksi || 'Menunggu Pengumuman';
            if (s.toLowerCase().includes('diterima') || s.toLowerCase().includes('lolos')) {
                s = 'Diterima';
            } else if (s.toLowerCase().includes('tidak')) {
                s = 'Tidak Diterima';
            } else {
                s = 'Menunggu Pengumuman';
            }
            document.getElementById('modalStatus').value = s;
            document.getElementById('modalCatatan').value = data.catatan_panitia || '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>
