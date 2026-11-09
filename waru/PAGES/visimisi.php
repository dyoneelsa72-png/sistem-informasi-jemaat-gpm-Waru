<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data visi misi
$result = $conn->query("SELECT * FROM visi_misi WHERE id = 1");
$data = $result->fetch_assoc();
$visi = $data['visi'] ?? 'Visi belum diatur.';
$misi = $data['misi'] ?? 'Misi belum diatur.';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visi & Misi - Sistem Informasi Desa</title>
    <!-- FontAwesome & CSS Framework jika diperlukan (Bootstrap/Custom) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .vision-mission-section {
            padding: 60px 0;
            background-color: #f8f9fa;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .icon-box {
            font-size: 2.5rem;
            color: #6f42c1; /* Warna tema ungu senada */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container vision-mission-section">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Visi & Misi</h1>
            <p class="text-muted">Arah kebijakan dan komitmen pelayanan kepada masyarakat</p>
            <hr class="w-25 mx-auto text-purple">
        </div>

        <div class="row g-4 justify-content-center">
            <!-- VISI -->
            <div class="col-md-5">
                <div class="card card-custom h-100 p-4 bg-white">
                    <div class="card-body text-center">
                        <div class="icon-box">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <h3 class="card-title fw-bold mb-3">Visi</h3>
                        <p class="card-text text-secondary fs-5 fst-italic">
                            "<?= htmlspecialchars($visi); ?>"
                        </p>
                    </div>
                </div>
            </div>

            <!-- MISI -->
            <div class="col-md-7">
                <div class="card card-custom h-100 p-4 bg-white">
                    <div class="card-body">
                        <div class="text-center icon-box">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <h3 class="card-title fw-bold mb-3 text-center">Misi</h3>
                        <div class="card-text text-secondary">
                            <?php 
                                // Ubah format teks enter pada database menjadi list bernomor HTML
                                $misi_lines = explode("\n", $misi);
                                echo "<ol class='ps-3 fs-6'>";
                                foreach ($misi_lines as $line) {
                                    $clean_line = trim(preg_replace('/^\d+\.\s*/', '', $line)); // Bersihkan angka manual jika ada
                                    if (!empty($clean_line)) {
                                        echo "<li class='mb-2'>" . htmlspecialchars($clean_line) . "</li>";
                                    }
                                }
                                echo "</ol>";
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>