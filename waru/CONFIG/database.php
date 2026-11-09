<?php
/** Koneksi dan instalasi otomatis awal untuk Laragon. Data lama tidak dihapus. */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_waru';
mysqli_report(MYSQLI_REPORT_OFF);
$server = @mysqli_connect($db_host, $db_user, $db_pass);
if (!$server) { http_response_code(500); die('MySQL belum aktif. Buka Laragon lalu klik Start All.'); }
mysqli_set_charset($server, 'utf8mb4');
if (!mysqli_query($server, "CREATE DATABASE IF NOT EXISTS `db_waru` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    http_response_code(500); die('Database db_waru tidak dapat dibuat. Periksa hak akses MySQL Laragon.');
}
mysqli_select_db($server, $db_name);
$check = mysqli_query($server, "SHOW TABLES LIKE 'users'");
$hasUsers = $check && mysqli_num_rows($check) > 0;
if (!$hasUsers) {
    $sqlFile = __DIR__ . '/../DATABASE/gpm_waru_db.sql';
    if (is_file($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        // Schema seed hanya dijalankan pada database yang belum memiliki tabel users.
        if ($sql !== false && mysqli_multi_query($server, $sql)) {
            do { if ($res = mysqli_store_result($server)) mysqli_free_result($res); } while (mysqli_more_results($server) && mysqli_next_result($server));
            mysqli_select_db($server, $db_name);
        }
    }
}
// Tabel tambahan/kolom opsional dibuat tanpa menghapus atau menimpa tabel yang sudah ada.
mysqli_query($server, "CREATE TABLE IF NOT EXISTS surat_digital (
 id INT AUTO_INCREMENT PRIMARY KEY, nama_pemohon VARCHAR(100) NOT NULL, sektor VARCHAR(50) NOT NULL,
 unit VARCHAR(50) NOT NULL, jenis_surat VARCHAR(100) NOT NULL, keterangan TEXT,
 file_lampiran VARCHAR(255) DEFAULT NULL, file_surat VARCHAR(255) DEFAULT NULL,
 status ENUM('pending','diproses','selesai','ditolak') DEFAULT 'pending', tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$col = mysqli_query($server, "SHOW COLUMNS FROM surat_digital LIKE 'file_surat'");
if ($col && mysqli_num_rows($col) === 0) mysqli_query($server, "ALTER TABLE surat_digital ADD COLUMN file_surat VARCHAR(255) DEFAULT NULL AFTER file_lampiran");
mysqli_query($server, "CREATE TABLE IF NOT EXISTS fasilitas_gereja (
 id INT AUTO_INCREMENT PRIMARY KEY, nama_fasilitas VARCHAR(100) NOT NULL, jumlah INT DEFAULT 1, deskripsi TEXT, foto VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($server, "CREATE TABLE IF NOT EXISTS struktur_organisasi (
 id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(100) NOT NULL, jabatan VARCHAR(100) NOT NULL, sektor VARCHAR(50) DEFAULT NULL, unit VARCHAR(50) DEFAULT NULL, foto VARCHAR(255) DEFAULT NULL, urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
// Kolom tampilan waktu tambahan: migrasi aman, tidak menghapus/mengubah data jadwal lama.
mysqli_query($server, "CREATE TABLE IF NOT EXISTS jadwal_pelayanan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nama_pelayanan VARCHAR(100) NOT NULL,
 hari ENUM('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
 waktu TIME DEFAULT NULL,
 keterangan_minggu VARCHAR(100) DEFAULT NULL,
 lokasi VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$jadwalCol = mysqli_query($server, "SHOW COLUMNS FROM jadwal_pelayanan LIKE 'waktu_tampilan'");
if ($jadwalCol && mysqli_num_rows($jadwalCol) === 0) {
    mysqli_query($server, "ALTER TABLE jadwal_pelayanan ADD COLUMN waktu_tampilan VARCHAR(100) DEFAULT NULL AFTER waktu");
}
$conn = $server;
?>
