
<?php
// ============================================================
// HALAMAN BERANDA JEMAAT GPM WARU
// File: pages/beranda.php
// ============================================================

// ------------------------------------------------------------
// 1. SESSION DAN KONEKSI DATABASE
// ------------------------------------------------------------

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo '<div style="padding:20px;color:red;">
        Koneksi database tidak tersedia. Periksa koneksi pada index.php.
    </div>';
    return;
}

// ------------------------------------------------------------
// 2. FUNGSI PENGAMANAN TEKS
// ------------------------------------------------------------

if (!function_exists('escBeranda')) {
    function escBeranda($teks) {
        return htmlspecialchars(
            (string)($teks ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

// ------------------------------------------------------------
// 3. NILAI AWAL
// ------------------------------------------------------------

$strukturPdfData = null;
$temaBulanan = null;
$temaMingguan = null;
$dataStatistik = null;
$profil = [];
$dataPendeta = [];
$dataVM = null;

// ------------------------------------------------------------
// 4. DATA STRUKTUR ORGANISASI
// ------------------------------------------------------------

$resStruktur = mysqli_query(
    $conn,
    "SELECT * FROM struktur_organisasi_pdf
     ORDER BY id DESC LIMIT 1"
);

if ($resStruktur) {
    $strukturPdfData = mysqli_fetch_assoc($resStruktur);
}

// ------------------------------------------------------------
// 5. DATA TEMA BULANAN DAN MINGGUAN
// ------------------------------------------------------------

$queryTema = mysqli_query(
    $conn,
    "SELECT id, jenis, judul_tema, keterangan_periode
     FROM tema_pelayanan
     WHERE jenis IN ('bulanan', 'mingguan')
     ORDER BY id DESC"
);

if ($queryTema) {
    while ($rowTema = mysqli_fetch_assoc($queryTema)) {

        if (
            $rowTema['jenis'] === 'bulanan' &&
            $temaBulanan === null
        ) {
            $temaBulanan = $rowTema;
        }

        if (
            $rowTema['jenis'] === 'mingguan' &&
            $temaMingguan === null
        ) {
            $temaMingguan = $rowTema;
        }
    }
}

// ------------------------------------------------------------
// 6. DATA STATISTIK JEMAAT
// ------------------------------------------------------------

$resStatistik = mysqli_query(
    $conn,
    "SELECT * FROM statistik_jemaat
     ORDER BY id DESC LIMIT 1"
);

if ($resStatistik && mysqli_num_rows($resStatistik) > 0) {
    $dataStatistik = mysqli_fetch_assoc($resStatistik);
}

$totalKK = (int)($dataStatistik['jumlah_kk'] ?? 0);
$totalJiwa = (int)($dataStatistik['jumlah_jiwa'] ?? 0);
$jumlahLaki = (int)($dataStatistik['jumlah_laki'] ?? 0);
$jumlahPerempuan = (int)($dataStatistik['jumlah_perempuan'] ?? 0);

// ------------------------------------------------------------
// 7. DATA PROFIL JEMAAT
// ------------------------------------------------------------

$resProfil = mysqli_query(
    $conn,
    "SELECT * FROM profil_jemaat
     ORDER BY id DESC LIMIT 1"
);

if ($resProfil && mysqli_num_rows($resProfil) > 0) {
    $profil = mysqli_fetch_assoc($resProfil);
}

$alamatJemaat = $profil['alamat'] ??
    'Negeri Waru, Dataran Waipia, Kec. Teon Nila Serua (TNS), Kab. Maluku Tengah, Maluku.';

$mapsUrl = $profil['maps_url'] ??
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3981.5!2d129.0!3d-3.0!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwMTgnMDAuMCJTIDEyOcKwMDAnMDAuMCJF!5e0!3m2!1sid!2sid';

// ------------------------------------------------------------
// 8. DATA FILE STRUKTUR ORGANISASI
// ------------------------------------------------------------

$fileStruktur = '';
$fileExt = '';
$fileJudul = '';

if (!empty($strukturPdfData['pdf_file'])) {

    $namaFileStruktur = basename($strukturPdfData['pdf_file']);

    $fileStruktur = 'uploads/struktur/' . $namaFileStruktur;

    $fileExt = strtolower(
        pathinfo($namaFileStruktur, PATHINFO_EXTENSION)
    );

    $fileJudul = $strukturPdfData['judul'] ??
        'Struktur Organisasi Jemaat';
}

// ------------------------------------------------------------
// 9. DATA PENDETA DARI DATABASE
// ------------------------------------------------------------

$queryPendeta = mysqli_query(
    $conn,
    "SELECT id, nama, jabatan, foto, keterangan
     FROM pendeta
     ORDER BY id ASC"
);

if ($queryPendeta) {
    while ($rowPendeta = mysqli_fetch_assoc($queryPendeta)) {
        $dataPendeta[] = $rowPendeta;
    }
}

// ------------------------------------------------------------
// 10. DATA VISI DAN MISI DARI DATABASE
// ------------------------------------------------------------

$visiBeranda = 'Visi belum diatur oleh admin.';
$misiBeranda = 'Misi belum diatur oleh admin.';

$queryVM = mysqli_query(
    $conn,
    "SELECT id, visi, misi
     FROM riwayat_visimisi
     ORDER BY id DESC
     LIMIT 1"
);

if ($queryVM && mysqli_num_rows($queryVM) > 0) {

    $dataVM = mysqli_fetch_assoc($queryVM);

    $visiBeranda = $dataVM['visi'] ?? $visiBeranda;
    $misiBeranda = $dataVM['misi'] ?? $misiBeranda;
}

?>

<!-- ============================================================
     11. GOOGLE FONTS, TAILWIND DAN AOS
============================================================ -->

<script src="https://cdn.tailwindcss.com"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: {
                    dark: '#0f172a',
                    emerald: '#064e3b',
                    gold: '#d97706',
                    goldlight: '#fbbf24',
                    cream: '#f8fafc'
                }
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
                serif: ['Playfair Display', 'serif']
            }
        }
    }
}
</script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>

