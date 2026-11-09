<?php
// =====================================================
// HALAMAN KELOLA DOKUMENTASI JEMAAT GPM WARU
// File: pages/kelola-dokumentasi.php
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan koneksi database tersedia
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// Pemeriksaan akses administrator
if (($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div style='padding:30px;text-align:center;color:red'>
            Akses ditolak! Halaman ini khusus administrator.
          </div>";
    return;
}

// Pastikan koneksi database tersedia
if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia.");
}

// Fungsi keamanan output
if (!function_exists('e_dok')) {
    function e_dok($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Token keamanan formulir
if (empty($_SESSION['csrf_dokumentasi'])) {
    $_SESSION['csrf_dokumentasi'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_dokumentasi'];

// Lokasi penyimpanan foto
$folderFoto = __DIR__ . '/../uploads/dokumentasi/';
$urlFoto = 'uploads/dokumentasi/';

// Membuat folder jika belum tersedia
if (!is_dir($folderFoto)) {
    mkdir($folderFoto, 0755, true);
}

$pesan = '';
$error = '';

// =====================================================
// FUNGSI UPLOAD FOTO
// =====================================================

function upload_foto_dokumentasi($file, $folderFoto) {

    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload foto gagal.");
    }

    // Periksa tipe file sebenarnya
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new Exception("Format foto harus JPG, PNG, atau WEBP.");
    }

    $namaFoto = 'dok_' . bin2hex(random_bytes(12))
              . '.' . $allowed[$mime];

    if (!move_uploaded_file(
        $file['tmp_name'],
        $folderFoto . $namaFoto
    )) {
        throw new Exception("Foto gagal disimpan.");
    }

    return $namaFoto;
}

// =====================================================
// PROSES TAMBAH, EDIT, DAN HAPUS
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $token = $_POST['csrf_token'] ?? '';

        if (!hash_equals($csrf, $token)) {
            throw new Exception("Permintaan tidak valid. Silakan muat ulang halaman.");
        }

        $aksi = $_POST['aksi'] ?? '';

        // TAMBAH DOKUMENTASI
        if ($aksi === 'tambah') {

            $kategori = $_POST['kategori'] ?? '';
            $tanggal = $_POST['tanggal'] ?? '';
            $keterangan = trim($_POST['keterangan'] ?? '');

            if (!in_array($kategori, ['kegiatan', 'pembangunan'], true)) {
                throw new Exception("Kategori tidak valid.");
            }

            if ($tanggal === '') {
                throw new Exception("Tanggal wajib diisi.");
            }

            $foto = upload_foto_dokumentasi($_FILES['foto'] ?? null, $folderFoto);

            if (!$foto) {
                throw new Exception("Silakan pilih foto dokumentasi.");
            }

            // Generate judul otomatis berdasarkan kategori & tanggal agar tidak kosong di database
            $judul = ($kategori === 'kegiatan' ? 'Kegiatan Jemaat - ' : 'Pembangunan - ') . $tanggal;

            $stmt = mysqli_prepare($conn, "INSERT INTO dokumentasi_jemaat (kategori, judul, tanggal, keterangan, foto) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare Tambah Gagal: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "sssss", $kategori, $judul, $tanggal, $keterangan, $foto);

            if (!mysqli_stmt_execute($stmt)) {
                @unlink($folderFoto . $foto);
                throw new Exception("Gagal menyimpan dokumentasi.");
            }

            mysqli_stmt_close($stmt);
            echo "<script>window.location.href='INDEX.php?page=kelola-dokumentasi&status=tambah';</script>";
            exit;
        }

        // EDIT DOKUMENTASI
        elseif ($aksi === 'edit') {

            $id = (int)($_POST['id'] ?? 0);
            $kategori = $_POST['kategori'] ?? '';
            $tanggal = $_POST['tanggal'] ?? '';
            $keterangan = trim($_POST['keterangan'] ?? '');

            if ($id <= 0) {
                throw new Exception("ID tidak valid.");
            }

            if ($tanggal === '') {
                throw new Exception("Tanggal wajib diisi.");
            }

            $stmt = mysqli_prepare($conn, "SELECT judul, foto FROM dokumentasi_jemaat WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $judulLama, $fotoLama);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            $fotoBaru = upload_foto_dokumentasi($_FILES['foto'] ?? null, $folderFoto);
            $foto = $fotoBaru ?: $fotoLama;
            $judul = $judulLama; // Pertahankan judul lama

            $stmt = mysqli_prepare($conn, "UPDATE dokumentasi_jemaat SET kategori = ?, judul = ?, tanggal = ?, keterangan = ?, foto = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare Edit Gagal: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "sssssi", $kategori, $judul, $tanggal, $keterangan, $foto, $id);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal memperbarui dokumentasi.");
            }

            mysqli_stmt_close($stmt);

            if ($fotoBaru && $fotoLama) {
                @unlink($folderFoto . basename($fotoLama));
            }

            echo "<script>window.location.href='INDEX.php?page=kelola-dokumentasi&status=edit';</script>";
            exit;
        }

        // HAPUS DOKUMENTASI
        elseif ($aksi === 'hapus') {

            $id = (int)($_POST['id'] ?? 0);

            $stmt = mysqli_prepare($conn, "SELECT foto FROM dokumentasi_jemaat WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $fotoHapus);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "DELETE FROM dokumentasi_jemaat WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($fotoHapus) {
                @unlink($folderFoto . basename($fotoHapus));
            }

            echo "<script>window.location.href='INDEX.php?page=kelola-dokumentasi&status=hapus';</script>";
            exit;
        }

    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

$status = $_GET['status'] ?? '';
if ($status === 'tambah') $pesan = 'Dokumentasi berhasil ditambahkan.';
elseif ($status === 'edit') $pesan = 'Dokumentasi berhasil diperbarui.';
elseif ($status === 'hapus') $pesan = 'Dokumentasi berhasil dihapus.';

$dataEdit = null;
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, kategori, judul, tanggal, keterangan, foto FROM dokumentasi_jemaat WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $idEdit);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $editId, $editKategori, $editJudul, $editTanggal, $editKeterangan, $editFoto);
    if (mysqli_stmt_fetch($stmt)) {
        $dataEdit = ['id' => $editId, 'kategori' => $editKategori, 'judul' => $editJudul, 'tanggal' => $editTanggal, 'keterangan' => $editKeterangan, 'foto' => $editFoto];
    }
    mysqli_stmt_close($stmt);
}

// AMBIL SEMUA DATA DOKUMENTASI
$kegiatan = [];
$pembangunan = [];

$result = mysqli_query($conn, "SELECT * FROM dokumentasi_jemaat ORDER BY tanggal DESC, id DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['kategori'] === 'kegiatan') {
            $kegiatan[] = $row;
        } else {
            $pembangunan[] = $row;
        }
    }
}

