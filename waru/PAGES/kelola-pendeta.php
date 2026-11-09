<?php
// 1. Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cek hak akses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    echo '<script>window.location.href = "INDEX.php?page=login";</script>';
    exit;
}

// 3. Fungsi helper untuk escaping output HTML
function e_pendeta($value) { 
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
}

$msg = '';
$error = '';
$uploadDir = 'uploads/pendeta/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Ambil data untuk mode edit jika parameter 'edit' tersedia
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmtEdit = mysqli_prepare($conn, "SELECT * FROM pendeta WHERE id = ?");
    mysqli_stmt_bind_param($stmtEdit, "i", $editId);
    mysqli_stmt_execute($stmtEdit);
    $resEdit = mysqli_stmt_get_result($stmtEdit);
    $editData = mysqli_fetch_assoc($resEdit);
    mysqli_stmt_close($stmtEdit);
}

// 4. Proses Hapus Data Pendeta
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        $stmtCek = mysqli_prepare($conn, "SELECT foto FROM pendeta WHERE id = ?");
        mysqli_stmt_bind_param($stmtCek, "i", $delId);
        mysqli_stmt_execute($stmtCek);
        $resCek = mysqli_stmt_get_result($stmtCek);
        if ($rowCek = mysqli_fetch_assoc($resCek)) {
            if (!empty($rowCek['foto']) && file_exists($uploadDir . $rowCek['foto'])) {
                unlink($uploadDir . $rowCek['foto']);
            }
        }
        mysqli_stmt_close($stmtCek);

        $stmtDel = mysqli_prepare($conn, "DELETE FROM pendeta WHERE id = ?");
        mysqli_stmt_bind_param($stmtDel, "i", $delId);
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);

        echo '<script>window.location.href = "INDEX.php?page=kelola-pendeta&deleted=1";</script>';
        exit;
    }
}

// 5. Proses Simpan / Update Data Pendeta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pendeta'])) {
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama     = trim($_POST['nama'] ?? '');
    $jabatan  = trim($_POST['jabatan'] ?? '');
    $ket      = trim($_POST['keterangan'] ?? '');

    if ($nama === '' || $jabatan === '') {
        $error = 'Nama pendeta dan Jabatan wajib diisi.';
    } else {
        $fileNameUploaded = '';
        
        // Cek apakah ada file foto yang diunggah
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto']['tmp_name'];
            $origName = $_FILES['foto']['name'];
            $fileExtension = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions, true)) {
                $fileNameUploaded = 'pendeta_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExtension;
                $dest_path = $uploadDir . $fileNameUploaded;

                if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                    $error = 'Gagal mengunggah foto ke server.';
                }
            } else {
                $error = 'Format foto tidak valid. Harap unggah file JPG, JPEG, PNG, atau WEBP.';
            }
        }

        if (empty($error)) {
            if ($id > 0) {
                // Proses UPDATE Data
                if (!empty($fileNameUploaded)) {
                    $stmtOld = mysqli_prepare($conn, "SELECT foto FROM pendeta WHERE id = ?");
                    mysqli_stmt_bind_param($stmtOld, "i", $id);
                    mysqli_stmt_execute($stmtOld);
                    $resOld = mysqli_stmt_get_result($stmtOld);
                    if ($rowOld = mysqli_fetch_assoc($resOld)) {
                        if (!empty($rowOld['foto']) && file_exists($uploadDir . $rowOld['foto'])) {
                            unlink($uploadDir . $rowOld['foto']);
                        }
                    }
                    mysqli_stmt_close($stmtOld);

                    $q = mysqli_prepare($conn, 'UPDATE pendeta SET nama = ?, jabatan = ?, keterangan = ?, foto = ? WHERE id = ?');
                    mysqli_stmt_bind_param($q, 'ssssi', $nama, $jabatan, $ket, $fileNameUploaded, $id);
                } else {
                    $q = mysqli_prepare($conn, 'UPDATE pendeta SET nama = ?, jabatan = ?, keterangan = ? WHERE id = ?');
                    mysqli_stmt_bind_param($q, 'sssi', $nama, $jabatan, $ket, $id);
                }
                mysqli_stmt_execute($q);
                mysqli_stmt_close($q);

                echo '<script>window.location.href = "INDEX.php?page=kelola-pendeta&success=update";</script>';
                exit;
            } else {
                // Proses INSERT Data Baru
                if (empty($fileNameUploaded)) {
                    $fileNameUploaded = ''; 
                }
                
                $q = mysqli_prepare($conn, 'INSERT INTO pendeta (nama, jabatan, keterangan, foto) VALUES (?, ?, ?, ?)');
                mysqli_stmt_bind_param($q, 'ssss', $nama, $jabatan, $ket, $fileNameUploaded);
                mysqli_stmt_execute($q);
                mysqli_stmt_close($q);

                echo '<script>window.location.href = "INDEX.php?page=kelola-pendeta&success=add";</script>';
                exit;
            }
        }
    }
}

// Notifikasi pesan
if (isset($_GET['success'])) { 
    $msg = ($_GET['success'] === 'update') ? 'Profil pendeta berhasil diperbarui.' : 'Profil pendeta berhasil ditambahkan.'; 
}
if (isset($_GET['deleted'])) { $msg = 'Profil pendeta berhasil dihapus.'; }