html {
    scroll-behavior: smooth;
}

.text-gold-gradient {
    background: linear-gradient(to right, #fbbf24, #d97706);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.glass-modal {
    background: rgba(15, 23, 42, 0.90);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
}

.gold-divider {
    height: 1px;
    background: linear-gradient(
        to right,
        transparent,
        #d97706,
        transparent
    );
}

#strukturModal {
    display: none;
    position: fixed;
    z-index: 9999;
    inset: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    align-items: center;
    justify-content: center;
}

.struktur-modal-box {
    background: white;
    width: 90%;
    max-width: 1000px;
    max-height: 92vh;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#strukturViewerContainer {
    width: 100%;
    height: 75vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

#sector-modal-content {
    transition: all 0.3s ease;
}

.card-pendeta {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.card-pendeta:hover {
    transform: translateY(-5px);
    border-color: #d97706;
    box-shadow: 0 12px 25px rgba(0,0,0,0.10);
}

.visi-misi-card {
    transition: all 0.3s ease;
}

.visi-misi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
}

</style>


<!-- ============================================================
     12. HERO SECTION
============================================================ -->

<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-brand-dark -mt-16">

    <div
        class="absolute inset-0 bg-cover bg-center bg-fixed opacity-40"
        style="background-image:url('greja_waru.jpeg');">
    </div>

    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/50 to-transparent"></div>

    <div class="relative z-10 text-center px-6 max-w-5xl mx-auto pt-28 pb-16">

        <img
            src="LOGO GPM 2.png"
            alt="Logo GPM Waru"
            class="w-28 mx-auto mb-6 drop-shadow-2xl hover:scale-105 transition-transform duration-300"
            data-aos="fade-down"
        >

        <h1
            class="text-4xl md:text-6xl font-serif font-bold text-white mb-4 leading-tight"
            data-aos="fade-up"
        >
            JEMAAT <br>

            <span class="text-gold-gradient italic">
                GPM WARU
            </span>
        </h1>

        <span class="inline-block py-1 px-4 border border-brand-goldlight/50 rounded-full text-brand-goldlight text-xs tracking-[0.3em] uppercase mb-4">
            Gereja Protestan Maluku
        </span>

        <p class="text-gray-300 text-sm md:text-base font-light tracking-wide max-w-2xl mx-auto mb-8">

            Pelayanan Digital Persekutuan, Kesaksian, dan Pelayanan Jemaat

            <br>

            <span class="text-xs text-brand-goldlight uppercase tracking-widest">
                Kec. Teon Nila Serua (TNS), Kab. Maluku Tengah
            </span>

        </p>

        <div
            class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto text-left"
            data-aos="fade-up"
        >

            <!-- TEMA BULANAN -->

            <div class="rounded-2xl p-5 bg-white/5 backdrop-blur-sm border border-white/10">

                <div class="flex items-center gap-2 text-blue-400 text-xs uppercase tracking-widest font-semibold mb-3">

                    <i class="fa-solid fa-calendar-days"></i>

                    Tema Pelayanan Bulanan

                </div>

                <h2 class="text-white text-base md:text-lg font-serif mb-3">

                    <?= $temaBulanan
                        ? escBeranda($temaBulanan['judul_tema'])
                        : 'Silakan masukkan tema bulanan'; ?>

                </h2>

                <div class="text-gray-300 text-xs pt-3 border-t border-white/10">

                    Periode:

                    <?= $temaBulanan
                        ? escBeranda($temaBulanan['keterangan_periode'])
                        : 'Bulan Berjalan'; ?>

                </div>

            </div>

            <!-- TEMA MINGGUAN -->

            <div class="rounded-2xl p-5 bg-white/5 backdrop-blur-sm border border-white/10">

                <div class="flex items-center gap-2 text-amber-400 text-xs uppercase tracking-widest font-semibold mb-3">

                    <i class="fa-solid fa-bullhorn"></i>

                    Tema Pelayanan / Warta Minggu Ini

                </div>

                <h2 class="text-white text-base md:text-lg font-serif mb-3">

                    <?= $temaMingguan
                        ? escBeranda($temaMingguan['judul_tema'])
                        : 'Silakan masukkan tema mingguan'; ?>

                </h2>

                <div class="text-gray-300 text-xs pt-3 border-t border-white/10">

                    Periode:

                    <?= $temaMingguan
                        ? escBeranda($temaMingguan['keterangan_periode'])
                        : 'Minggu Berjalan'; ?>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ============================================================
     13. STATISTIK JEMAAT
