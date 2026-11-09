
<?php
// ==================================================
// KELOLA TEMA PELAYANAN BULANAN DAN MINGGUAN
// File: PAGES/kelola-temapelayanan.php
// ==================================================

// 1. KONEKSI DATABASE
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia.");
}

// 2. FUNGSI ESCAPE
if (!function_exists('escTema')) {
    function escTema($nilai) {
        return htmlspecialchars(
            $nilai ?? '',
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

// 3. FUNGSI SIMPAN / UPDATE TEMA
if (!function_exists('simpanTemaPelayanan')) {
    function simpanTemaPelayanan($conn, $jenis, $judul, $periode) {

        $cek = mysqli_prepare(
            $conn,
            "SELECT id FROM tema_pelayanan
             WHERE jenis = ? LIMIT 1"
        );

        if (!$cek) {
            throw new Exception("Gagal memeriksa tema.");
        }

        mysqli_stmt_bind_param($cek, "s", $jenis);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        $sudahAda = mysqli_stmt_num_rows($cek) > 0;
        mysqli_stmt_close($cek);

        if ($sudahAda) {

            // UPDATE TEMA LAMA
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE tema_pelayanan
                 SET judul_tema = ?,
                     keterangan_periode = ?
                 WHERE jenis = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $judul,
                $periode,
                $jenis
            );

        } else {

            // INSERT TEMA BARU
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO tema_pelayanan
                 (jenis, judul_tema, keterangan_periode)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $jenis,
                $judul,
                $periode
            );
        }

        if (!$stmt) {
            throw new Exception("Gagal menyiapkan penyimpanan tema.");
        }

        $berhasil = mysqli_stmt_execute($stmt);

        if (!$berhasil) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception($error);
        }

        mysqli_stmt_close($stmt);
    }
}

// 4. NOTIFIKASI
$pesanError = "";

// ==================================================
// 5. PROSES HAPUS KEDUA TEMA SEKALIGUS
// ==================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['hapus_semua_tema'])
) {

    mysqli_begin_transaction($conn);

    try {

        $hapus = mysqli_prepare(
            $conn,
            "DELETE FROM tema_pelayanan
             WHERE jenis IN ('bulanan', 'mingguan')"
        );

        if (!$hapus) {
            throw new Exception("Gagal menyiapkan penghapusan.");
        }

        if (!mysqli_stmt_execute($hapus)) {
            throw new Exception("Gagal menghapus tema.");
        }

        mysqli_stmt_close($hapus);

        mysqli_commit($conn);

        echo '<script>
            window.location.href =
            "index.php?page=kelola-tema&tema_deleted=1";
        </script>';

        exit;

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        $pesanError = "Gagal menghapus kedua tema pelayanan.";
    }
}

// ==================================================
// 6. PROSES EDIT / SIMPAN KEDUA TEMA SEKALIGUS
// ==================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['simpan_semua_tema'])
) {

    $judulBulanan = trim($_POST['judul_bulanan'] ?? '');
    $periodeBulanan = trim($_POST['periode_bulanan'] ?? '');

    $judulMingguan = trim($_POST['judul_mingguan'] ?? '');
    $periodeMingguan = trim($_POST['periode_mingguan'] ?? '');

    if (
        $judulBulanan === '' ||
        $periodeBulanan === '' ||
        $judulMingguan === '' ||
        $periodeMingguan === ''
    ) {

        $pesanError = "Semua kolom tema wajib diisi.";

    } else {

        mysqli_begin_transaction($conn);

        try {

            // UPDATE / SIMPAN BULANAN
            simpanTemaPelayanan(
                $conn,
                'bulanan',
                $judulBulanan,
                $periodeBulanan
            );

            // UPDATE / SIMPAN MINGGUAN
            simpanTemaPelayanan(
                $conn,
                'mingguan',
                $judulMingguan,
                $periodeMingguan
            );

            mysqli_commit($conn);

            echo '<script>
                window.location.href =
                "index.php?page=kelola-tema&tema_saved=1";
            </script>';

            exit;

        } catch (Throwable $e) {

            mysqli_rollback($conn);

            $pesanError = "Gagal menyimpan tema. Periksa database.";
        }
    }
}

// ==================================================
// 7. AMBIL DATA TEMA BULANAN
// ==================================================

$resBulanan = mysqli_query(
    $conn,
    "SELECT * FROM tema_pelayanan
     WHERE jenis = 'bulanan'
     LIMIT 1"
);

$dataBulanan = $resBulanan
    ? mysqli_fetch_assoc($resBulanan)
    : null;

// ==================================================
// 8. AMBIL DATA TEMA MINGGUAN
// ==================================================

$resMingguan = mysqli_query(
    $conn,
    "SELECT * FROM tema_pelayanan
     WHERE jenis = 'mingguan'
     LIMIT 1"
);

$dataMingguan = $resMingguan
    ? mysqli_fetch_assoc($resMingguan)
    : null;

?>

<style>

/* CONTAINER UTAMA */

.tema-container {
    width: 100%;
    box-sizing: border-box;
    margin: 25px auto;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
}

.tema-container h3 {
    color: #172f50;
    font-size: 22px;
    margin-bottom: 8px;
}

.tema-deskripsi {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 25px;
}

/* GRID TEMA */

.tema-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* KARTU TEMA */

.tema-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 22px;
    min-width: 0;
}

.tema-section h4 {
    color: #17365d;
    font-size: 18px;
    margin: 0 0 20px;
}

/* INPUT */

.tema-field {
    margin-bottom: 17px;
}

.tema-field label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 7px;
}

.tema-field input {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
    color: #172f50;
    background: #ffffff;
    outline: none;
}

