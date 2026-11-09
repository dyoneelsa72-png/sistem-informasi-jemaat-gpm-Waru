<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil pesan error jika ada
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Jemaat GPM Waru</title>
    <!-- FontAwesome untuk Icon Kunci & Gereja -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Link ke file CSS utama -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="login-page">
        <div class="login-card">
            
            <!-- Emblem / Logo Gereja -->
            <div class="login-emblem">
                <i class="fa-solid fa-church"></i>
            </div>

            <!-- Header Teks -->
            <span class="eyebrow">JEMAAT GPM WARU</span>
            <h1>Selamat Datang</h1>
            <p>Masuk ke panel Administrator untuk mengelola informasi jemaat.</p>

            <!-- Tampilkan Pesan Error jika ada dari Session -->
            <?php if (!empty($error)): ?>
                <div class="admin-alert error" style="margin-bottom: 20px; text-align: left;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Form Login -->
            <form action="INDEX.php?page=login" method="POST" class="admin-form">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" autocomplete="username" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="admin-btn primary full" style="margin-top: 10px; padding: 14px; font-size: 14px;">
                    Masuk ke Dashboard &rarr;
                </button>
            </form>

            <!-- Footer / Keterangan Akses -->
            <span class="login-foot">
                <i class="fa-solid fa-lock"></i> Akses khusus Administrator
            </span>
            
        </div>
    </div>

</body>
</html>