============================================================ -->

<section class="py-16 bg-white relative -mt-10 z-20 rounded-t-3xl shadow-2xl max-w-7xl mx-auto px-6">

    <div class="text-center mb-12">

        <h2 class="text-xs font-semibold tracking-[0.2em] text-brand-gold uppercase mb-2">
            Data Kependudukan
        </h2>

        <h3 class="text-3xl font-serif text-brand-dark">
            Statistik Jiwa & Wilayah
        </h3>

        <div class="gold-divider w-20 mx-auto mt-4"></div>

    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="p-6 bg-brand-cream border rounded-2xl text-center shadow-sm">

            <i class="fa-solid fa-users text-4xl text-brand-gold mb-3 block"></i>

            <h4 class="text-3xl font-bold text-brand-dark">
                <?= number_format($totalJiwa, 0, ',', '.'); ?>
            </h4>

            <p class="text-xs text-slate-500 uppercase">
                Total Jiwa Jemaat
            </p>

        </div>

        <div class="p-6 bg-brand-cream border rounded-2xl text-center shadow-sm">

            <i class="fa-solid fa-house-chimney text-4xl text-emerald-700 mb-3 block"></i>

            <h4 class="text-3xl font-bold text-brand-dark">
                <?= number_format($totalKK, 0, ',', '.'); ?>
            </h4>

            <p class="text-xs text-slate-500 uppercase">
                Kepala Keluarga (KK)
            </p>

        </div>

        <div class="p-6 bg-brand-cream border rounded-2xl text-center shadow-sm">

            <i class="fa-solid fa-person text-4xl text-blue-600 mb-3 block"></i>

            <h4 class="text-3xl font-bold text-brand-dark">
                <?= number_format($jumlahLaki, 0, ',', '.'); ?>
            </h4>

            <p class="text-xs text-slate-500 uppercase">
                Laki-Laki (L)
            </p>

        </div>

        <div class="p-6 bg-brand-cream border rounded-2xl text-center shadow-sm">

            <i class="fa-solid fa-person-dress text-4xl text-pink-600 mb-3 block"></i>

            <h4 class="text-3xl font-bold text-brand-dark">
                <?= number_format($jumlahPerempuan, 0, ',', '.'); ?>
            </h4>

            <p class="text-xs text-slate-500 uppercase">
                Perempuan (P)
            </p>

        </div>

    </div>

