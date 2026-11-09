<?php
// ==================================================
// KELOLA STATISTIK JUMLAH JIWA, KELUARGA, & VISI-MISI JEMAAT
// File: pages/kelola-statistikjemaat.php
// ==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan koneksi database tersedia
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia.");
}

$pesanPemberitahuan = '';
$pesanVisiMisi = '';

// ==================================================
// 1. PROSES SIMPAN / UPDATE STATISTIK JIWA & KK
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_statistik'])) {
    $jumlahKK = (int)($_POST['jumlah_kk'] ?? 0);
    $jumlahJiwa = (int)($_POST['jumlah_jiwa'] ?? 0);
    $jumlahLaki = (int)($_POST['jumlah_laki'] ?? 0);
    $jumlahPerempuan = (int)($_POST['jumlah_perempuan'] ?? 0);

    $cekData = mysqli_query($conn, "SELECT id FROM statistik_jemaat LIMIT 1");
    
    if ($cekData && mysqli_num_rows($cekData) > 0) {
        $rowLama = mysqli_fetch_assoc($cekData);
        $idLama = (int)$rowLama['id'];

        $stmtUp = mysqli_prepare($conn, "UPDATE statistik_jemaat SET jumlah_kk = ?, jumlah_jiwa = ?, jumlah_laki = ?, jumlah_perempuan = ? WHERE id = ?");
        if ($stmtUp) {
            mysqli_stmt_bind_param($stmtUp, "iiiii", $jumlahKK, $jumlahJiwa, $jumlahLaki, $jumlahPerempuan, $idLama);
            mysqli_stmt_execute($stmtUp);
            mysqli_stmt_close($stmtUp);
        }
        $aksiStatus = 'edit';
    } else {
        $stmtIns = mysqli_prepare($conn, "INSERT INTO statistik_jemaat (jumlah_kk, jumlah_jiwa, jumlah_laki, jumlah_perempuan) VALUES (?, ?, ?, ?)");
        if ($stmtIns) {
            mysqli_stmt_bind_param($stmtIns, "iiii", $jumlahKK, $jumlahJiwa, $jumlahLaki, $jumlahPerempuan);
            mysqli_stmt_execute($stmtIns);
            mysqli_stmt_close($stmtIns);
        }
        $aksiStatus = 'tambah';
    }

    echo '<script>window.location.href = "INDEX.php?page=kelola-jumlahjiwa&status=' . $aksiStatus . '";</script>';
    exit;
}

// ==================================================
// 2. PROSES SIMPAN / EDIT VISI & MISI (DENGAN RIWAYAT)
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_visimisi'])) {
    $id_edit = $_POST['id_visimisi'] ?? '';
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');

    if (!empty($id_edit)) {
        // Update data yang dipilih berdasarkan ID
        $stmtVM = mysqli_prepare($conn, "UPDATE riwayat_visimisi SET visi = ?, misi = ? WHERE id = ?");
        if ($stmtVM) {
            mysqli_stmt_bind_param($stmtVM, "ssi", $visi, $misi, $id_edit);
            mysqli_stmt_execute($stmtVM);
            mysqli_stmt_close($stmtVM);
        }
        $pesanVisiMisi = 'Visi dan Misi berhasil diperbarui.';
    } else {
        // Simpan data baru sebagai riwayat/catatan baru
        $stmtVMIns = mysqli_prepare($conn, "INSERT INTO riwayat_visimisi (visi, misi) VALUES (?, ?)");
        if ($stmtVMIns) {
            mysqli_stmt_bind_param($stmtVMIns, "ss", $visi, $misi);
            mysqli_stmt_execute($stmtVMIns);
            mysqli_stmt_close($stmtVMIns);
        }
        $pesanVisiMisi = 'Visi dan Misi baru berhasil ditambahkan dan disimpan.';
    }

    echo '<script>window.location.href = "INDEX.php?page=kelola-jumlahjiwa&status=visimisi_sukses";</script>';
    exit;
}

// Proses Hapus Riwayat Visi Misi (Opsional)
if (isset($_GET['hapus_vm'])) {
    $idHapus = (int)$_GET['hapus_vm'];
    mysqli_query($conn, "DELETE FROM riwayat_visimisi WHERE id = $idHapus");
    echo '<script>window.location.href = "INDEX.php?page=kelola-jumlahjiwa&status=vm_hapus";</script>';
    exit;
}

// Penanganan Status Pesan dari URL
$status = $_GET['status'] ?? '';
if ($status === 'tambah' || $status === 'edit') {
    $pesanPemberitahuan = 'Data statistik jumlah jiwa berhasil diperbarui.';
} elseif ($status === 'visimisi_sukses') {
    $pesanVisiMisi = 'Perubahan Visi dan Misi berhasil disimpan.';
} elseif ($status === 'vm_hapus') {
    $pesanVisiMisi = 'Riwayat Visi dan Misi berhasil dihapus.';
}

