<?php
// ==========================================
// KELOLA FASILITAS GEREJA
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: INDEX.php?page=login');
    exit;
}

$dir = __DIR__ . '/../uploads/fasilitas/';
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_fasilitas'] ?? '');
    $jumlah = max(1, (int)($_POST['jumlah'] ?? 1));
    $desc = trim($_POST['deskripsi'] ?? '');
    $foto = null;

    // Proses upload foto tanpa batas maksimal ukuran file
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['foto'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        // Hanya memvalidasi ekstensi file, batasan ukuran ($f['size']) telah dihapus
        if (in_array($ext, $allowed, true)) {
            $new = 'fasilitas_' . bin2hex(random_bytes(5)) . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $dir . $new)) {
                $foto = 'uploads/fasilitas/' . $new;
            }
        }
    }

    if ($act === 'simpan' && $nama !== '') {
        if ($id > 0) {
            if ($foto) {
                $q = mysqli_prepare($conn, 'UPDATE fasilitas_gereja SET nama_fasilitas=?, jumlah=?, deskripsi=?, foto=? WHERE id=?');
                mysqli_stmt_bind_param($q, 'sissi', $nama, $jumlah, $desc, $foto, $id);
            } else {
                $q = mysqli_prepare($conn, 'UPDATE fasilitas_gereja SET nama_fasilitas=?, jumlah=?, deskripsi=? WHERE id=?');
                mysqli_stmt_bind_param($q, 'sisi', $nama, $jumlah, $desc, $id);
            }
        } else {
            $q = mysqli_prepare($conn, 'INSERT INTO fasilitas_gereja (nama_fasilitas, jumlah, deskripsi, foto) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($q, 'siss', $nama, $jumlah, $desc, $foto);
        }
        mysqli_stmt_execute($q);
        mysqli_stmt_close($q);
        $msg = 'Data fasilitas berhasil disimpan.';
    }

    if ($act === 'hapus' && $id) {
        // Hapus file fisik jika ada
        $qCek = mysqli_prepare($conn, 'SELECT foto FROM fasilitas_gereja WHERE id=?');
        mysqli_stmt_bind_param($qCek, 'i', $id);
        mysqli_stmt_execute($qCek);
        $resCek = mysqli_stmt_get_result($qCek);
        if ($rowCek = mysqli_fetch_assoc($resCek)) {
            if (!empty($rowCek['foto']) && file_exists(__DIR__ . '/../' . $rowCek['foto'])) {
                @unlink(__DIR__ . '/../' . $rowCek['foto']);
            }
        }
        mysqli_stmt_close($qCek);

        $q = mysqli_prepare($conn, 'DELETE FROM fasilitas_gereja WHERE id=?');
        mysqli_stmt_bind_param($q, 'i', $id);
        mysqli_stmt_execute($q);
        mysqli_stmt_close($q);
        $msg = 'Data fasilitas dihapus.';
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $q = mysqli_prepare($conn, 'SELECT * FROM fasilitas_gereja WHERE id=?');
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
    mysqli_stmt_close($q);
}

$list = mysqli_query($conn, 'SELECT * FROM fasilitas_gereja ORDER BY id DESC');
?>

<section class="page-header">
    <h1><i class="fa-solid fa-building"></i> Kelola Fasilitas Gereja</h1>
    <p>Tambah, ubah, dan hapus fasilitas beserta foto.</p>
</section>

<div class="card mt-3">
    <h3><?= $edit ? 'Ubah Fasilitas' : 'Tambah Fasilitas' ?></h3>
    <?php if ($msg): ?>
        <div class="notice notice-success" style="padding: 10px; background: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 15px;"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
        
        <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <label style="display: block; font-weight: 600;">Nama Fasilitas
                <input class="form-control" name="nama_fasilitas" required value="<?= htmlspecialchars($edit['nama_fasilitas'] ?? '') ?>" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #d1d5db; border-radius: 6px;">
            </label>
            <label style="display: block; font-weight: 600;">Jumlah
                <input class="form-control" type="number" min="1" name="jumlah" value="<?= (int)($edit['jumlah'] ?? 1) ?>" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #d1d5db; border-radius: 6px;">
            </label>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600;">Deskripsi
                <textarea class="form-control" name="deskripsi" rows="3" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #d1d5db; border-radius: 6px;"><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
            </label>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600;">Foto (Format: JPG, PNG, WEBP)
                <?php if ($edit && !empty($edit['foto'])): ?>
                    <div style="margin-bottom: 5px; font-size: 12px; color: #059669;">Foto saat ini tersedia. Unggah file baru jika ingin mengganti.</div>
                <?php endif; ?>
                <input class="form-control" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #d1d5db; border-radius: 6px;">
            </label>
        </div>

        <!-- Tombol Simpan & Batal Sejajar / Rapi -->
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button class="btn btn-primary" style="flex: 1; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Simpan</button>
            <?php if ($edit): ?>
                <a class="btn btn-secondary" href="INDEX.php?page=kelola-fasilitas" style="flex: 1; text-align: center; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; background: #6b7280; color: white; padding: 10px; border-radius: 6px; font-weight: 600;">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card mt-3" style="margin-top: 25px; padding: 20px; background: #fff; border-radius: 8px;">
    <h3>Daftar Fasilitas Gereja</h3>
    <div class="table-responsive" style="margin-top: 15px;">
        <table class="custom-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f3f4f6; text-align: left;">
                    <th style="padding: 10px;">Foto</th>
                    <th style="padding: 10px;">Nama</th>
                    <th style="padding: 10px;">Jumlah</th>
                    <th style="padding: 10px;">Deskripsi</th>
                    <th style="padding: 10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($list && mysqli_num_rows($list) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($list)): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 10px;">
                                <?php if ($r['foto'] && is_file(__DIR__ . '/../' . $r['foto'])): ?>
                                    <img class="admin-thumb" src="<?= htmlspecialchars($r['foto']) ?>" alt="Foto fasilitas" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($r['nama_fasilitas']) ?></td>
                            <td style="padding: 10px;"><?= (int)$r['jumlah'] ?></td>
                            <td style="padding: 10px; color: #4b5563;"><?= htmlspecialchars($r['deskripsi'] ?? '') ?></td>
                            <td style="padding: 10px;">
                                <!-- Container Aksi dengan jarak (gap) agar tombol Edit dan Hapus tidak menempel -->
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <a class="btn btn-small btn-primary" href="INDEX.php?page=kelola-fasilitas&edit=<?= (int)$r['id'] ?>" style="background: #f59e0b; color: #fff; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 600;">Edit</a>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Hapus fasilitas ini?')" style="margin: 0;">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button class="btn btn-small btn-danger" style="background: #ef4444; color: #fff; border: none; padding: 5px 12px; border-radius: 4px; font-size: 13px; font-weight: 600; cursor: pointer;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 15px; text-align: center; color: #6b7280;">Belum ada data fasilitas gereja.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tombol Kembali ke Dashboard Admin -->
<div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 20px;">
    <a href="INDEX.php?page=admin-dashboard" style="display: inline-flex; align-items: center; gap: 8px; background: #e5e7eb; color: #374151; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: background 0.2s;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>