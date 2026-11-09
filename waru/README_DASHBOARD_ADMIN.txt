PEMBARUAN DASHBOARD ADMINISTRATOR JEMAAT GPM WARU

Dashboard administrator telah diperbarui dengan layout sidebar navy,
header sederhana, banner sambutan, dan enam kartu menu.

File yang diubah:
- INCLUDE/header.php
- INCLUDE/footer.php
- PAGES/admin-dasboard.php
- ASSETS/css/admin-dashboard.css (file baru)

Database dan file SQL tidak diubah.
Sistem login, pemeriksaan role admin, dan rute menu yang sudah ada tetap dipakai.

Cara memasang:
1. Ekstrak ZIP ini ke folder project Laragon (misalnya www/jemaat1).
2. Jalankan Laragon dan buka website seperti biasa.
3. Login menggunakan akun administrator.
4. Dashboard baru tampil saat membuka INDEX.php?page=admin-dashboard.

Catatan:
Jika nama file atau rute halaman pada project Anda berbeda, sesuaikan href menu
di PAGES/admin-dasboard.php dan INCLUDE/header.php dengan rute yang tersedia.