// Ambil Data untuk Diedit jika parameter ?edit_vm=ID ada
$editDataVM = ['id' => '', 'visi' => '', 'misi' => ''];
if (isset($_GET['edit_vm'])) {
    $idEditGet = (int)$_GET['edit_vm'];
    $resEdit = mysqli_query($conn, "SELECT * FROM riwayat_visimisi WHERE id = $idEditGet LIMIT 1");
    if ($resEdit && mysqli_num_rows($resEdit) > 0) {
        $editDataVM = mysqli_fetch_assoc($resEdit);
    }
}

// Ambil Data Statistik
$dataStatistik = null;
$resultList = mysqli_query($conn, "SELECT * FROM statistik_jemaat LIMIT 1");
if ($resultList && mysqli_num_rows($resultList) > 0) {
    $dataStatistik = mysqli_fetch_assoc($resultList);
}

// Ambil Semua Daftar Riwayat Visi & Misi
$listVisiMisi = mysqli_query($conn, "SELECT * FROM riwayat_visimisi ORDER BY id DESC");

function escStat($nilai) {
    return htmlspecialchars($nilai ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<style>
.stat-container {
    max-width: 100%;
    margin: 25px auto;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
}
.stat-container h3 {
    color: #172f50;
    font-size: 22px;
    margin-bottom: 8px;
}
.stat-deskripsi {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 25px;
}
.stat-form-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 30px;
}
.stat-form-card h4 {
    color: #17365d;
    font-size: 18px;
    margin: 0 0 20px;
}
.stat-field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}
.stat-field {
    margin-bottom: 17px;
}
.stat-field label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 7px;
}
.stat-field input, .stat-field textarea {
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
.stat-field input:focus, .stat-field textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.10);
}
.stat-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.stat-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 20px;
    border: none;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s;
}
.stat-btn.primary { background: #2563eb; color: #ffffff; }
.stat-btn.primary:hover { background: #1d4ed8; }
.stat-btn.warning { background: #d97706; color: #ffffff; padding: 6px 12px; font-size: 12px; }
.stat-btn.warning:hover { background: #b45309; }
.stat-btn.danger { background: #dc2626; color: #ffffff; padding: 6px 12px; font-size: 12px; }
.stat-btn.danger:hover { background: #b91c1c; }

/* Ringkasan Statistik Cards */
.stat-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}
.stat-summary-box {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    padding: 15px;
    border-radius: 9px;
    text-align: center;
}
.stat-summary-box span {
    display: block;
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 5px;
}
.stat-summary-box h3 {
    margin: 0;
    font-size: 22px;
    color: #0f172a;
}

.stat-alert {
    padding: 12px 15px;
    border-radius: 7px;
    margin-bottom: 18px;
    font-size: 14px;
}
.stat-sukses { background: #d1fae5; color: #065f46; }

/* Tabel Riwayat */
.stat-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
}
.stat-table th, .stat-table td {
    padding: 12px;
    border: 1px solid #e2e8f0;
    text-align: left;
    font-size: 14px;
    color: #1e293b;
    vertical-align: top;
}
.stat-table th {
    background: #f1f5f9;
    font-weight: 600;
}

@media(max-width: 768px) {
    .stat-field-grid, .stat-summary-grid { grid-template-columns: 1fr; gap: 10px; }
}
</style>

<div class="stat-container">

    <h3>
        <i class="fa-solid fa-users"></i>
        Kelola Statistik Jumlah Jiwa & Visi Misi Jemaat
    </h3>

    <p class="stat-deskripsi">
        Perbarui total data kependudukan keseluruhan serta Visi dan Misi jemaat GPM Waru.
    </p>

    <!-- KOTAK RINGKASAN TOTAL -->
    <div class="stat-summary-grid">
        <div class="stat-summary-box">
            <span>Total Kepala Keluarga (KK)</span>
            <h3><?= number_format($dataStatistik['jumlah_kk'] ?? 0) ?></h3>
        </div>
        <div class="stat-summary-box">
            <span>Total Jiwa Jemaat</span>
            <h3><?= number_format($dataStatistik['jumlah_jiwa'] ?? 0) ?></h3>
        </div>
        <div class="stat-summary-box">
            <span>Jumlah Laki-Laki</span>
            <h3><?= number_format($dataStatistik['jumlah_laki'] ?? 0) ?></h3>
        </div>
        <div class="stat-summary-box">
            <span>Jumlah Perempuan</span>
            <h3><?= number_format($dataStatistik['jumlah_perempuan'] ?? 0) ?></h3>
        </div>
    </div>

    <!-- NOTIFIKASI STATISTIK -->
    <?php if ($pesanPemberitahuan !== ''): ?>
        <div class="stat-alert stat-sukses">
            <i class="fa-solid fa-circle-check"></i>
            <?= escStat($pesanPemberitahuan) ?>
        </div>
    <?php endif; ?>

    <!-- FORM INPUT STATISTIK -->
    <div class="stat-form-card">
        <h4>
            <i class="fa-solid fa-pen-to-square"></i>
            Perbarui Data Statistik Jemaat
        </h4>

        <form method="POST" action="INDEX.php?page=kelola-jumlahjiwa">
            <div class="stat-field-grid">
                <div class="stat-field">
                    <label>Total Kepala Keluarga (KK)</label>
                    <input type="number" name="jumlah_kk" value="<?= escStat($dataStatistik['jumlah_kk'] ?? '') ?>" placeholder="0" min="0" required>
                </div>

                <div class="stat-field">
                    <label>Total Jiwa Keseluruhan</label>
                    <input type="number" name="jumlah_jiwa" value="<?= escStat($dataStatistik['jumlah_jiwa'] ?? '') ?>" placeholder="0" min="0" required>
                </div>

                <div class="stat-field">
                    <label>Jumlah Jiwa Laki-Laki</label>
                    <input type="number" name="jumlah_laki" value="<?= escStat($dataStatistik['jumlah_laki'] ?? '') ?>" placeholder="0" min="0" required>
                </div>

                <div class="stat-field">
                    <label>Jumlah Jiwa Perempuan</label>
                    <input type="number" name="jumlah_perempuan" value="<?= escStat($dataStatistik['jumlah_perempuan'] ?? '') ?>" placeholder="0" min="0" required>
                </div>
            </div>

            <div class="stat-actions">
                <button type="submit" name="simpan_statistik" class="stat-btn primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan Statistik
                </button>
            </div>
        </form>
    </div>

    <!-- NOTIFIKASI VISI MISI -->
    <?php if ($pesanVisiMisi !== ''): ?>
        <div class="stat-alert stat-sukses">
            <i class="fa-solid fa-circle-check"></i>
            <?= escStat($pesanVisiMisi) ?>
        </div>
    <?php endif; ?>

    <!-- FORM INPUT / EDIT VISI & MISI -->
    <div class="stat-form-card">
        <h4>
            <i class="fa-solid fa-bullseye"></i>
            <?= !empty($editDataVM['id']) ? 'Edit Visi & Misi Gereja (ID: ' . $editDataVM['id'] . ')' : 'Tambah / Perbarui Visi & Misi Gereja' ?>
        </h4>

        <form method="POST" action="INDEX.php?page=kelola-jumlahjiwa">
            <!-- Hidden input untuk menyimpan ID jika sedang mode edit -->
            <input type="hidden" name="id_visimisi" value="<?= escStat($editDataVM['id']) ?>">

            <div class="stat-field">
                <label>Visi</label>
                <textarea name="visi" rows="3" placeholder="Masukkan visi gereja..." required><?= escStat($editDataVM['visi']) ?></textarea>
            </div>

            <div class="stat-field">
                <label>Misi</label>
                <textarea name="misi" rows="5" placeholder="Masukkan misi gereja..." required><?= escStat($editDataVM['misi']) ?></textarea>
                <small style="color: #64748b;">Gunakan baris baru (Enter) untuk memisahkan setiap poin misi.</small>
            </div>

            <div class="stat-actions">
                <button type="submit" name="simpan_visimisi" class="stat-btn primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= !empty($editDataVM['id']) ? 'Update Perubahan Visi & Misi' : 'Simpan Visi & Misi Baru' ?>
                </button>
                <?php if (!empty($editDataVM['id'])): ?>
                    <a href="INDEX.php?page=kelola-jumlahjiwa" class="stat-btn" style="background:#e2e8f0; color:#334158; text-decoration:none;">Batal Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABEL DAFTAR / RIWAYAT PENYIMPANAN VISI & MISI DI BAWAHNYA -->
    <div class="stat-form-card" style="margin-top: 30px;">
        <h4>
            <i class="fa-solid fa-list"></i>
            Daftar / Riwayat Visi & Misi Tersimpan
        </h4>
        <div style="overflow-x: auto;">
            <table class="stat-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Visi</th>
                        <th style="width: 45%;">Misi</th>
                        <th style="width: 15%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($listVisiMisi && mysqli_num_rows($listVisiMisi) > 0): ?>
                        <?php $no = 1; while($rowVM = mysqli_fetch_assoc($listVisiMisi)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= nl2br(escStat($rowVM['visi'])) ?></td>
                                <td><?= nl2br(escStat($rowVM['misi'])) ?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="INDEX.php?page=kelola-jumlahjiwa&edit_vm=<?= $rowVM['id'] ?>" class="stat-btn warning">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <a href="INDEX.php?page=kelola-jumlahjiwa&hapus_vm=<?= $rowVM['id'] ?>" class="stat-btn danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b;">Belum ada data Visi & Misi yang tersimpan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- TOMBOL KEMBALI KE DASHBOARD -->
<div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 20px;">
    <a href="INDEX.php?page=admin-dashboard" style="display: inline-flex; align-items: center; gap: 8px; background: #e5e7eb; color: #374151; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>