</section>


<!-- ============================================================
     14. PROFIL DAN SEJARAH JEMAAT
============================================================ -->

<section id="profil" class="py-24 bg-brand-cream">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- SEJARAH -->

            <div data-aos="fade-right">

                <h2 class="text-xs font-semibold tracking-[0.2em] text-brand-gold uppercase mb-3">
                    Mengenal Kami
                </h2>

                <h3 class="text-4xl font-serif text-brand-dark leading-tight mb-6">
                    Profil & Sejarah <br>
                    Jemaat GPM Waru
                </h3>

                <p class="text-slate-600 leading-relaxed mb-4 text-justify">

                    Jemaat Gereja Protestan Maluku (GPM) Waru merupakan jemaat evakuasi dari Negeri Waru, Pulau Serua (Kepulauan TNS), yang resmi mendiami Dataran Waipia, Pulau Seram sejak <strong>16 April 1982</strong> akibat ancaman aktivitas gunung berapi.

                </p>

                <p class="text-slate-600 leading-relaxed mb-6 text-justify">

                    Secara administratif berada di Kecamatan Teon Nila Serua, Kabupaten Maluku Tengah. Pelayanan jemaat terbagi dalam 4 Sektor dan 12 Unit Pelayanan.

                </p>

                <div class="grid grid-cols-2 gap-4 pt-6 border-t border-slate-200">

                    <div class="flex items-center gap-3">

                        <i class="fa-solid fa-church text-2xl text-brand-gold"></i>

                        <div>
                            <h4 class="font-semibold text-brand-dark text-sm">
                                4 Sektor
                            </h4>

                            <p class="text-xs text-slate-500">
                                Wilayah Pelayanan
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-3">

                        <i class="fa-solid fa-people-group text-2xl text-brand-gold"></i>

                        <div>
                            <h4 class="font-semibold text-brand-dark text-sm">
                                12 Unit
                            </h4>

                            <p class="text-xs text-slate-500">
                                Unit Pelayanan Jemaat
                            </p>
                        </div>

                    </div>

                </div>

            </div>

            <!-- FOTO GEREJA -->

            <div data-aos="fade-left">

                <img
                    src="greja_waru.jpeg"
                    alt="Gedung Gereja GPM Waru"
                    class="w-full h-[450px] object-cover rounded-tl-[80px] rounded-br-[80px] shadow-2xl"
                >

            </div>

        </div>

    </div>

</section>


<!-- ============================================================
     15. PROFIL PENDETA DAN STRUKTUR ORGANISASI
============================================================ -->

