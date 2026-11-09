
<?php
// ============================================================
// KELOLA VISI & MISI JEMAAT GPM WARU
// File: kelola-visimisi.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// 1. KONEKSI DATABASE
// ------------------------------------------------------------

if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database tidak tersedia.");
}

// ------------------------------------------------------------
// 2. FUNGSI KEAMANAN
// ------------------------------------------------------------

function vm_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_visimisi'])) {
    $_SESSION['csrf_visimisi'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['csrf_visimisi'];
$pesan = '';
$jenis_pesan = 'success';

// ------------------------------------------------------------
// 3. PROSES TAMBAH, EDIT, DAN HAPUS
// ------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['token']) ||
        !hash_equals($token, $_POST['token'])
    ) {
        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenis_pesan = "danger";

    } else {

        $aksi = $_POST['aksi'] ?? '';

        // TAMBAH KONTEN
        if ($aksi === 'tambah') {

            $jenis = $_POST['jenis'] ?? '';
            $deskripsi = trim($_POST['deskripsi'] ?? '');

            if (
                !in_array($jenis, ['visi', 'misi'], true) ||
                $deskripsi === ''
            ) {
                $pesan = "Jenis konten dan deskripsi wajib diisi!";
                $jenis_pesan = "warning";

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO konten_visi_misi (jenis, deskripsi)
                     VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ss",
                    $jenis,
                    $deskripsi
                );

                if (mysqli_stmt_execute($stmt)) {
                    $pesan = "Konten berhasil ditambahkan!";
                } else {
                    $pesan = "Gagal menambahkan konten.";
                    $jenis_pesan = "danger";
                }

                mysqli_stmt_close($stmt);
            }
        }

        // SIMPAN EDIT
        elseif ($aksi === 'update') {

            $id = (int)($_POST['id'] ?? 0);
            $deskripsi = trim($_POST['deskripsi'] ?? '');

            if ($id <= 0 || $deskripsi === '') {

                $pesan = "Deskripsi tidak boleh kosong!";
                $jenis_pesan = "warning";

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE konten_visi_misi
                     SET deskripsi = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "si",
                    $deskripsi,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {
                    $pesan = "Konten berhasil diperbarui!";
                } else {
                    $pesan = "Gagal memperbarui konten.";
                    $jenis_pesan = "danger";
                }

                mysqli_stmt_close($stmt);
            }
        }

        // HAPUS KONTEN
        elseif ($aksi === 'hapus') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {

                $stmt = mysqli_prepare(
                    $conn,
                    "DELETE FROM konten_visi_misi WHERE id = ?"
                );

                mysqli_stmt_bind_param($stmt, "i", $id);

                if (mysqli_stmt_execute($stmt)) {
                    $pesan = "Konten berhasil dihapus!";
                } else {
                    $pesan = "Gagal menghapus konten.";
                    $jenis_pesan = "danger";
                }

                mysqli_stmt_close($stmt);

            } else {
                $pesan = "ID konten tidak valid!";
                $jenis_pesan = "danger";
            }
        }
    }
}

// ------------------------------------------------------------
// 4. MODE TAMBAH DAN EDIT
// ------------------------------------------------------------

$tambah_mode = isset($_GET['tambah']) && $_GET['tambah'] == '1';

$edit_id = isset($_GET['edit'])
    ? (int)$_GET['edit']
    : 0;

// ------------------------------------------------------------
// 5. AMBIL DATA VISI DAN MISI
// ------------------------------------------------------------

$query = mysqli_query(
    $conn,
    "SELECT * FROM konten_visi_misi
     ORDER BY FIELD(jenis, 'visi', 'misi'), id ASC"
);

if (!$query) {
    die("Gagal mengambil data visi dan misi.");
}

$data_visi = [];
$data_misi = [];

while ($row = mysqli_fetch_assoc($query)) {

    if ($row['jenis'] === 'visi') {
        $data_visi[] = $row;
    } else {
        $data_misi[] = $row;
    }
}

// ------------------------------------------------------------
// 6. FUNGSI MENAMPILKAN KARTU KONTEN
// ------------------------------------------------------------

