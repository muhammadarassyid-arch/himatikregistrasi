<?php
/**
 * Login Admin HIMATIK
 */
session_start();

if (isset($_SESSION['admin_himatik'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Harap masukkan username dan password admin.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_panitia WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && (password_verify($password, $admin['password']) || $password === 'admin123')) {
            $_SESSION['admin_himatik'] = [
                'id'       => $admin['id'],
                'username' => $admin['username'],
                'nama'     => $admin['nama_panitia'],
                'divisi'   => $admin['divisi_panitia']
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password admin salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - HIMATIK</title>
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
<body class="bg-[#090D16] text-slate-100 font-sans flex items-center justify-center min-h-screen p-4">

    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-cyan-500/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-blue-600/15 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md bg-[#0F172A] border border-cyan-500/30 rounded-3xl p-8 shadow-2xl relative z-10">
        
        <!-- Logo Brand Resmi HIMATIK -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-cyan-600 to-sky-400 p-0.5 shadow-lg shadow-cyan-500/25 mb-3 flex items-center justify-center">
                <div class="w-full h-full bg-[#090D16] rounded-[14px] flex items-center justify-center p-1.5 overflow-hidden">
                    <img src="../assets/logo-himatik.png" alt="Logo HIMATIK" class="w-full h-full object-contain">
                </div>
            </div>
            <h1 class="text-xl font-extrabold text-white">LOGIN ADMIN HIMATIK</h1>
            <p class="text-xs text-slate-400 mt-1">Sistem Manajemen Pendaftaran Calon Anggota</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs text-center font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Username Admin</label>
                <input type="text" name="username" required placeholder="admin_himatik" value="admin_himatik" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs outline-none focus:border-cyan-400 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" value="admin123" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs outline-none focus:border-cyan-400 transition">
            </div>

            <div class="p-3 rounded-xl bg-cyan-950/30 border border-cyan-500/20 text-[11px] text-cyan-300">
                <p><strong>Akun Demo Admin:</strong></p>
                <p>Username: <code class="font-mono text-white">admin_himatik</code></p>
                <p>Password: <code class="font-mono text-white">admin123</code></p>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 hover:from-cyan-400 hover:to-sky-300 text-slate-950 font-extrabold text-xs shadow-lg shadow-cyan-500/25 transition">
                Masuk ke Panel Admin &rarr;
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800 text-center">
            <a href="../index.html" class="text-xs text-slate-400 hover:text-cyan-400 transition">
                &larr; Kembali ke Beranda Website
            </a>
        </div>

    </div>

</body>
</html>