<section class="py-24 bg-white border-t border-slate-200">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <div class="text-center mb-16" data-aos="fade-up">

            <h2 class="text-xs font-semibold tracking-[0.2em] text-brand-gold uppercase mb-2">
                Kepemimpinan Jemaat
            </h2>

            <h3 class="text-4xl font-serif text-brand-dark">
                Profil Pendeta & Struktur Organisasi Jemaat
            </h3>

            <div class="gold-divider w-24 mx-auto mt-4"></div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- KOLOM PENDETA -->

            <div class="lg:col-span-1 space-y-6">

                <?php if (!empty($dataPendeta)): ?>

                    <?php foreach ($dataPendeta as $p): ?>

                        <?php

                        $namaFoto = basename($p['foto'] ?? '');

                        $lokasiFoto = '';

                        if (!empty($namaFoto)) {
                            $lokasiFoto = __DIR__ .
                                '/../uploads/pendeta/' .
                                $namaFoto;
                        }

                        if (
                            !empty($namaFoto) &&
                            is_file($lokasiFoto)
                        ) {

                            $urlFoto = 'uploads/pendeta/' .
                                rawurlencode($namaFoto);

                        } elseif (
                            !empty($p['foto']) &&
                            filter_var(
                                $p['foto'],
                                FILTER_VALIDATE_URL
                            )
                        ) {

                            $urlFoto = $p['foto'];

                        } else {

                            $urlFoto = 'assets/img/logo-default.png';

                        }

                        ?>

                        <div
                            class="bg-brand-cream p-6 rounded-2xl shadow-sm border border-slate-100 text-center hover:shadow-lg transition-all duration-300"
                            data-aos="fade-right"
                        >

                            <div class="w-24 h-24 mx-auto mb-6 rounded-full overflow-hidden border-4 border-brand-gold/20 shadow-inner">

                                <img
                                    src="<?= escBeranda($urlFoto); ?>"
                                    alt="<?= escBeranda($p['nama'] ?? 'Pendeta'); ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='assets/img/logo-default.png'"
                                >

                            </div>

                            <span class="inline-block py-1 px-3 bg-brand-gold/10 text-brand-gold text-[9px] font-bold tracking-widest uppercase rounded-full mb-3">

                                <?= escBeranda($p['jabatan'] ?? 'Pendeta Jemaat'); ?>

                            </span>

                            <h4 class="text-xl font-serif text-brand-dark mb-2">

                                <?= escBeranda($p['nama'] ?? 'Nama Pendeta'); ?>

                            </h4>

                            <p class="text-xs text-slate-500 leading-relaxed">

                                <?= escBeranda($p['keterangan'] ?? 'Pendeta Jemaat GPM Waru'); ?>

                            </p>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="bg-brand-cream p-8 rounded-2xl text-center">

                        <i class="fa-solid fa-user-tie text-4xl text-slate-300 mb-3"></i>

                        <p class="text-slate-500">
                            Profil pendeta belum tersedia.
                        </p>

                    </div>

                <?php endif; ?>

            </div>


            <!-- KOLOM STRUKTUR ORGANISASI -->

            <div
                class="lg:col-span-2 bg-brand-cream p-6 rounded-2xl shadow-sm border border-slate-100 text-center"
                data-aos="fade-left"
            >

                <h4 class="text-xl font-serif text-brand-dark mb-6 flex items-center justify-center gap-2">

                    <i class="fa-solid fa-sitemap text-brand-gold"></i>

                    Bagan & Struktur Organisasi Jemaat

                </h4>

                <?php if (
                    $strukturPdfData &&
                    !empty($fileStruktur) &&
                    is_file(__DIR__ . '/../' . $fileStruktur)
                ): ?>

                    <div class="relative group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-inner p-2">

                        <?php if ($fileExt === 'pdf'): ?>

                            <div
                                class="relative w-full h-[450px] overflow-hidden cursor-pointer"
                                onclick="bukaModalStruktur(
                                    <?= htmlspecialchars(json_encode($fileStruktur), ENT_QUOTES, 'UTF-8'); ?>,
                                    <?= htmlspecialchars(json_encode($fileJudul), ENT_QUOTES, 'UTF-8'); ?>
                                )"
                            >

                                <iframe
                                    src="<?= escBeranda($fileStruktur); ?>#toolbar=0&navpanes=0&scrollbar=0"
                                    style="width:100%;height:100%;border:none;pointer-events:none;"
                                    title="Pratinjau Struktur Organisasi"
                                ></iframe>

                                <div class="absolute inset-0 bg-black/0 hover:bg-black/10 transition-colors flex items-end justify-center pb-4">

                                    <span class="bg-brand-dark/80 text-white text-xs px-4 py-2 rounded-full shadow-lg">

                                        <i class="fa-solid fa-magnifying-glass-plus"></i>

                                        Klik untuk memperbesar

                                    </span>

                                </div>

                            </div>

                        <?php else: ?>

                            <div
                                class="relative w-full max-h-[500px] overflow-hidden cursor-pointer flex items-center justify-center"
                                onclick="bukaModalStruktur(
                                    <?= htmlspecialchars(json_encode($fileStruktur), ENT_QUOTES, 'UTF-8'); ?>,
                                    <?= htmlspecialchars(json_encode($fileJudul), ENT_QUOTES, 'UTF-8'); ?>
                                )"
                            >

                                <img
                                    src="<?= escBeranda($fileStruktur); ?>"
                                    alt="Struktur Organisasi Jemaat"
                                    class="w-full h-auto max-h-[480px] object-contain rounded-lg"
                                >

                                <div class="absolute inset-0 bg-black/0 hover:bg-black/20 transition-colors flex items-end justify-center pb-4 opacity-0 hover:opacity-100">

                                    <span class="bg-brand-dark/80 text-white text-xs px-4 py-2 rounded-full shadow-lg">

                                        <i class="fa-solid fa-magnifying-glass-plus"></i>

                                        Klik untuk memperbesar

                                    </span>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                    <p class="text-xs text-slate-500 mt-4">
                        <?= escBeranda($fileJudul); ?>
                    </p>

                <?php else: ?>

                    <div class="py-20 text-slate-400 italic bg-white rounded-xl border border-dashed border-slate-200">

                        <i class="fa-solid fa-file-circle-exclamation text-3xl mb-3 block text-slate-300"></i>

                        Dokumen atau gambar struktur organisasi belum diunggah oleh admin.

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</section>


