<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan apakah halaman saat ini adalah dashboard admin
$isAdminDashboard = (($_GET['page'] ?? '') === 'admin-dashboard' && ($_SESSION['role'] ?? '') === 'admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Jemaat GPM Waru</title>
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="ASSETS/css/style.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if ($isAdminDashboard): ?>
        <link rel="stylesheet" href="ASSETS/css/admin-dashboard.css">
    <?php endif; ?>

    <style>
        /* Styling Navbar Horizontal yang Rapi */
        .header-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header-nav a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .header-nav a i {
            font-size: 16px;
        }
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        /* Penyesuaian Topbar Admin agar Box Biru Full Width & Tanggal/Profil di Pojok Kanan Atas */
        .gpm-topbar {
            width: 100% !important;
            max-width: 100% !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            box-sizing: border-box;
            position: relative;
        }
        .gpm-welcome {
            flex: 1 !important;
        }
        .gpm-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            position: absolute;
            top: 20px;
            right: 25px;
            z-index: 5;
        }
        .gpm-topbar-right .gpm-date-pill {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .gpm-topbar-right .gpm-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px 4px 4px;
            border-radius: 20px;
        }
        .gpm-topbar-right .gpm-avatar {
            background: white;
            color: #0f2c59;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        /* Styling Navigasi Admin */
        .admin-nav-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0 10px 0;
        }
        .admin-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #0f2c59;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 6px;
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            transition: all 0.3s ease;
        }
        .admin-section-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f2c59;
            margin-top: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }

        /* Responsive untuk Perangkat Seluler (Mobile) */
        @media (max-width: 992px) {
            .nav-right-wrapper {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }
            .header-nav {
                display: none !important;
                width: 100% !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                background-color: #0f2c59 !important;
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                padding: 20px !important;
                gap: 12px !important;
                box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important;
                z-index: 99999 !important;
            }
            .header-nav.active {
                display: flex !important;
            }
            .header-nav a {
                color: #ffffff !important;
                padding: 10px 0 !important;
                text-decoration: none !important;
                display: block !important;
                font-size: 16px !important;
                width: 100% !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            .mobile-toggle {
                display: block !important;
                z-index: 10000;
            }
            .gpm-topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .gpm-topbar-right {
                position: static;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Header -->
    <header class="main-header" style="background-color: #0f2c59; color: white; padding: 15px 30px; position: sticky; top: 0; z-index: 1000;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 100%; box-sizing: border-box; position: relative;">
            
            <!-- Logo Area -->
            <div class="logo-area" style="display: flex; align-items: center; gap: 15px;">
                <i class="fa-solid fa-church logo-icon" style="color: #fbbf24; font-size: 28px;"></i>
                <div class="logo-text">
                    <h2 style="font-size: 18px; font-weight: bold; margin: 0; color: white;">JEMAAT GPM WARU</h2>
                    <span style="color: #d1d5db; font-size: 12px;">Gereja Protestan Maluku</span>
                </div>
            </div>

            <!-- Wadah Kanan: Tombol Hamburger & Navigasi -->
            <div class="nav-right-wrapper" style="display: flex; align-items: center; gap: 15px;">
                <!-- Tombol Hamburger Mobile (Berada di dekat area kanan/login) -->
                <button type="button" class="mobile-toggle" id="mobileToggle" aria-label="Buka Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- Navigasi Menu Publik & Fitur -->
                <nav class="header-nav" id="navMenu">
                    <a href="INDEX.php?page=beranda"><i class="fa-solid fa-house"></i> Beranda</a>
                    <a href="INDEX.php?page=jadwal"><i class="fa-solid fa-calendar-days"></i> Jadwal</a>
                    <a href="INDEX.php?page=fasilitas"><i class="fa-solid fa-building"></i> Fasilitas</a>
                    <a href="INDEX.php?page=galeri"><i class="fa-solid fa-images"></i> Galeri</a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="INDEX.php?page=admin-dashboard" class="nav-admin"><i class="fa-solid fa-user-shield"></i> Admin</a>
                        <a href="INDEX.php?page=logout" class="nav-logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
                    <?php else: ?>
                        <a href="INDEX.php?page=login" style="background-color: #ffd166; color: #1b3b6f; font-weight: bold; border-radius: 4px; padding: 6px 12px;"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                    <?php endif; ?>
                </nav>
            </div>

        </div>
    </header>

    <!-- Skrip JavaScript Tunggal yang Bersih & Responsif -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');

        if (mobileToggle && navMenu) {
            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                // Membuka atau menutup menu navigasi
                const opened = navMenu.classList.toggle('active');
                
                // Mengubah ikon hamburger (☰) menjadi tanda silang (✕) saat dibuka
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.className = opened ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
                }
            });

            // Menutup menu otomatis jika mengklik di luar area menu
            document.addEventListener('click', function(e) {
                if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
                    navMenu.classList.remove('active');
                    const icon = mobileToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fa-solid fa-bars';
                    }
                }
            });
        }
    });
    </script>

    <!-- KONDISI STRUKTUR KONTEN BERDASARKAN HALAMAN -->
    <?php if ($isAdminDashboard): ?>
        <!-- Layout Khusus Dashboard Admin -->
        <main class="main-content container" style="max-width: 100% !important; padding: 0 15px; box-sizing: border-box;">
            <main class="gpm-admin-main" style="width: 100%; max-width: 100%;">
                <header class="gpm-topbar" style="width: 100%;">
                    <section class="gpm-welcome" style="width: 100%;">
                        <div class="gpm-welcome-copy">
                            <span class="gpm-welcome-line"></span>
                            <h1>Selamat Datang, Administrator!</h1>
                            <p>Kelola data dan informasi Jemaat GPM Waru dengan cepat dan mudah melalui halaman administrator.</p>
                        </div>
                        <div class="gpm-welcome-watermark" aria-hidden="true"><i class="fa-solid fa-church"></i></div>
                    </section>
                    <!-- Posisi Tanggal & Profil di pojok kanan atas dalam box biru -->
                    <div class="gpm-topbar-right">
                        <div class="gpm-date-pill"><i class="fa-regular fa-calendar"></i><span id="gpmToday"></span></div>
                        <div class="gpm-profile"><span class="gpm-avatar"><i class="fa-solid fa-user"></i></span><span>Administrator</span></div>
                    </div>
                </header>

                <!-- Tombol Dashboard & Judul Menu Pengelolaan -->
                <div class="admin-nav-section">
                    <a href="INDEX.php?page=admin-dashboard" class="admin-nav-link">
                        <i class="fa-solid fa-house"></i> Dashboard
                    </a>
                </div>

                <h3 class="admin-section-title">
                    <i class="fa-solid fa-bars-progress" style="color: #0f2c59;"></i> Menu Pengelolaan
                </h3>

                <section class="gpm-admin-content">
    <?php else: ?>
        <!-- Layout Halaman Publik Biasa -->
        <main class="main-content container">
    <?php endif; ?>