<?php
// 1. Mulai session paling awal sebelum ada output HTML apa pun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Sertakan koneksi database (sesuaikan jalur jika diperlukan, misal: __DIR__ . '/CONFIG/database.php')
require_once __DIR__ . '/CONFIG/database.php';

// 3. Tangkap parameter halaman dari URL
$page = isset($_GET['page']) ? trim($_GET['page']) : 'beranda';


// 4. KHUSUS HALAMAN LOGOUT: Tangani langsung di awal agar tidak memuat header HTML
if ($page === 'logout') {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }

    session_destroy();

    echo '<script>window.location.href = "INDEX.php?page=beranda";</script>';
    exit;
}

// 5. PROSES LOGIN POST: Tangani sebelum header HTML dikirim agar redirect bekerja mulus
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, username, email, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        $valid = false;
        if ($user) {
            $stored = (string)$user['password'];
            $valid = password_verify($password, $stored) || hash_equals($stored, $password);
        }
        
        if ($valid && in_array($user['role'], ['admin', 'kmj', 'editor', 'petugas', 'pengurus'], true)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: INDEX.php?page=admin-dashboard');
            } elseif ($user['role'] === 'kmj') {
                header('Location: INDEX.php?page=kmj-dashboard');
            } else {
                header('Location: INDEX.php?page=admin-dashboard');
            }
            exit;
        }
    }
    $_SESSION['login_error'] = 'Username atau password salah.';
    header('Location: INDEX.php?page=login');
    exit;
}
?>
<?php
// 6. Muat header HTML utama setelah seluruh proses redirect/session selesai
include __DIR__ . '/INCLUDE/header.php';

// 7. Pengaturan Rute Halaman (Switch Case)
switch ($page) {
    case 'jadwal': 
        include __DIR__ . '/PAGES/jadwal.php'; 
        break;

    case 'fasilitas': 
        include __DIR__ . '/PAGES/fasilitas.php'; 
        break;
        
    case 'kelola-fasilitas': 
        if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: INDEX.php?page=login'); exit; } 
        include __DIR__ . '/PAGES/kelola-fasilitas.php'; 
        break;
        
    case 'kelola-jadwal': 
        if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: INDEX.php?page=login'); exit; } 
        include __DIR__ . '/PAGES/kelola-jadwal.php'; 
        break;
        
    case 'kelola-tema':
        include __DIR__ . '/PAGES/kelola-temapelayanan.php';
        break;
        
    case 'kelola-dokumentasi':
        include __DIR__ . '/PAGES/kelola-dokumentasi.php';
        break;
        
    // DIJADIKAN SATU / TERINTEGRASI DI DIREKTORI PAGES
    case 'kelola-struktur': 
    case 'kelola-pendeta':
        if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: INDEX.php?page=login'); exit; } 
        include __DIR__ . '/PAGES/kelola-struktur.php'; 
        break;
    case 'kelola-jumlahjiwa':
        include __DIR__ . '/PAGES/kelola-statistikjemaat.php';
        break;
        
    case 'login': 
        include __DIR__ . '/PAGES/login.php'; 
        break;
        
    case 'logout': 
        include __DIR__ . '/PAGES/logout.php'; 
        break;
        
    case 'admin-dashboard': 
        include __DIR__ . '/PAGES/admin-dasboard.php'; 
        break;
        
    case 'kmj-dashboard': 
        include __DIR__ . '/PAGES/kmj.dasboard.php'; 
        break;
        
    case 'kelola-editor': 
        include __DIR__ . '/PAGES/kelola-editor.php'; 
        break;
        
    case 'beranda':
    default: 
        include __DIR__ . '/PAGES/beranda.php'; 
        break;
        
    case 'galeri':
    case 'dokumentasi':
        include __DIR__ . '/PAGES/dokumentasi.php';
        break;
}

// 8. Muat Footer Halaman
include __DIR__ . '/INCLUDE/footer.php';
?>