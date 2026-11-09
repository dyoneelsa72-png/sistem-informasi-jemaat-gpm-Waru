<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: INDEX.php?page=login');
    exit;
}
?>

<div class="gpm-feature-grid">

  <a class="gpm-feature-card gpm-purple" href="INDEX.php?page=kelola-struktur">
    <span class="gpm-feature-icon"><i class="fa-solid fa-people-group"></i></span>
    <span class="gpm-feature-copy"><strong>Kelola Struktur Organisasi & Profil Pendeta </strong><small>Perbarui nama, jabatan, sektor, dan unit pelayanan.</small></span>
    <span class="gpm-feature-arrow"><i class="fa-solid fa-arrow-right"></i></span>
  </a>
  <a class="gpm-feature-card gpm-purple" href="INDEX.php?page=kelola-jumlahjiwa">
    <span class="gpm-feature-icon"><i class="fa-solid fa-users"></i></span>
    <span class="gpm-feature-copy"><strong>Kelola Statistik Jemaat & Visi-Misi</strong><small>Perbarui data statistik jumlah kepala keluarga dan jiwa jemaat.</small></span>
    <span class="gpm-feature-arrow"><i class="fa-solid fa-arrow-right"></i></span>
  </a>
  <a class="gpm-feature-card gpm-green" href="INDEX.php?page=kelola-jadwal">
    <span class="gpm-feature-icon"><i class="fa-solid fa-calendar-days"></i></span>
    <span class="gpm-feature-copy"><strong>Kelola Jadwal & Warta Jemaat</strong><small>Edit dan perbarui jadwal pelayanan setiap minggu.</small></span>
    <span class="gpm-feature-arrow"><i class="fa-solid fa-arrow-right"></i></span>
  </a>
  <a class="gpm-feature-card gpm-orange" href="INDEX.php?page=kelola-fasilitas">
    <span class="gpm-feature-icon"><i class="fa-solid fa-building"></i></span>
    <span class="gpm-feature-copy"><strong>Kelola Fasilitas</strong><small>Tambah, edit, hapus fasilitas, dan unggah foto.</small></span>
    <span class="gpm-feature-arrow"><i class="fa-solid fa-arrow-right"></i></span>
  </a>
<a class="gpm-feature-card gpm-cyan" href="INDEX.php?page=kelola-tema">
    <span class="gpm-feature-icon"><i class="fa-solid fa-bookmark"></i></span>
    <span class="gpm-feature-copy"><strong>Kelola Tema Pelayanan</strong><small>Perbarui tema bulanan dan mingguan GPM Waru.</small></span>
    <span class="gpm-feature-arrow"><i class="fa-solid fa-arrow-right"></i></span>
  </a>
<a class="gpm-feature-card gpm-cyan" href="INDEX.php?page=kelola-dokumentasi">
    <div class="gpm-feature-icon"><i class="fa-solid fa-images"></i>
    </div><div class="gpm-feature-copy"><strong>Kelola Dokumentasi Pembangunan Dalam Jemaat</strong><small>Periksa dokumentasi, foto kegiatan jemaat, proses pembangunan sertaedit/update.</small></span>
    <span class="gpm-feature-arrow"><i class="fa-solid fa-arrow-right"></i></span>
    </div>
</a>
  </a>
</div>