$totalDokumentasi = count($kegiatan) + count($pembangunan);
?>

<div class="dok-page">

    <section class="dok-header">
        <div class="dok-header-icon">
            <i class="fa-solid fa-images"></i>
        </div>
        <div>
            <h1>Dokumentasi Pembangunan Dalam Jemaat</h1>
            <p>Kelola foto kegiatan jemaat dan perkembangan pembangunan gereja</p>
        </div>
    </section>

    <?php if ($pesan): ?>
        <div class="dok-alert success"><i class="fa-solid fa-circle-check"></i> <?= e_dok($pesan) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="dok-alert danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e_dok($error) ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT (TANPA INPUT JUDUL) -->
    <?php if (isset($_GET['tambah']) || $dataEdit): ?>
    <section class="dok-form-card">
        <h2><i class="fa-solid fa-pen-to-square"></i> <?= $dataEdit ? 'Edit Dokumentasi' : 'Tambah Dokumentasi Baru' ?></h2>
        <form method="POST" enctype="multipart/form-data" action="INDEX.php?page=kelola-dokumentasi<?= $dataEdit ? '&edit=' . (int)$dataEdit['id'] : '&tambah=1' ?>">
            <input type="hidden" name="csrf_token" value="<?= e_dok($csrf) ?>">
            <input type="hidden" name="aksi" value="<?= $dataEdit ? 'edit' : 'tambah' ?>">
            <?php if ($dataEdit): ?><input type="hidden" name="id" value="<?= (int)$dataEdit['id'] ?>"><?php endif; ?>

            <div class="dok-form-grid">
                <div class="dok-field">
                    <label>Kategori Dokumentasi</label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="kegiatan" <?= ($dataEdit['kategori'] ?? '') === 'kegiatan' ? 'selected' : '' ?>>Foto Kegiatan Jemaat</option>
                        <option value="pembangunan" <?= ($dataEdit['kategori'] ?? '') === 'pembangunan' ? 'selected' : '' ?>>Proses Pembangunan</option>
                    </select>
                </div>
                <div class="dok-field">
                    <label>Tanggal Dokumentasi</label>
                    <input type="date" name="tanggal" value="<?= e_dok($dataEdit['tanggal'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="dok-field full">
                    <label>Foto Dokumentasi</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" <?= $dataEdit ? '' : 'required' ?>>
                    <small>Format JPG, PNG, WEBP.</small>
                </div>
                <div class="dok-field full">
                    <label>Keterangan Dokumentasi</label>
                    <textarea name="keterangan" rows="4" placeholder="Masukkan keterangan dokumentasi..."><?= e_dok($dataEdit['keterangan'] ?? '') ?></textarea>
                </div>
                <?php if ($dataEdit): ?>
                <div class="dok-field full">
                    <label>Foto Saat Ini</label>
                    <img src="<?= e_dok($urlFoto . basename($dataEdit['foto'])) ?>" class="dok-preview" alt="Foto">
                </div>
                <?php endif; ?>
            </div>
            <div class="dok-form-actions">
                <button type="submit" class="dok-btn primary"><i class="fa-solid fa-save"></i> <?= $dataEdit ? 'Simpan Perubahan' : 'Simpan Dokumentasi' ?></button>
                <a href="INDEX.php?page=kelola-dokumentasi" class="dok-btn secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
            </div>
        </form>
    </section>
    <?php endif; ?>

    <!-- KOTAK TOTAL DOKUMENTASI DI SEBELAH KANAN DAN UKURAN MENYESUAIKAN -->
    <section class="dok-toolbar-right">
        <div class="dok-total">
            Total Dokumentasi:
            <span><?= $totalDokumentasi ?></span>
        </div>
    </section>

    <!-- BAGIAN FOTO KEGIATAN JEMAAT -->
    <section class="dok-section">
        <div class="dok-section-heading">
            <h2><i class="fa-solid fa-camera"></i> Foto Kegiatan Jemaat</h2>
            <a href="INDEX.php?page=kelola-dokumentasi&tambah=1" class="dok-add-small"><i class="fa-solid fa-plus"></i> Tambah Dokumentasi</a>
        </div>
        <div class="dok-grid">
            <?php if ($kegiatan): ?>
                <?php foreach ($kegiatan as $row): ?>
                <article class="dok-item">
                    <img src="<?= e_dok($urlFoto . basename($row['foto'])) ?>" alt="Foto" class="dok-image">
                    <div class="dok-item-body">
                        <p class="dok-date"><i class="fa-regular fa-calendar-days"></i> <?= date('d F Y', strtotime($row['tanggal'])) ?></p>
                        <?php if (!empty($row['keterangan'])): ?>
                            <p style="font-size: 13px; color: #475569; margin: 0 0 10px;"><?= e_dok($row['keterangan']) ?></p>
                        <?php endif; ?>
                        <div class="dok-actions">
                            <a href="INDEX.php?page=kelola-dokumentasi&edit=<?= (int)$row['id'] ?>" class="dok-action edit"><i class="fa-solid fa-pen"></i> Edit</a>
                            <form method="POST" action="INDEX.php?page=kelola-dokumentasi" onsubmit="return confirm('Yakin ingin menghapus?')">
                                <input type="hidden" name="csrf_token" value="<?= e_dok($csrf) ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="dok-action delete"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="dok-empty"><i class="fa-regular fa-images"></i><p>Belum ada foto kegiatan jemaat.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- BAGIAN PROSES PEMBANGUNAN -->
    <section class="dok-section">
        <div class="dok-section-heading">
            <h2><i class="fa-solid fa-helmet-safety"></i> Dokumentasi Proses Pembangunan</h2>
            <a href="INDEX.php?page=kelola-dokumentasi&tambah=1" class="dok-add-small"><i class="fa-solid fa-plus"></i> Tambah Dokumentasi</a>
        </div>
        <div class="dok-grid">
            <?php if ($pembangunan): ?>
                <?php foreach ($pembangunan as $row): ?>
                <article class="dok-item">
                    <img src="<?= e_dok($urlFoto . basename($row['foto'])) ?>" alt="Foto" class="dok-image">
                    <div class="dok-item-body">
                        <p class="dok-date"><i class="fa-regular fa-calendar-days"></i> <?= date('d F Y', strtotime($row['tanggal'])) ?></p>
                        <?php if (!empty($row['keterangan'])): ?>
                            <p style="font-size: 13px; color: #475569; margin: 0 0 10px;"><?= e_dok($row['keterangan']) ?></p>
                        <?php endif; ?>
                        <div class="dok-actions">
                            <a href="INDEX.php?page=kelola-dokumentasi&edit=<?= (int)$row['id'] ?>" class="dok-action edit"><i class="fa-solid fa-pen"></i> Edit</a>
                            <form method="POST" action="INDEX.php?page=kelola-dokumentasi" onsubmit="return confirm('Yakin ingin menghapus?')">
                                <input type="hidden" name="csrf_token" value="<?= e_dok($csrf) ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="dok-action delete"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="dok-empty"><i class="fa-solid fa-helmet-safety"></i><p>Belum ada dokumentasi proses pembangunan.</p></div>
            <?php endif; ?>
        </div>
    </section>