<!-- ============================================================
     16. LOKASI DAN GOOGLE MAPS
============================================================ -->

<section class="py-24 bg-white">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <div class="grid lg:grid-cols-2 gap-12 items-center">

            <div data-aos="fade-right">

                <h2 class="text-xs font-semibold tracking-[0.2em] text-brand-gold uppercase mb-3">
                    Peta Wilayah
                </h2>

                <h3 class="text-4xl font-serif text-brand-dark mb-6">
                    Lokasi & Sekretariat Jemaat
                </h3>

                <p class="text-slate-600 leading-relaxed mb-6">

                    Gedung Gereja dan Kantor Sekretariat Jemaat GPM Waru terletak di kawasan Dataran Waipia, Pulau Seram, Kabupaten Maluku Tengah. Kunjungi kami untuk pelayanan administrasi surat-menyurat maupun persekutuan doa.

                </p>

                <div class="flex items-start gap-3 text-slate-700 text-sm">

                    <i class="fa-solid fa-location-dot text-brand-gold text-lg mt-1"></i>

                    <div>

                        <strong>Alamat Lengkap:</strong>

                        <br>

                        <?= escBeranda($alamatJemaat); ?>

                    </div>

                </div>

            </div>

            <div
                class="rounded-2xl overflow-hidden shadow-xl border h-[400px]"
                data-aos="fade-left"
            >

                <iframe
                    src="<?= escBeranda($mapsUrl); ?>"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi Jemaat GPM Waru"
                ></iframe>

            </div>

        </div>

    </div>

</section>


<!-- ============================================================
     17. EMPAT SEKTOR PELAYANAN
============================================================ -->