// Ambil daftar pendeta
$listPendeta = mysqli_query($conn, 'SELECT * FROM pendeta ORDER BY id DESC');
?>

<section class="page-header">
    <h1><i class="fa-solid fa-user-tie"></i> Kelola Profil Pendeta & Pelayan</h1>
    <p>Tambah, edit, atau hapus profil pimpinan jemaat dan pendeta yang tampil pada halaman depan.</p>
</section>

<?php if ($msg): ?><div class="alert alert-success mt-3" style="padding:12px; background:#d1fae5; color:#065f46; border-radius:6px; margin-bottom:15px;"><?= e_pendeta($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger mt-3" style="padding:12px; background:#fee2e2; color:#991b1b; border-radius:6px; margin-bottom:15px;"><?= e_pendeta($error) ?></div><?php endif; ?>

<!-- Form Tambah / Edit Pendeta -->
<div class="card mt-3" style="padding:20px; background:#fff; border-radius:8px;">
    <h3><?= $editData ? 'Edit Profil Pendeta' : 'Tambah Profil Pendeta Baru' ?></h3>
    <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
        <input type="hidden" name="id" value="<?= $editData ? (int)$editData['id'] : 0 ?>">
        
        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Nama Lengkap & Gelar</label>
            <input class="form-control" name="nama" type="text" placeholder="Contoh: Pdt. Z. F. Salaka, S.Th." value="<?= $editData ? e_pendeta($editData['nama']) : '' ?>" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Jabatan / Posisi</label>
            <input class="form-control" name="jabatan" type="text" placeholder="Contoh: KETUA MAJELIS JEMAAT" value="<?= $editData ? e_pendeta($editData['jabatan']) : '' ?>" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Keterangan / Sub-teks</label>
            <input class="form-control" name="keterangan" type="text" placeholder="Contoh: Pendeta Jemaat GPM Waru" value="<?= $editData ? e_pendeta($editData['keterangan']) : '' ?>" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Foto Profil / Logo</label>
            <?php if ($editData && !empty($editData['foto'])): ?>
                <div style="margin-bottom: 8px;">
                    <img src="uploads/pendeta/<?= e_pendeta($editData['foto']) ?>" alt="Foto" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 1px solid #ccc;">
                    <span style="font-size: 12px; color: #6b7280; display: block;">Foto saat ini (biarkan kosong jika tidak ingin mengganti)</span>
                </div>
            <?php endif; ?>
            <input class="form-control" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
            <small style="color: #6b7280; font-size: 12px;">Format yang didukung: JPG, JPEG, PNG, WEBP.</small>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="simpan_pendeta" class="btn btn-primary" style="background:#2563eb; color:#fff; padding:8px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-floppy-disk"></i> <?= $editData ? 'Update Profil' : 'Simpan Profil' ?>
            </button>
            <?php if ($editData): ?>
                <a href="INDEX.php?page=kelola-pendeta" style="background:#6b7280; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center;">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabel Daftar Pendeta Tersimpan -->
<div class="card mt-3" style="padding:20px; background:#fff; border-radius:8px; margin-top:20px;">
    <h3>Daftar Profil Pendeta Tersimpan</h3>
    <div class="table-responsive" style="margin-top: 10px;">
        <table class="custom-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background:#f3f4f6; text-align:left;">
                    <th style="padding:10px;">Foto</th>
                    <th style="padding:10px;">Nama & Gelar</th>
                    <th style="padding:10px;">Jabatan</th>
                    <th style="padding:10px;">Keterangan</th>
                    <th style="padding:10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($listPendeta && mysqli_num_rows($listPendeta) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($listPendeta)): ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:10px;">
                        <?php if (!empty($r['foto']) && file_exists($uploadDir . $r['foto'])): ?>
                            <img src="uploads/pendeta/<?= e_pendeta($r['foto']) ?>" alt="Foto" style="width:40px; height:40px; object-fit:cover; border-radius:50%;">
                        <?php else: ?>
                            <div style="width:40px; height:40px; background:#e5e7eb; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; color:#6b7280;"><i class="fa-solid fa-user"></i></div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px; font-weight:600;"><?= e_pendeta($r['nama']) ?></td>
                    <td style="padding:10px; font-size:13px;"><span style="background:#fef3c7; color:#92400e; padding:3px 8px; border-radius:4px; font-weight:600;"><?= e_pendeta($r['jabatan']) ?></span></td>
                    <td style="padding:10px; color:#4b5563;"><?= e_pendeta($r['keterangan']) ?></td>
                    <td style="padding:10px; white-space:nowrap;">
                        <a href="INDEX.php?page=kelola-pendeta&edit=<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning" style="background:#f59e0b; color:#fff; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:13px; font-weight:600; margin-right:5px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                        <a href="INDEX.php?page=kelola-pendeta&delete=<?= (int)$r['id'] ?>" onclick="return confirm('Hapus profil pendeta ini?');" class="btn btn-sm btn-danger" style="background:#ef4444; color:#fff; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:13px; font-weight:600;"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="padding:15px; text-align:center; color:#6b7280;">Belum ada data pendeta yang diunggah.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 20px;">
    <a href="INDEX.php?page=admin-dashboard" style="display: inline-flex; align-items: center; gap: 8px; background: #e5e7eb; color: #374151; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>