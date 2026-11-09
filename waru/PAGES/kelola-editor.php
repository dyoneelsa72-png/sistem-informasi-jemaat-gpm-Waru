<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    echo '<div class="container my-5"><div class="alert alert-danger">Halaman ini hanya dapat diakses Admin.</div></div>';
    return;
}
require_once __DIR__ . '/../CONFIG/database.php';
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Lengkapi semua data. Email harus valid dan password minimal 8 karakter.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username,email,password,nama_lengkap,role) VALUES (?,?,?,?, 'editor')");
        mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $hash, $nama);
        if (mysqli_stmt_execute($stmt)) $message = 'Akun Editor berhasil dibuat.';
        else $error = 'Gagal membuat akun. Username atau email mungkin sudah digunakan.';
        mysqli_stmt_close($stmt);
    }
}
?>
<div class="container my-5" style="max-width: 760px">
  <h2>Kelola Akun Editor</h2>
  <p>Admin dapat menambahkan akun Editor baru dari halaman ini.</p>
  <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <div class="card shadow-sm"><div class="card-body">
    <form method="post">
      <div class="mb-3"><label class="form-label" for="nama_lengkap">Nama lengkap</label><input class="form-control" id="nama_lengkap" name="nama_lengkap" required></div>
      <div class="mb-3"><label class="form-label" for="username">Username</label><input class="form-control" id="username" name="username" required></div>
      <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required></div>
      <div class="mb-3"><label class="form-label" for="password">Password (minimal 8 karakter)</label><input class="form-control" type="password" id="password" name="password" minlength="8" required></div>
      <button class="btn btn-primary" type="submit">Tambah Editor</button>
      <a class="btn btn-secondary" href="INDEX.php?page=admin-dashboard">Kembali</a>
    </form>
  </div></div>
</div>
