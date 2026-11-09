<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kmj') {
    header("Location: index.php?page=login");
    exit;
}
?>
<div class="container my-5">
    <h2>Dashboard Ketua Majelis Jemaat (KMJ)</h2>
    <p>Selamat datang, <b><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></b>!</p>
    <a href="index.php?page=logout" class="btn btn-danger">Logout</a>
</div>