.tema-field input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.10);
}

/* TOMBOL AKSI */

.tema-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-top: 25px;
    flex-wrap: wrap;
}

.tema-btn {
    padding: 12px 22px;
    border: none;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.tema-edit {
    background: #2563eb;
    color: white;
}

.tema-edit:hover {
    background: #1d4ed8;
}

.tema-hapus {
    background: #dc2626;
    color: white;
}

.tema-hapus:hover {
    background: #b91c1c;
}

/* NOTIFIKASI */

.tema-alert {
    padding: 12px 15px;
    border-radius: 7px;
    margin-bottom: 18px;
    font-size: 14px;
}

.tema-sukses {
    background: #d1fae5;
    color: #065f46;
}

.tema-error {
    background: #fee2e2;
    color: #991b1b;
}

/* RESPONSIVE */

@media(max-width: 768px) {

    .tema-grid {
        grid-template-columns: 1fr;
    }

    .tema-actions {
        flex-direction: column;
    }

    .tema-btn {
        width: 100%;
    }
}

@media(max-width: 600px) {

    .tema-container {
        padding: 15px;
    }

    .tema-section {
        padding: 15px;
    }
}

</style>


<!-- ==================================================
     TAMPILAN KELOLA TEMA
================================================== -->

<div class="tema-container">

    <h3>
        <i class="fa-solid fa-bookmark"></i>
        Pengaturan Tema Pelayanan
    </h3>

    <p class="tema-deskripsi">
        Kelola dan perbarui tema pelayanan bulanan
        serta mingguan Jemaat GPM Waru.
    </p>


    <!-- NOTIFIKASI BERHASIL SIMPAN -->

    <?php if (isset($_GET['tema_saved'])): ?>

        <div class="tema-alert tema-sukses">
            <i class="fa-solid fa-circle-check"></i>
            Kedua tema pelayanan berhasil diperbarui!
        </div>

    <?php endif; ?>


    <!-- NOTIFIKASI BERHASIL HAPUS -->

    <?php if (isset($_GET['tema_deleted'])): ?>

        <div class="tema-alert tema-sukses">
            <i class="fa-solid fa-circle-check"></i>
            Tema bulanan dan mingguan berhasil dihapus!
        </div>

    <?php endif; ?>


    <!-- NOTIFIKASI ERROR -->

    <?php if ($pesanError !== ''): ?>

        <div class="tema-alert tema-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= escTema($pesanError) ?>
        </div>

    <?php endif; ?>


    <!-- SATU FORM EDIT / SIMPAN -->

    <form
        method="POST"
        action="index.php?page=kelola-tema"
    >

        <div class="tema-grid">

            <!-- TEMA BULANAN -->

            <div class="tema-section">

                <h4>
                    <i class="fa-solid fa-calendar-days"></i>
                    Tema Bulanan
                </h4>

                <div class="tema-field">

                    <label>Judul Tema Bulanan</label>

                    <input
                        type="text"
                        name="judul_bulanan"
                        value="<?= escTema($dataBulanan['judul_tema'] ?? '') ?>"
                        placeholder="Contoh: Bulan Keluarga GPM"
                        required
                    >

                </div>

                <div class="tema-field">

                    <label>Keterangan / Bulan</label>

                    <input
                        type="text"
                        name="periode_bulanan"
                        value="<?= escTema($dataBulanan['keterangan_periode'] ?? '') ?>"
                        placeholder="Contoh: Bulan November 2026"
                        required
                    >

                </div>

            </div>


            <!-- TEMA MINGGUAN -->

            <div class="tema-section">

                <h4>
                    <i class="fa-solid fa-bullhorn"></i>
                    Tema Mingguan
                </h4>

                <div class="tema-field">

                    <label>Judul Tema Mingguan</label>

                    <input
                        type="text"
                        name="judul_mingguan"
                        value="<?= escTema($dataMingguan['judul_tema'] ?? '') ?>"
                        placeholder="Contoh: Hidup Dalam Kasih Kristus"
                        required
                    >

                </div>

                <div class="tema-field">

                    <label>Periode / Tanggal Minggu Ini</label>

                    <input
                        type="text"
                        name="periode_mingguan"
                        value="<?= escTema($dataMingguan['keterangan_periode'] ?? '') ?>"
                        placeholder="Contoh: 1-7 November 2026"
                        required
                    >

                </div>

            </div>

        </div>


        <!-- SATU TOMBOL EDIT / SIMPAN DAN SATU TOMBOL HAPUS -->

        <div class="tema-actions">

            <!-- TOMBOL EDIT / SIMPAN -->

            <button
                type="submit"
                name="simpan_semua_tema"
                value="1"
                class="tema-btn tema-edit"
            >
                <i class="fa-solid fa-pen-to-square"></i>
                Edit / Simpan Semua Tema
            </button>

        </form>


        <!-- FORM HAPUS KEDUA TEMA -->

        <form
            method="POST"
            action="index.php?page=kelola-tema"
            onsubmit="return confirm('PERINGATAN! Apakah Anda yakin ingin menghapus TEMA BULANAN DAN MINGGUAN sekaligus? Data yang dihapus tidak dapat dikembalikan.');"
        >

            <button
                type="submit"
                name="hapus_semua_tema"
                value="1"
                class="tema-btn tema-hapus"
            >
                <i class="fa-solid fa-trash"></i>
                Hapus Semua Tema
            </button>

        </form>

    </div>

</div>
<!-- TOMBOL KEMBALI KE DASHBOARD -->
<div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 20px;">
    <a href="INDEX.php?page=admin-dashboard" style="display: inline-flex; align-items: center; gap: 8px; background: #e5e7eb; color: #374151; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>