</div>

<style>
.dok-page { padding: 20px; color: #173568; background: #f1f5f9; min-height: 100vh; }
.dok-header { display: flex; align-items: center; gap: 22px; background: #fff; padding: 22px 30px; border-radius: 14px; border-left: 5px solid #0875df; box-shadow: 0 3px 12px rgba(0,0,0,.06); margin-bottom: 14px; }
.dok-header-icon { width: 90px; height: 80px; min-width: 90px; background: #dbeafe; color: #0875df; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 42px; }
.dok-header h1 { font-size: 25px; font-weight: 800; margin: 0 0 7px; color: #173568; }
.dok-header p { color: #526581; margin: 0; font-size: 15px; }

.dok-toolbar-right { 
    display: flex; 
    justify-content: flex-end; 
    align-items: center; 
    background: white; 
    padding: 12px 18px; 
    border-radius: 13px; 
    margin-bottom: 15px; 
    box-shadow: 0 3px 10px rgba(0,0,0,.05); 
    width: fit-content;    /* Membuat kartu menyesuaikan isi teks */
    margin-left: auto;     /* Memindahkan kartu secara otomatis ke sebelah kanan */
}

.dok-total { 
    font-size: 14px; 
    font-weight: 600; 
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;   /* Mencegah teks terpotong ke bawah */
}

.dok-total span { 
    background: #0875df; 
    color: white; 
    padding: 5px 12px; 
    border-radius: 8px; 
}

.dok-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 17px; border: none; border-radius: 8px; font-weight: 700; text-decoration: none; cursor: pointer; font-size: 13px; }
.dok-btn.primary { background: #0868ce; color: white; }
.dok-btn.secondary { background: #e5e7eb; color: #374151; }
.dok-section { background: white; padding: 20px; border-radius: 14px; margin-bottom: 15px; box-shadow: 0 3px 12px rgba(0,0,0,.06); }
.dok-section-heading { display: flex; align-items: center; justify-content: space-between; gap: 10px; border-bottom: 1px solid #dbe2eb; padding-bottom: 13px; margin-bottom: 15px; }
.dok-section-heading h2 { margin: 0; font-size: 19px; font-weight: 800; color: #173568; }
.dok-add-small { background: #0868ce; color: white; padding: 9px 13px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; white-space: nowrap; }
.dok-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.dok-item { border: 1px solid #e2e8f0; border-radius: 11px; overflow: hidden; background: #fff; box-shadow: 0 2px 7px rgba(0,0,0,.05); transition: .2s; }
.dok-item:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,.09); }
.dok-image { width: 100%; height: 155px; object-fit: cover; display: block; background: #e5e7eb; }
.dok-item-body { padding: 12px; }
.dok-date { color: #526581; font-size: 12px; margin: 0 0 12px; }
.dok-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.dok-actions form { margin: 0; min-width: 0; }
.dok-action { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; min-height: 36px; padding: 7px 4px; border: none; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; cursor: pointer; }
.dok-action.edit { background: #ffedd0; color: #d97706; }
.dok-action.delete { background: #fee2e2; color: #dc2626; }
.dok-form-card { background: white; padding: 22px; border-radius: 13px; margin-bottom: 18px; box-shadow: 0 3px 12px rgba(0,0,0,.06); }
.dok-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.dok-field { display: flex; flex-direction: column; gap: 8px; }
.dok-field.full { grid-column: 1 / -1; }
.dok-field label { font-size: 14px; font-weight: 700; }
.dok-field input, .dok-field select, .dok-field textarea { width: 100%; box-sizing: border-box; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; }
.dok-preview { width: 220px; max-width: 100%; height: auto; border-radius: 9px; }
.dok-form-actions { display: flex; gap: 10px; margin-top: 20px; }
.dok-alert { padding: 13px 16px; border-radius: 9px; margin-bottom: 15px; font-size: 14px; font-weight: 600; }
.dok-alert.success { background: #dcfce7; color: #166534; }
.dok-alert.danger { background: #fee2e2; color: #991b1b; }
.dok-empty { grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b; }
.dok-empty i { font-size: 40px; margin-bottom: 10px; }
.dok-back { margin-top: 20px; }
.dok-back a { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0868ce; text-decoration: none; }
</style>

<!-- Tombol Kembali ke Dashboard Admin (Di sebelah kanan bawah) -->
<div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 20px;">
    <a href="INDEX.php?page=admin-dashboard" style="display: inline-flex; align-items: center; gap: 8px; background: #e5e7eb; color: #374151; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: background 0.2s;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>