# ⛪ Sistem Informasi Jemaat GPM Waru

Aplikasi Sistem Informasi berbasis PHP & MySQL yang dirancang khusus untuk mempermudah pengelolaan data dan penyampaian informasi publik bagi **Jemaat GPM Waru**.

---

## 📁 Struktur Folder Proyek

```text
/gpm-waru/
│
├── 📂 ASSETS/                   # Aset Statis Aplikasi
│   ├── 📂 css/
│   │   └── style.css           # Custom Styling & Responsive CSS
│   ├── 📂 js/
│   │   └── main.js             # JavaScript Interaktif & Animasi
│   └── 📂 images/              # Galeri Gambar, Banner, & Foto Fasilitas
│
├── 📂 CONFIG/                   # Konfigurasi Utama
│   └── database.php            # Koneksi Database MySQL (PHP MySQLi)
│
├── 📂 DATABASE/                 # Skema & Data Awal Database
│   └── gpm_waru_db.sql         # Script Import SQL LENGKAP
│
├── 📂 INCLUDE/                  # Layout & Template Global
│   ├── header.php              # Navigasi & Header Website
│   └── footer.php              # Footer, Media Sosial, Alamat, & Kontak
│
├── 📂 PAGES/                    # Modul & Fitur Halaman Website
│   ├── beranda.php             # Banner Animasi, Ucapan Selamat Datang, & Statistik
│   ├── sejarah.php             # Sejarah Pembentukan Jemaat GPM Waru
│   ├── profil.php              # Profil Jemaat & Profil Ketua Majelis Jemaat (KMJ)
│   ├── visi-misi.php           # Visi dan Misi Pelayanan
│   ├── struktur.php            # Struktur Organisasi (Pendeta, Penatua, 4 Sektor, 12 Unit)
│   ├── jadwal.php              # Jadwal Pelayanan & Ibadah Minggu/Harian
│   ├── fasilitas.php           # Fasilitas (2 Gedung Gereja, 2 Pastori, Inventaris)
│   ├── galeri.php              # Galeri Foto, Video Jemaat, & Proyek Pembangunan
│   └── layanan-surat.php       # Layanan Pengajuan Surat Digital
│
├── 📄 INDEX.php                 # Router Utama Aplikasi
└── 📄 README.md                 # Dokumentasi & Panduan Aplikasi
```

---

## 🛠️ Langkah-Langkah Instalasi (XAMPP / WAMP / Laragon)

1. **Ekstrak File ZIP:**
   * Ekstrak file `.zip` ke dalam folder web server lokal Anda:
     * **XAMPP:** `C:/xampp/htdocs/gpm-waru/`
     * **Laragon:** `C:/laragon/www/gpm-waru/`

2. **Import Database MySQL:**
   * Buka browser dan buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
   * Buat Database Baru bernama: **`db_waru`**.
   * Pilih database `db_waru` $
ightarrow$ klik tab **Import**.
   * Choose file dari folder `DATABASE/gpm_waru_db.sql`.
   * Klik tombol **Go / Kirim**.

3. **Jalankan Aplikasi:**
   * Buka browser dan ketikkan alamat berikut:
     `http://localhost/gpm-waru/`

---

## 📊 Data Statistik & Informasi Jemaat GPM Waru (Hasil Pendataan):
- **Jumlah Jiwa:** 1.016.000 jiwa
- **Jumlah Kepala Keluarga (KK):** 225 KK
- **Laki-laki:** 521 | **Perempuan:** 495
- **Struktur Pelayanan:** 2 Pendeta, 4 Penatua, 4 Sektor, 12 Unit
- **Fasilitas:** 2 Gedung Gereja (Rehobot & Imanuel), 2 Gedung Pastori, Inventaris Gereja
- **Media Sosial:** Facebook (Jemaat GPM Waru)
\n\n## Akun Admin awal\n- Username: `admin`\n- Password: `Admin123`\n\nDatabase pada paket ini bernama `db_waru`. Impor `DATABASE/gpm_waru_db.sql` satu kali untuk instalasi baru. Mengimpor SQL akan membuat database dan memasukkan data awal. Jangan impor ulang ke database yang sudah berisi data penting.\n