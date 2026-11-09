-- ===================================================
-- DATABASE SCHEME & DATA INITIALIZATION
-- SISTEM INFORMASI JEMAAT GPM WARU
-- ===================================================

CREATE DATABASE IF NOT EXISTS db_waru DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE db_waru;

-- 1. TABEL USERS (AKSES ADMIN / PETUGAS)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kmj', 'editor', 'petugas', 'pengurus') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. TABEL PROFIL JEMAAT & STATISTIK
CREATE TABLE IF NOT EXISTS profil_jemaat (
    id INT PRIMARY KEY DEFAULT 1,
    nama_jemaat VARCHAR(100) DEFAULT 'Jemaat GPM Waru',
    sejarah TEXT,
    visi TEXT,
    misi TEXT,
    profil_kmj TEXT,
    jumlah_jemaat INT DEFAULT 1016000,
    jumlah_kk INT DEFAULT 225,
    jumlah_laki INT DEFAULT 521,
    jumlah_perempuan INT DEFAULT 495,
    alamat TEXT,
    email_kontak VARCHAR(100),
    facebook_url VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. TABEL STRUKTUR ORGANISASI
CREATE TABLE IF NOT EXISTS struktur_organisasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    sektor VARCHAR(50) DEFAULT NULL,
    unit VARCHAR(50) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. TABEL JADWAL PELAYANAN & IBADAH
CREATE TABLE IF NOT EXISTS jadwal_pelayanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelayanan VARCHAR(100) NOT NULL,
    hari ENUM('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    waktu TIME DEFAULT NULL,
    keterangan_minggu VARCHAR(100) DEFAULT NULL,
    lokasi VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 5. TABEL FASILITAS GEREJA
CREATE TABLE IF NOT EXISTS fasilitas_gereja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_fasilitas VARCHAR(100) NOT NULL,
    jumlah INT DEFAULT 1,
    deskripsi TEXT,
    foto VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 6. TABEL BANNER BERANDA / SLIDER
CREATE TABLE IF NOT EXISTS banner_beranda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) DEFAULT 'SELAMAT DATANG DI SISTEM INFORMASI JEMAAT GPM WARU',
    subjudul VARCHAR(255),
    file_gambar VARCHAR(255) NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 7. TABEL GALERI & DOKUMENTASI PEMBANGUNAN
CREATE TABLE IF NOT EXISTS galeri_dokumentasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    kategori ENUM('kegiatan', 'pembangunan', 'video') NOT NULL,
    tipe_file ENUM('foto', 'video') DEFAULT 'foto',
    file_path_or_url VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    tanggal_upload DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 8. TABEL SURAT DIGITAL ONLINE
CREATE TABLE IF NOT EXISTS surat_digital (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pemohon VARCHAR(100) NOT NULL,
    sektor VARCHAR(50) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    jenis_surat VARCHAR(100) NOT NULL,
    keterangan TEXT,
    file_lampiran VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'diproses', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ===================================================
-- INSERT DATA AWAL (BERDASARKAN HASIL WAWANCARA)
-- ===================================================

INSERT INTO profil_jemaat (id, nama_jemaat, sejarah, visi, misi, profil_kmj, jumlah_jemaat, jumlah_kk, jumlah_laki, jumlah_perempuan, alamat, email_kontak, facebook_url)
VALUES (
    1, 
    'Jemaat GPM Waru', 
    'Jemaat GPM Waru merupakan salah satu komunitas persekutuan umat Allah dalam lingkungan Gereja Protestan Maluku (GPM) yang terus berkembang melayani jemaat dengan penuh kasih.', 
    'Menjadi Jemaat GPM Waru yang Bersekutu, Bersaksi, dan Melayani secara Mandiri, Transformatif, dan Berkelanjutan.', 
    '1. Meningkatkan pembinaan spiritualitas warga jemaat.\n2. Mengembangkan sarana dan pelayanan digital jemaat.\n3. Memperkuat kepedulian sosial dan persekutuan antar Sektor & Unit.', 
    'Ketua Majelis Jemaat (KMJ) GPM Waru bertindak sebagai pimpinan rohani dan koordinator pelayanan ibadah di lingkungan Jemaat GPM Waru.', 
    1016000, 225, 521, 495, 
    'Desa Waru, Maluku', 
    'info@gpmwaru.org', 
    'https://facebook.com'
) ON DUPLICATE KEY UPDATE nama_jemaat=VALUES(nama_jemaat);

-- Data Fasilitas
INSERT INTO fasilitas_gereja (nama_fasilitas, jumlah, deskripsi) VALUES
('Pastori', 2, 'Fasilitas rumah dinas pelayanan Pendeta Jemaat GPM Waru.'),
('Gedung Gereja', 2, 'Terdiri dari Gedung Gereja Rehobot dan Gedung Gereja Imanuel.'),
('Inventaris Gereja', 1, 'Sarana prasarana penunjang peribadahan dan pelayanan jemaat.');

-- Data Struktur
INSERT INTO struktur_organisasi (nama, jabatan, sektor, unit, urutan) VALUES
('Pendeta 1', 'Pendeta / Ketua Majelis Jemaat', 'Semua Sektor', 'Semua Unit', 1),
('Pendeta 2', 'Pendeta Pelayanan', 'Semua Sektor', 'Semua Unit', 2),
('Penatua 1', 'Penatua', 'Sektor 1', 'Unit 1-3', 3),
('Penatua 2', 'Penatua', 'Sektor 2', 'Unit 4-6', 4),
('Penatua 3', 'Penatua', 'Sektor 3', 'Unit 7-9', 5),
('Penatua 4', 'Penatua', 'Sektor 4', 'Unit 10-12', 6);

-- Data Jadwal Pelayanan
INSERT INTO jadwal_pelayanan (nama_pelayanan, hari, waktu, lokasi) VALUES
('Ibadah Minggu Utama', 'Minggu', '09:00:00', 'Gedung Gereja Rehobot & Gereja Imanuel'),
('Ibadah Sekolah Minggu', 'Minggu', '12:00:00', 'Sektor Masing-Masing'),
('Pelayanan Perempuan & Pelayanan Laki-Laki', 'Minggu', '16:00:00', 'Gereja / Sektor'),
('Katekisasi Minggu', 'Minggu', NULL, 'Gereja (Setelah Ibadah Minggu)'),
('Ibadah Gatris', 'Senin', NULL, 'Setiap Unit'),
('Katekisasi Senin', 'Senin', '16:00:00', 'Gereja'),
('Ibadah AMGPM Ranting Rehobot', 'Jumat', NULL, 'Gedung Gereja Rehobot'),
('Ibadah Unit', 'Sabtu', NULL, 'Unit Masing-Masing');

INSERT INTO jadwal_pelayanan (nama_pelayanan, hari, keterangan_minggu, lokasi) VALUES
('Ibadah Sektor', 'Minggu', 'Minggu ke-4', 'Sektor Masing-Masing');


-- Akun Admin default: username admin, password Admin123
-- Hash password dibuat menggunakan password_hash() PHP.
INSERT INTO users (username, email, password, nama_lengkap, role)
VALUES ('admin', 'admin@gpmwaru.local', '$2y$12$aOmj3E9YrHhcPfn9aGLKBuHmk8NFslitgUxjqRqZiaPAwUIfZ4OuW', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE
  password = VALUES(password),
  nama_lengkap = VALUES(nama_lengkap),
  role = VALUES(role);
