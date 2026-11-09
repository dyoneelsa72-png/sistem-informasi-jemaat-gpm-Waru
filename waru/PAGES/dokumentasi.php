<?php
// ==================================================
// HALAMAN PUBLIK DOKUMENTASI PEMBANGUNAN JEMAAT
// File: pages/dokumentasi.php
// ==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan koneksi database tersedia
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// Fungsi keamanan tampilan
if (!function_exists('esc_dok')) {
    function esc_dok($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Format tanggal Indonesia
if (!function_exists('format_tanggal_dok')) {
    function format_tanggal_dok($tanggal) {
        if (empty($tanggal)) return '-';

        $bulan = [
            1 => 'Januari', 'Februari', 'Maret',
            'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'
        ];

        $timestamp = strtotime($tanggal);

        if (!$timestamp) return '-';

        return date('d', $timestamp) . ' ' .
               $bulan[(int)date('n', $timestamp)] . ' ' .
               date('Y', $timestamp);
    }
}

// ==================================================
// PENCARIAN DAN FILTER
// ==================================================

$cari = trim($_GET['cari'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

if (!in_array($kategori, ['', 'kegiatan', 'pembangunan'], true)) {
    $kategori = '';
}

// ==================================================
// MENGAMBIL DATA DARI DATABASE (MENGGUNAKAN PREPARED STATEMENTS)
// ==================================================

$dataDokumentasi = [];
$totalDokumentasi = 0;

if (isset($conn) && $conn instanceof mysqli) {

    // Menghitung total seluruh dokumentasi
    $sqlTotal = "SELECT COUNT(*) AS total FROM dokumentasi_jemaat";
    $hasilTotal = mysqli_query($conn, $sqlTotal);

    if ($hasilTotal) {
        $totalDokumentasi = (int)mysqli_fetch_assoc($hasilTotal)['total'];
    }

    // Mengambil data dokumentasi dengan Prepared Statement
    $sql = "SELECT id, kategori, judul, tanggal, foto, keterangan
            FROM dokumentasi_jemaat
            WHERE 1=1";
    
    $params = [];
    $types = "";

    if ($cari !== '') {
        $sql .= " AND (judul LIKE ? OR keterangan LIKE ?)";
        $keyword = "%" . $cari . "%";
        $types .= "ss";
        $params[] = $keyword;
        $params[] = $keyword;
    }

    if ($kategori !== '') {
        $sql .= " AND kategori = ?";
        $types .= "s";
        $params[] = $kategori;
    }

    $sql .= " ORDER BY tanggal DESC, id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);

        if ($hasil) {
            while ($row = mysqli_fetch_assoc($hasil)) {
                $dataDokumentasi[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Memisahkan data berdasarkan kategori
$kegiatan = [];
$pembangunan = [];

foreach ($dataDokumentasi as $item) {
    if ($item['kategori'] === 'kegiatan') {
        $kegiatan[] = $item;
    }
    if ($item['kategori'] === 'pembangunan') {
        $pembangunan[] = $item;
    }
}
?>

<!-- ==================================================
     HEADER DOKUMENTASI
================================================== -->

<section class="dok-publik-header">
    <div class="dok-publik-icon">
        <i class="fa-regular fa-image"></i>
    </div>
    <div class="dok-publik-heading">
        <h1>Dokumentasi Pembangunan Dalam Jemaat</h1>
        <p>Kelola foto kegiatan jemaat dan perkembangan pembangunan gereja</p>
    </div>
</section>

<!-- ==================================================
     PENCARIAN, FILTER DAN TOTAL DOKUMENTASI
================================================== -->
        <span><?= $totalDokumentasi ?></span>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 14px;">
    <div style="display: inline-flex; align-items: center; gap: 8px; background: #fff; padding: 10px 18px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 3px 10px rgba(0,0,0,.04); color: #435675; font-size: 14px; font-weight: 600;">
        Total Dokumentasi:
        <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 28px; padding: 0 6px; border-radius: 8px; color: white; background: #0875df;"><?= $totalDokumentasi ?></span>
    </div>
</div>
    </div>
</form>

<!-- ==================================================
     BAGIAN FOTO KEGIATAN JEMAAT
================================================== -->

<?php if ($kategori === '' || $kategori === 'kegiatan'): ?>
<section class="dok-publik-section">
    <div class="dok-publik-section-heading">
        <h2>
            <i class="fa-solid fa-camera"></i>
            Foto Kegiatan Jemaat
        </h2>
    </div>

    <?php if (!empty($kegiatan)): ?>
        <div class="dok-publik-grid">
            <?php foreach ($kegiatan as $item): ?>
                <?php
                $namaFoto = basename($item['foto'] ?? '');
                $pathFoto = 'uploads/dokumentasi/' . $namaFoto;
                ?>
                <article class="dok-publik-card">
                    <div class="dok-publik-image">
                        <?php if ($namaFoto !== '' && file_exists($pathFoto)): ?>
                            <img
                                src="<?= esc_dok($pathFoto) ?>"
                                alt="<?= esc_dok($item['judul']) ?>"
                                loading="lazy"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                            >
                            <div class="dok-publik-no-image" style="display:none;">
                                <i class="fa-regular fa-image"></i>
                                Foto tidak tersedia
                            </div>
                        <?php else: ?>
                            <div class="dok-publik-no-image">
                                <i class="fa-regular fa-image"></i>
                                Foto tidak tersedia
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="dok-publik-content">
                        <h3><?= esc_dok($item['judul']) ?></h3>
                        <div class="dok-publik-date">
                            <i class="fa-regular fa-calendar-days"></i>
                            <?= esc_dok(format_tanggal_dok($item['tanggal'])) ?>
                        </div>
                        <?php if (!empty($item['keterangan'])): ?>
                            <p><?= esc_dok($item['keterangan']) ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="dok-publik-empty">
            <i class="fa-regular fa-images"></i>
            <p>Belum ada foto kegiatan jemaat.</p>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- ==================================================
     BAGIAN PROSES PEMBANGUNAN
================================================== -->

<?php if ($kategori === '' || $kategori === 'pembangunan'): ?>
<section class="dok-publik-section">
    <div class="dok-publik-section-heading">
        <h2>
            <i class="fa-solid fa-helmet-safety"></i>
            Dokumentasi Proses Pembangunan
        </h2>
    </div>

    <?php if (!empty($pembangunan)): ?>
        <div class="dok-publik-grid">
            <?php foreach ($pembangunan as $item): ?>
                <?php
                $namaFoto = basename($item['foto'] ?? '');
                $pathFoto = 'uploads/dokumentasi/' . $namaFoto;
                ?>
                <article class="dok-publik-card">
                    <div class="dok-publik-image">
                        <?php if ($namaFoto !== '' && file_exists($pathFoto)): ?>
                            <img
                                src="<?= esc_dok($pathFoto) ?>"
                                alt="<?= esc_dok($item['judul']) ?>"
                                loading="lazy"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                            >
                            <div class="dok-publik-no-image" style="display:none;">
                                <i class="fa-regular fa-image"></i>
                                Foto tidak tersedia
                            </div>
                        <?php else: ?>
                            <div class="dok-publik-no-image">
                                <i class="fa-regular fa-image"></i>
                                Foto tidak tersedia
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="dok-publik-content">
                        <h3><?= esc_dok($item['judul']) ?></h3>
                        <div class="dok-publik-date">
                            <i class="fa-regular fa-calendar-days"></i>
                            <?= esc_dok(format_tanggal_dok($item['tanggal'])) ?>
                        </div>
                        <?php if (!empty($item['keterangan'])): ?>
                            <p><?= esc_dok($item['keterangan']) ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="dok-publik-empty">
            <i class="fa-solid fa-person-digging"></i>
            <p>Belum ada dokumentasi proses pembangunan.</p>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (empty($dataDokumentasi)): ?>
<div class="dok-publik-empty">
    <i class="fa-solid fa-folder-open"></i>
    <p>Dokumentasi belum tersedia atau tidak ditemukan.</p>
</div>
<?php endif; ?>

<!-- ==================================================
     CSS DOKUMENTASI PUBLIK
================================================== -->

<style>
.dok-publik-header {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: 22px 28px;
    margin-bottom: 14px;
    background: #fff;
    border-radius: 16px;
    border-left: 6px solid #0875df;
    box-shadow: 0 4px 15px rgba(0,0,0,.06);
}

.dok-publik-icon {
    width: 104px;
    height: 80px;
    min-width: 104px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    border-radius: 14px;
    color: #0865cb;
    font-size: 48px;
}

.dok-publik-heading h1 {
    margin: 0 0 7px;
    color: #153566;
    font-size: 25px;
    font-weight: 800;
}

.dok-publik-heading p {
    margin: 0;
    color: #536987;
    font-size: 15px;
}

.dok-publik-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    padding: 12px;
    margin-bottom: 14px;
    background: #fff;
    border-radius: 15px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 3px 12px rgba(0,0,0,.04);
}

.dok-publik-search,
.dok-publik-filter {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    min-height: 42px;
    border: 1px solid #d9e1ed;
    border-radius: 10px;
    color: #29466f;
    background: #fff;
}

.dok-publik-search {
    flex: 1;
    min-width: 200px;
}

.dok-publik-search input,
.dok-publik-filter select {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    color: #263c5c;
    font-size: 14px;
}

.dok-publik-filter {
    min-width: 220px;
}

.dok-publik-btn-search {
    padding: 12px 20px;
    border: none;
    border-radius: 9px;
    background: #0866cf;
    color: white;
    font-weight: 700;
    cursor: pointer;
}

.dok-publik-btn-search:hover {
    background: #064fa8;
}

.dok-publik-total {
    margin-left: auto;
    color: #435675;
    font-size: 14px;
    font-weight: 600;
}

.dok-publik-total span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 32px;
    padding: 0 8px;
    margin-left: 6px;
    border-radius: 10px;
    color: white;
    background: #0875df;
}

.dok-publik-section {
    padding: 18px;
    margin-bottom: 15px;
    background: #fff;
    border: 1px solid #e5eaf1;
    border-radius: 17px;
    box-shadow: 0 4px 15px rgba(0,0,0,.05);
}

.dok-publik-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 12px;
    margin-bottom: 15px;
    border-bottom: 1px solid #dce3ec;
}

.dok-publik-section-heading h2 {
    margin: 0;
    color: #153566;
    font-size: 20px;
    font-weight: 800;
}

.dok-publik-section-heading i {
    margin-right: 10px;
}

.dok-publik-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.dok-publik-card {
    overflow: hidden;
    background: #fff;
    border: 1px solid #e1e7ef;
    border-radius: 13px;
    box-shadow: 0 3px 10px rgba(0,0,0,.06);
    transition: .25s;
}

.dok-publik-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 7px 17px rgba(0,0,0,.10);
}

.dok-publik-image {
    width: 100%;
    height: 140px;
    overflow: hidden;
    background: #e5e7eb;
}

.dok-publik-image img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dok-publik-no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 8px;
    color: #64748b;
    font-size: 14px;
}

.dok-publik-no-image i {
    font-size: 30px;
}

.dok-publik-content {
    padding: 11px 12px 14px;
}

.dok-publik-content h3 {
    margin: 0 0 7px;
    color: #173568;
    font-size: 15px;
    font-weight: 800;
}

.dok-publik-date {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #536987;
    font-size: 12px;
}

.dok-publik-content p {
    margin: 9px 0 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.6;
}

.dok-publik-empty {
    padding: 35px 15px;
    text-align: center;
    color: #718096;
}

.dok-publik-empty i {
    display: block;
    margin-bottom: 10px;
    font-size: 35px;
}

/* Tampilan tablet */
@media(max-width: 900px) {
    .dok-publik-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .dok-publik-total {
        margin-left: 0;
    }
}

/* Tampilan handphone */
@media(max-width: 600px) {
    .dok-publik-header {
        padding: 15px;
        gap: 12px;
    }
    .dok-publik-icon {
        width: 65px;
        height: 65px;
        min-width: 65px;
        font-size: 30px;
    }
    .dok-publik-heading h1 {
        font-size: 17px;
    }
    .dok-publik-heading p {
        font-size: 12px;
    }
    .dok-publik-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .dok-publik-search,
    .dok-publik-filter {
        width: 100%;
        box-sizing: border-box;
    }
    .dok-publik-total {
        text-align: right;
    }
    .dok-publik-grid {
        grid-template-columns: 1fr;
    }
    .dok-publik-section-heading h2 {
        font-size: 16px;
    }
}
</style>