<section id="sektor" class="py-24 bg-brand-dark">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <div class="text-center mb-16">

            <h2 class="text-xs font-semibold tracking-[0.2em] text-brand-goldlight uppercase mb-2">
                Pilar Persekutuan
            </h2>

            <h3 class="text-4xl font-serif text-white">
                4 Sektor Pelayanan
            </h3>

            <div class="gold-divider w-24 mx-auto mt-6"></div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <?php

            $sektor = [

                [
                    'Pniel',
                    'Wajah Tuhan',
                    'Namamu tidak akan disebut lagi Yakub, tetapi Israel, sebab engkau telah bergumul melawan Allah dan manusia, dan engkau menang. — Kejadian 32:28. Sektor Pniel menjadi tempat pergumulan doa dan pelayanan jemaat.',
                    'fa-praying-hands'
                ],

                [
                    'Maranatha',
                    'Tuhan Datanglah',
                    'Maranatha! — 1 Korintus 16:22. Sektor Maranatha memfokuskan persekutuan pada pengharapan Kristus serta persaudaraan yang rukun.',
                    'fa-fire'
                ],

                [
                    'Ebenhaezer',
                    'Pertolongan Tuhan',
                    'Sampai di sini TUHAN telah menolong kita — 1 Samuel 7:12. Sektor Ebenhaezer adalah wadah saling menopang dalam kasih Kristus.',
                    'fa-hands-holding-child'
                ],

                [
                    'Imanuel',
                    'Allah Menyertai Kita',
                    'Sesungguhnya, anak dara itu akan mengandung dan melahirkan seorang anak laki-laki, dan mereka akan menamakan Dia Imanuel — Matius 1:23. Sektor Imanuel meneguhkan kehadiran Tuhan dalam hidup jemaat.',
                    'fa-dove'
                ]

            ];

            foreach ($sektor as $s):

            ?>

                <div
                    onclick='openSectorModal(
                        <?= json_encode($s[0], JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                        <?= json_encode($s[1], JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                        <?= json_encode($s[2], JSON_HEX_APOS | JSON_HEX_QUOT); ?>
                    )'
                    class="p-8 border border-slate-700/60 hover:border-brand-goldlight rounded-2xl bg-slate-800/40 hover:bg-slate-800 transition-all cursor-pointer text-center"
                >

                    <i class="fa-solid <?= escBeranda($s[3]); ?> text-3xl text-brand-goldlight mb-4 block"></i>

                    <h4 class="text-2xl font-serif text-white mb-2">
                        <?= escBeranda($s[0]); ?>
                    </h4>

                    <p class="text-xs text-slate-400 uppercase tracking-widest">
                        Penatua & Pengurus Sektor
                    </p>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>


<!-- ============================================================
     18. VISI DAN MISI JEMAAT DARI DATABASE
============================================================ -->

<section id="visi-misi" class="py-24 bg-white">

    <div class="max-w-5xl mx-auto px-6">

        <div class="text-center mb-16" data-aos="fade-up">

            <h2 class="text-xs font-semibold tracking-[0.2em] text-brand-gold uppercase mb-2">
                Arah Pelayanan
            </h2>

            <h3 class="text-4xl font-serif text-brand-dark">
                Visi & Misi Jemaat
            </h3>

            <div class="gold-divider w-20 mx-auto mt-4"></div>

            <p class="text-sm text-slate-500 mt-4">
                Visi dan Misi Pelayanan Jemaat GPM Waru
            </p>

        </div>

        <div class="grid md:grid-cols-2 gap-10">

            <!-- VISI -->

            <div
                class="visi-misi-card p-8 border border-slate-200 rounded-2xl bg-brand-cream shadow-sm"
                data-aos="fade-right"
            >

                <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center mb-5">

                    <i class="fa-solid fa-eye text-3xl text-brand-gold"></i>

                </div>

                <h4 class="text-xl font-serif text-brand-dark mb-5">
                    VISI JEMAAT
                </h4>

                <div class="border-l-4 border-brand-gold pl-5">

                    <p class="italic text-slate-600 leading-relaxed text-justify">

                        <?= escBeranda($visiBeranda); ?>

                    </p>

                </div>

            </div>

            <!-- MISI -->

            <div
                class="visi-misi-card p-8 border border-slate-200 rounded-2xl bg-brand-cream shadow-sm"
                data-aos="fade-left"
            >

                <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center mb-5">

                    <i class="fa-solid fa-list-check text-3xl text-brand-gold"></i>

                </div>

                <h4 class="text-xl font-serif text-brand-dark mb-5">
                    MISI JEMAAT
                </h4>

                <div class="text-sm text-slate-600 leading-relaxed text-justify">

                    <?= nl2br(escBeranda($misiBeranda)); ?>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ============================================================
     19. MODAL STRUKTUR ORGANISASI
============================================================ -->

<div id="strukturModal">

    <div class="struktur-modal-box">

        <div class="px-5 py-4 bg-gray-800 text-white flex justify-between items-center">

            <h4 id="strukturModalTitle" class="text-base font-semibold">
                Pratinjau Struktur Organisasi
            </h4>

            <button
                type="button"
                onclick="tutupModalStruktur()"
                class="text-white text-2xl"
                aria-label="Tutup modal"
            >
                &times;
            </button>

        </div>

        <div class="p-4 flex-grow bg-gray-50 overflow-auto">

            <div id="strukturViewerContainer"></div>

        </div>

    </div>

</div>


<!-- ============================================================
     20. MODAL INFORMASI SEKTOR
============================================================ -->

<div
    id="sector-modal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4"
>

    <div
        class="absolute inset-0 bg-black/70 backdrop-blur-sm"
        onclick="closeSectorModal()"
    ></div>

    <div
        class="glass-modal relative w-full max-w-lg rounded-2xl p-8 text-center transform scale-95 opacity-0 transition-all duration-300"
        id="sector-modal-content"
    >

        <button
            type="button"
            onclick="closeSectorModal()"
            class="absolute top-4 right-6 text-slate-400 text-2xl"
            aria-label="Tutup modal"
        >
            &times;
        </button>

        <div class="w-16 h-16 mx-auto bg-brand-gold/20 rounded-full flex items-center justify-center mb-4">

            <i class="fa-solid fa-church text-2xl text-brand-goldlight"></i>

        </div>

        <h3 id="sector-title" class="text-3xl font-serif text-white mb-1">
            Nama Sektor
        </h3>

        <p id="sector-subtitle" class="text-xs tracking-[0.2em] text-brand-goldlight uppercase mb-4">
            Makna Sektor
        </p>

        <div class="gold-divider w-16 mx-auto mb-6"></div>

        <p id="sector-desc" class="text-slate-300 leading-relaxed italic text-sm">
            Deskripsi Sektor
        </p>

    </div>

</div>


<!-- ============================================================
     21. JAVASCRIPT AOS DAN MODAL
============================================================ -->

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>

// ------------------------------------------------------------
// ANIMASI AOS
// ------------------------------------------------------------

if (typeof AOS !== 'undefined') {

    AOS.init({
        duration: 800,
        once: true,
        easing: 'ease-in-out'
    });

}


// ------------------------------------------------------------
// MODAL STRUKTUR ORGANISASI
// ------------------------------------------------------------

function bukaModalStruktur(fileUrl, title) {

    document.getElementById('strukturModalTitle').innerText =
        'Perbesar: ' + title;

    let container =
        document.getElementById('strukturViewerContainer');

    container.innerHTML = '';

    let extension = fileUrl
        .split('?')[0]
        .split('.')
        .pop()
        .toLowerCase();

    if (extension === 'pdf') {

        let iframe = document.createElement('iframe');

        iframe.src =
            fileUrl + '#toolbar=0&navpanes=0&scrollbar=0';

        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = '1px solid #d1d5db';

        container.appendChild(iframe);

    } else {

        let img = document.createElement('img');

        img.src = fileUrl;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '100%';
        img.style.objectFit = 'contain';

        container.appendChild(img);

    }

    document.getElementById('strukturModal').style.display =
        'flex';

}


// ------------------------------------------------------------
// TUTUP MODAL STRUKTUR
// ------------------------------------------------------------

function tutupModalStruktur() {

    document.getElementById('strukturModal').style.display =
        'none';

    document.getElementById('strukturViewerContainer').innerHTML =
        '';

}


// ------------------------------------------------------------
// MODAL SEKTOR
// ------------------------------------------------------------

function openSectorModal(title, subtitle, desc) {

    document.getElementById('sector-title').innerText =
        title;

    document.getElementById('sector-subtitle').innerText =
        subtitle;

    document.getElementById('sector-desc').innerText =
        desc;

    const modal =
        document.getElementById('sector-modal');

    const content =
        document.getElementById('sector-modal-content');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(function() {

        content.classList.remove(
            'scale-95',
            'opacity-0'
        );

        content.classList.add(
            'scale-100',
            'opacity-100'
        );

    });

}


// ------------------------------------------------------------
// TUTUP MODAL SEKTOR
// ------------------------------------------------------------

function closeSectorModal() {

    const modal =
        document.getElementById('sector-modal');

    const content =
        document.getElementById('sector-modal-content');

    content.classList.remove(
        'scale-100',
        'opacity-100'
    );

    content.classList.add(
        'scale-95',
        'opacity-0'
    );

    setTimeout(function() {

        modal.classList.add('hidden');
        modal.classList.remove('flex');

    }, 300);

}


// ------------------------------------------------------------
// KLIK DI LUAR MODAL STRUKTUR
// ------------------------------------------------------------

window.addEventListener('click', function(event) {

    let modalStruktur =
        document.getElementById('strukturModal');

    if (event.target === modalStruktur) {

        tutupModalStruktur();

    }

});


// ------------------------------------------------------------
// TOMBOL ESC
// ------------------------------------------------------------

document.addEventListener('keydown', function(event) {

    if (event.key === 'Escape') {

        tutupModalStruktur();

        closeSectorModal();

    }

});

</script>