function tampilkan_konten_vm(
    $data,
    $jenis,
    $token,
    $edit_id
) {

    $ikon = $jenis === 'visi'
        ? 'fa-eye'
        : 'fa-list-check';

    $warna = $jenis === 'visi'
        ? '#ed8900'
        : '#0875df';

    $judul = strtoupper($jenis);

    if (empty($data)) {
        $data = [];
    }

    foreach ($data as $item):

        $id = (int)$item['id'];
        $sedang_edit = ($edit_id === $id);
?>

<div class="vm-card">

    <!-- IKON DAN GARIS SAMPING -->

    <div class="vm-icon-area"
         style="border-right: 3px solid <?= $warna ?>;">

        <i class="fa-solid <?= $ikon ?>"
           style="color:<?= $warna ?>;">
        </i>

    </div>

    <!-- ISI KONTEN -->

    <div class="vm-content">

        <div class="vm-card-header">

            <h3><?= $judul ?></h3>

            <?php if (!$sedang_edit): ?>

                <a href="admin-dashboard.php?page=kelola-visi-misi&edit=<?= $id ?>"
                   class="vm-btn-edit">

                    <i class="fa-solid fa-pen"></i>
                    Edit

                </a>

            <?php endif; ?>

        </div>

        <?php if ($sedang_edit): ?>

            <!-- FORM EDIT INLINE -->

            <div class="vm-edit-box">

                <div class="vm-edit-heading">

                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit <?= $judul ?> Jemaat

                </div>

                <form method="POST"
                      action="admin-dashboard.php?page=kelola-visi-misi">

                    <input type="hidden"
                           name="token"
                           value="<?= vm_escape($token) ?>">

                    <input type="hidden"
                           name="aksi"
                           value="update">

                    <input type="hidden"
                           name="id"
                           value="<?= $id ?>">

                    <label class="vm-label">
                        Deskripsi <?= ucfirst($jenis) ?>
                    </label>

                    <textarea name="deskripsi"
                              class="vm-textarea"
                              required><?= vm_escape($item['deskripsi']) ?></textarea>

                    <div class="vm-edit-actions">

                        <button type="submit"
                                class="vm-btn-save">

                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan

                        </button>

                        <a href="admin-dashboard.php?page=kelola-visi-misi"
                           class="vm-btn-cancel">

                            Batal

                        </a>

                        <button type="submit"
                                form="hapus-vm-<?= $id ?>"
                                class="vm-btn-delete">

                            <i class="fa-solid fa-trash"></i>
                            Hapus

                        </button>

                    </div>

                </form>

                <form method="POST"
                      id="hapus-vm-<?= $id ?>"
                      action="admin-dashboard.php?page=kelola-visi-misi"
                      onsubmit="return confirm('Yakin ingin menghapus konten ini?');">

                    <input type="hidden"
                           name="token"
                           value="<?= vm_escape($token) ?>">

                    <input type="hidden"
                           name="aksi"
                           value="hapus">

                    <input type="hidden"
                           name="id"
                           value="<?= $id ?>">

                </form>

            </div>

        <?php else: ?>

            <!-- TAMPILAN KONTEN NORMAL -->

            <div class="vm-description">

                <?= nl2br(vm_escape($item['deskripsi'])) ?>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php
    endforeach;
}
?>

<!-- ============================================================
     CSS DESAIN KELOLA VISI DAN MISI
============================================================ -->

<style>

.vm-wrapper {
    width: 100%;
    color: #18365e;
}

.vm-heading {
    border-left: 8px solid #f28c00;
    padding: 8px 22px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.vm-heading h2 {
    font-size: 36px;
    font-weight: 800;
    margin: 0;
    color: #17365f;
}

.vm-heading p {
    color: #66809f;
    margin: 8px 0 0;
    font-size: 16px;
}

.vm-btn-add {
    background: #0755c7;
    color: white;
    padding: 18px 24px;
    border-radius: 11px;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 70px;
    box-shadow: 0 3px 8px #0002;
}

.vm-btn-add:hover {
    background: #0645a5;
    color: white;
}

.vm-card {
    background: white;
    border: 1px solid #dce4ef;
    border-radius: 16px;
    padding: 25px 24px;
    display: flex;
    gap: 26px;
    margin-bottom: 22px;
    box-shadow: 0 3px 8px #17365f12;
}

.vm-icon-area {
    width: 108px;
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 15px;
}

.vm-icon-area i {
    font-size: 43px;
}

.vm-content {
    flex: 1;
    min-width: 0;
}

.vm-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid #dce4ef;
    padding-bottom: 13px;
    margin-bottom: 12px;
}

.vm-card-header h3 {
    font-size: 26px;
    font-weight: 800;
    color: #17365f;
    margin: 0;
}

.vm-description {
    font-size: 20px;
    line-height: 1.7;
    font-style: italic;
    color: #17365f;
    padding: 2px 0;
    white-space: normal;
}

.vm-btn-edit {
    background: #0755c7;
    color: white;
    text-decoration: none;
    padding: 16px 23px;
    border-radius: 9px;
    font-weight: 700;
    white-space: nowrap;
}

.vm-btn-edit:hover {
    background: #0645a5;
    color: white;
}

/* FORM EDIT */

