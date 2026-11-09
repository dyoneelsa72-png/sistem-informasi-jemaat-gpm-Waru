SISTEM INFORMASI JEMAAT GPM WARU - PEMASANGAN LARAGON

1. Ekstrak folder jemaat/jemaat_waru ke C:\laragon\www\jemaat_waru (atau folder www yang Anda inginkan).
2. Buka Laragon dan klik Start All (Apache dan MySQL harus aktif).
3. Buka http://localhost/jemaat_waru/INDEX.php di browser.
4. Pada akses pertama, CONFIG/database.php membuat database db_waru dan mengimpor DATABASE/gpm_waru_db.sql hanya jika tabel users belum ada. Tidak ada perintah DROP dan data database yang sudah ada tidak dihapus.
5. Akun bawaan pada database baru: username admin, password Admin123. Jika db_waru sudah berisi tabel users, akun lama tetap digunakan.
6. Login admin lalu gunakan menu Arsip Surat, Struktur, dan Kelola Fasilitas. Pengajuan dari menu Layanan Surat tersimpan ke database. Unggah PDF maksimal 10 MB; foto fasilitas maksimal 5 MB.
7. Jika database db_waru sudah ada namun belum berisi tabel users, seed awal akan dimasukkan. Pastikan MySQL Laragon menggunakan user root tanpa password (konfigurasi default Laragon).

Catatan: jangan menghapus file DATABASE/gpm_waru_db.sql. Pastikan PHP memiliki izin tulis pada folder uploads (folder akan dibuat otomatis).


FITUR KELOLA JADWAL (PEMBARUAN)
- Login menggunakan akun administrator.
- Buka menu Jadwal, lalu pilih tombol Kelola / Update Jadwal atau Edit pada baris jadwal.
- Ubah hari, jam/waktu, nama pelayanan, lokasi, atau keterangan minggu lalu klik Simpan Perubahan.
- Kolom waktu_tampilan ditambahkan otomatis oleh CONFIG/database.php tanpa menghapus data jadwal lama.
- Jadwal publik akan menampilkan perubahan yang tersimpan di database.