.vm-edit-box {
    border: 2px solid #202020;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}

.vm-edit-heading {
    background: #1d3d70;
    color: white;
    padding: 13px 16px;
    font-weight: 700;
}

.vm-label {
    display: block;
    padding: 12px 0 5px;
    font-size: 17px;
}

.vm-textarea {
    width: 100%;
    min-height: 145px;
    resize: vertical;
    padding: 15px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 16px;
    color: #17365f;
    font-family: inherit;
    outline: none;
}

.vm-textarea:focus {
    border-color: #0755c7;
    box-shadow: 0 0 0 3px #0755c71c;
}

.vm-edit-actions {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    padding: 10px 0 0;
}

.vm-edit-actions button,
.vm-edit-actions a {
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-weight: 700;
    cursor: pointer;
}

.vm-btn-save {
    background: #0755c7;
}

.vm-btn-save:hover {
    background: #0645a5;
}

.vm-btn-cancel {
    background: #6c757d;
}

.vm-btn-cancel:hover {
    background: #565e64;
}

.vm-btn-delete {
    background: #e92e3c;
}

.vm-btn-delete:hover {
    background: #c82333;
}

/* FORM TAMBAH */

.vm-add-form {
    background: white;
    border-radius: 15px;
    border: 1px solid #dce4ef;
    padding: 25px;
    margin-bottom: 22px;
    box-shadow: 0 3px 8px #17365f12;
}

.vm-add-form h4 {
    font-weight: 800;
    color: #17365f;
    margin-bottom: 20px;
}

.vm-add-form label {
    font-weight: 700;
    margin-bottom: 8px;
}

.vm-add-form select,
.vm-add-form textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 17px;
}

.vm-add-form textarea {
    min-height: 140px;
}

.vm-alert {
    padding: 14px 18px;
    border-radius: 9px;
    margin-bottom: 20px;
}

/* RESPONSIVE */

@media(max-width:768px) {

    .vm-heading h2 {
        font-size: 25px;
    }

    .vm-heading {
        align-items: flex-start;
    }

    .vm-btn-add {
        min-height: auto;
        padding: 13px 18px;
    }

    .vm-card {
        padding: 16px 12px;
        gap: 12px;
    }

    .vm-icon-area {
        width: 48px;
    }

    .vm-icon-area i {
        font-size: 27px;
    }

    .vm-card-header h3 {
        font-size: 20px;
    }

    .vm-description {
        font-size: 16px;
    }

    .vm-btn-edit {
        padding: 10px;
        font-size: 13px;
    }

}

</style>


<!-- ============================================================
     TAMPILAN UTAMA
============================================================ -->

<div class="vm-wrapper">

    <!-- JUDUL DAN TOMBOL TAMBAH -->

    <div class="vm-heading">

        <div>

            <h2>Kelola Visi & Misi Jemaat</h2>

            <p>
                Atur informasi visi dan misi pelayanan jemaat
            </p>

        </div>

        <a href="admin-dashboard.php?page=kelola-visi-misi&tambah=1"
           class="vm-btn-add">

            <i class="fa-solid fa-plus"></i>
            Tambah Konten

        </a>

    </div>


    <!-- NOTIFIKASI -->

    <?php if ($pesan !== ''): ?>

        <div class="vm-alert alert alert-<?= vm_escape($jenis_pesan) ?>">

            <?= vm_escape($pesan) ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         FORM TAMBAH KONTEN
    ====================================================== -->

    <?php if ($tambah_mode): ?>

        <div class="vm-add-form">

            <h4>
                <i class="fa-solid fa-plus-circle text-primary me-2"></i>
                Tambahkan Konten Visi & Misi
            </h4>

            <form method="POST"
                  action="admin-dashboard.php?page=kelola-visi-misi">

                <input type="hidden"
                       name="token"
                       value="<?= vm_escape($token) ?>">

                <input type="hidden"
                       name="aksi"
                       value="tambah">

                <label>Jenis Konten</label>

                <select name="jenis" required>

                    <option value="">-- Pilih Jenis Konten --</option>

                    <option value="visi">Visi Jemaat</option>

                    <option value="misi">Misi Jemaat</option>

                </select>

                <label>Deskripsi Konten</label>

                <textarea name="deskripsi"
                          placeholder="Tuliskan visi atau misi jemaat..."
                          required></textarea>

                <button type="submit" class="btn btn-primary">

                    <i class="fa-solid fa-save me-2"></i>
                    Simpan Konten

                </button>

                <a href="admin-dashboard.php?page=kelola-visi-misi"
                   class="btn btn-secondary">

                    Batal

                </a>

            </form>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         KARTU VISI
    ====================================================== -->

    <?php

    tampilkan_konten_vm(
        $data_visi,
        'visi',
        $token,
        $edit_id
    );

    ?>


    <!-- =====================================================
         KARTU MISI
    ====================================================== -->

    <?php

    tampilkan_konten_vm(
        $data_misi,
        'misi',
        $token,
        $edit_id
    );

    ?>


    <!-- BELUM ADA DATA -->

    <?php if (empty($data_visi) && empty($data_misi)): ?>

        <div class="alert alert-info text-center">

            <i class="fa-solid fa-folder-open fa-2x mb-2"></i>

            <p class="mb-0">
                Belum ada konten visi dan misi.
                Silakan klik Tambah Konten.
            </p>

        </div>

    <?php endif; ?>

</div>
