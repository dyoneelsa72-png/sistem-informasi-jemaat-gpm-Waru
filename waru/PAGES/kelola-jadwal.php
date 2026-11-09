<?php
// ==========================================
// KELOLA JADWAL PELAYANAN & WARTA JEMAAT
// ==========================================

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
function e_kelola_jadwal($value) { 
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
}

$pesanJadwal = '';
$errorJadwal = '';
$pesanWarta = '';
$errorWarta = '';

// Folder penyimpanan PDF Warta
$uploadDirWarta = 'uploads/warta/';
if (!is_dir($uploadDirWarta)) {
    mkdir($uploadDirWarta, 0755, true);
}

// =========================================================================
// OTOMATIS HAPUS WARTA JEMAAT YANG LEBIH DARI 1 HARI (24 JAM)
// =========================================================================
$queryExpiredWarta = mysqli_query($conn, "SELECT id, pdf_file, created_at FROM warta_jemaat");
if ($queryExpiredWarta) {
    while ($expRow = mysqli_fetch_assoc($queryExpiredWarta)) {
        $waktuUpload = strtotime($expRow['created_at']);
        $selisihWaktu = time() - $waktuUpload; // Selisih dalam detik
        $satuHariDetik = 86400; // 24 * 60 * 60 detik (1 hari)

        if ($selisihWaktu > $satuHariDetik) {
            $expId = (int)$expRow['id'];
            if (!empty($expRow['pdf_file']) && file_exists($uploadDirWarta . $expRow['pdf_file'])) {
                @unlink($uploadDirWarta . $expRow['pdf_file']);
            }
            $stmtDelExp = mysqli_prepare($conn, "DELETE FROM warta_jemaat WHERE id = ?");
            if ($stmtDelExp) {
                mysqli_stmt_bind_param($stmtDelExp, "i", $expId);
                mysqli_stmt_execute($stmtDelExp);
                mysqli_stmt_close($stmtDelExp);
            }
        }
    }
}

// 4. Proses Hapus Data Jadwal (DELETE)
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId > 0) {
        $stmtDel = mysqli_prepare($conn, "DELETE FROM jadwal_pelayanan WHERE id = ?");
        if ($stmtDel) {
            mysqli_stmt_bind_param($stmtDel, "i", $deleteId);
            if (mysqli_stmt_execute($stmtDel)) {
                mysqli_stmt_close($stmtDel);
                echo '<script>window.location.href = "INDEX.php?page=kelola-jadwal&deleted=1";</script>';
                exit;
            }
            mysqli_stmt_close($stmtDel);
        }
    }
}

// 5. Proses Hapus Data Warta (DELETE)
if (isset($_GET['delete_warta'])) {
    $deleteWartaId = (int)$_GET['delete_warta'];
    if ($deleteWartaId > 0) {
        $stmtCekWarta = mysqli_prepare($conn, "SELECT pdf_file FROM warta_jemaat WHERE id = ?");
        if ($stmtCekWarta) {
            mysqli_stmt_bind_param($stmtCekWarta, "i", $deleteWartaId);
            mysqli_stmt_execute($stmtCekWarta);
            $resCekWarta = mysqli_stmt_get_result($stmtCekWarta);
            if ($rowCW = mysqli_fetch_assoc($resCekWarta)) {
                if (!empty($rowCW['pdf_file']) && file_exists($uploadDirWarta . $rowCW['pdf_file'])) {
                    unlink($uploadDirWarta . $rowCW['pdf_file']);
                }
            }
            mysqli_stmt_close($stmtCekWarta);
        }

        $stmtDelWarta = mysqli_prepare($conn, "DELETE FROM warta_jemaat WHERE id = ?");
        if ($stmtDelWarta) {
            mysqli_stmt_bind_param($stmtDelWarta, "i", $deleteWartaId);
            if (mysqli_stmt_execute($stmtDelWarta)) {
                mysqli_stmt_close($stmtDelWarta);
                echo '<script>window.location.href = "INDEX.php?page=kelola-jadwal&warta_deleted=1";</script>';
                exit;
            }
            mysqli_stmt_close($stmtDelWarta);
        }
    }
}

// 6. Proses Penyimpanan Jadwal (Tambah / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_jadwal'])) {
    $id = (int)($_POST['id'] ?? 0);
    $hari = trim($_POST['hari'] ?? '');
    $nama = trim($_POST['nama_pelayanan'] ?? '');
    $waktuTampilan = trim($_POST['waktu_tampilan'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');

    if ($hari === '' || $nama === '' || $lokasi === '') {
        $errorJadwal = 'Hari, Nama Pelayanan, dan Lokasi wajib diisi.';
    } else {
        $waktuDb = null;
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $waktuTampilan, $match)) {
            $waktuDb = $waktuTampilan . ':00';
        }

        if ($id > 0) {
            // Update Jadwal
            $stmt = mysqli_prepare($conn, "UPDATE jadwal_pelayanan SET nama_pelayanan=?, hari=?, waktu=?, waktu_tampilan=?, lokasi=? WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssi", $nama, $hari, $waktuDb, $waktuTampilan, $lokasi, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                echo '<script>window.location.href = "INDEX.php?page=kelola-jadwal&updated=1";</script>';
                exit;
            }
        } else {
            // Tambah Jadwal Baru
            $stmt = mysqli_prepare($conn, "INSERT INTO jadwal_pelayanan (nama_pelayanan, hari, waktu, waktu_tampilan, lokasi) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssss", $nama, $hari, $waktuDb, $waktuTampilan, $lokasi);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                echo '<script>window.location.href = "INDEX.php?page=kelola-jadwal&added=1";</script>';
                exit;
            }
        }
    }
}

// 7. Proses Penyimpanan Warta Jemaat (Tambah / Edit & Upload PDF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_warta'])) {
    $wartaId = (int)($_POST['warta_id'] ?? 0);
    $judulWarta = trim($_POST['judul'] ?? '');
    $tanggalWarta = trim($_POST['tanggal'] ?? '');

    if ($judulWarta === '' || $tanggalWarta === '') {
        $errorWarta = 'Judul dan Tanggal Warta wajib diisi.';
    } else {
        $pdfFileName = null;
        if ($wartaId > 0) {
            $stmtWU = mysqli_prepare($conn, "SELECT pdf_file FROM warta_jemaat WHERE id = ?");
            mysqli_stmt_bind_param($stmtWU, "i", $wartaId);
            mysqli_stmt_execute($stmtWU);
            $resWU = mysqli_stmt_get_result($stmtWU);
            if ($rowWU = mysqli_fetch_assoc($resWU)) {
                $pdfFileName = $rowWU['pdf_file'];
            }
            mysqli_stmt_close($stmtWU);
        }

        if (isset($_FILES['pdf_warta']) && $_FILES['pdf_warta']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['pdf_warta']['tmp_name'];
            $fileName = $_FILES['pdf_warta']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension === 'pdf') {
                $newFileName = 'warta_' . time() . '_' . mt_rand(1000, 9999) . '.pdf';
                $dest_path = $uploadDirWarta . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if (!empty($pdfFileName) && file_exists($uploadDirWarta . $pdfFileName)) {
                        unlink($uploadDirWarta . $pdfFileName);
                    }
                    $pdfFileName = $newFileName;
                } else {
                    $errorWarta = 'Gagal mengunggah file PDF Warta.';
                }
            } else {
                $errorWarta = 'Format file Warta harus berupa PDF.';
            }
        }

        if (empty($errorWarta)) {
            if ($wartaId > 0) {
                $stmtUp = mysqli_prepare($conn, "UPDATE warta_jemaat SET judul=?, tanggal=?, pdf_file = COALESCE(?, pdf_file), created_at = NOW() WHERE id=?");
                mysqli_stmt_bind_param($stmtUp, "sssi", $judulWarta, $tanggalWarta, $pdfFileName, $wartaId);
                mysqli_stmt_execute($stmtUp);
                mysqli_stmt_close($stmtUp);
            } else {
                if (empty($pdfFileName)) {
                    $errorWarta = 'File PDF Warta wajib diunggah untuk data baru.';
                } else {
                    $stmtIns = mysqli_prepare($conn, "INSERT INTO warta_jemaat (judul, tanggal, pdf_file, created_at) VALUES (?, ?, ?, NOW())");
                    mysqli_stmt_bind_param($stmtIns, "sss", $judulWarta, $tanggalWarta, $pdfFileName);
                    mysqli_stmt_execute($stmtIns);
                    mysqli_stmt_close($stmtIns);
                }
            }

            if (empty($errorWarta)) {
                echo '<script>window.location.href = "INDEX.php?page=kelola-jadwal&warta_saved=1";</script>';
                exit;
            }
        }
    }
}

// Notifikasi URL
if (isset($_GET['updated'])) { $pesanJadwal = 'Jadwal berhasil diperbarui.'; }
if (isset($_GET['added'])) { $pesanJadwal = 'Jadwal berhasil ditambahkan.'; }
if (isset($_GET['deleted'])) { $pesanJadwal = 'Jadwal berhasil dihapus.'; }
if (isset($_GET['warta_saved'])) { $pesanWarta = 'Warta jemaat berhasil disimpan.'; }
if (isset($_GET['warta_deleted'])) { $pesanWarta = 'Warta jemaat berhasil dihapus.'; }

// Ambil data untuk Edit Jadwal (Hanya 1 data untuk form tunggal)
$editId = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($editId > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, nama_pelayanan, hari, waktu, waktu_tampilan, lokasi FROM jadwal_pelayanan WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $editId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $editRow = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
    }
}

// Ambil data untuk Edit Warta
$editWartaId = (int)($_GET['edit_warta'] ?? 0);
$editWartaRow = null;
if ($editWartaId > 0) {
    $stmtWEdit = mysqli_prepare($conn, "SELECT id, judul, tanggal, pdf_file FROM warta_jemaat WHERE id=?");
    if ($stmtWEdit) {
        mysqli_stmt_bind_param($stmtWEdit, "i", $editWartaId);
        mysqli_stmt_execute($stmtWEdit);
        $resWE = mysqli_stmt_get_result($stmtWEdit);
        $editWartaRow = $resWE ? mysqli_fetch_assoc($resWE) : null;
        mysqli_stmt_close($stmtWEdit);
    }
}

// Ambil daftar keseluruhan jadwal & warta
$list = mysqli_query($conn, "SELECT id, nama_pelayanan, hari, waktu, waktu_tampilan, lokasi FROM jadwal_pelayanan ORDER BY id ASC");
$listWarta = mysqli_query($conn, "SELECT id, judul, tanggal, pdf_file FROM warta_jemaat ORDER BY id DESC");
?>

<!-- HEADER HALAMAN -->
<section class="page-header">
    <h1><i class="fa-solid fa-calendar-pen"></i> Kelola Jadwal & Warta Pelayanan</h1>
    <p>Tambah, edit, atau hapus jadwal ibadah dan upload Warta Jemaat PDF.</p>
</section>

<!-- NOTIFIKASI -->
<?php if ($pesanJadwal): ?><div class="alert alert-success mt-3" style="padding:12px; background:#d1fae5; color:#065f46; border-radius:6px; margin-bottom:15px;"><?= e_kelola_jadwal($pesanJadwal) ?></div><?php endif; ?>
<?php if ($errorJadwal): ?><div class="alert alert-danger mt-3" style="padding:12px; background:#fee2e2; color:#991b1b; border-radius:6px; margin-bottom:15px;"><?= e_kelola_jadwal($errorJadwal) ?></div><?php endif; ?>
<?php if ($pesanWarta): ?><div class="alert alert-success mt-3" style="padding:12px; background:#d1fae5; color:#065f46; border-radius:6px; margin-bottom:15px;"><?= e_kelola_jadwal($pesanWarta) ?></div><?php endif; ?>
<?php if ($errorWarta): ?><div class="alert alert-danger mt-3" style="padding:12px; background:#fee2e2; color:#991b1b; border-radius:6px; margin-bottom:15px;"><?= e_kelola_jadwal($errorWarta) ?></div><?php endif; ?>

<!-- FORM TUNGGAL (UNTUK TAMBAH ATAU EDIT JADWAL) -->
<div class="card mt-3" style="padding:20px; background: #fff; border-radius: 8px; margin-bottom: 25px;">
    <h3><?= $editRow ? 'Edit Jadwal: ' . e_kelola_jadwal($editRow['nama_pelayanan']) : 'Tambah Jadwal Pelayanan Baru' ?></h3>
    <form method="post" action="INDEX.php?page=kelola-jadwal<?= $editRow ? '&edit=' . (int)$editRow['id'] : '' ?>">
        <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
        
        <div class="form-group mb-3" style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Hari</label>
            <select class="form-control" name="hari" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
                <?php foreach (['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h): ?>
                <option value="<?= $h ?>" <?= ($editRow && $editRow['hari'] === $h) ? 'selected' : '' ?>><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3" style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Jam / Waktu</label>
            <input class="form-control" name="waktu_tampilan" type="text" placeholder="Contoh: 09.00 WIT" value="<?= $editRow ? e_kelola_jadwal($editRow['waktu_tampilan']) : '' ?>" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div class="form-group mb-3" style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Nama Pelayanan / Ibadah</label>
            <input class="form-control" name="nama_pelayanan" type="text" maxlength="100" placeholder="Contoh: Ibadah Minggu Pagi" value="<?= $editRow ? e_kelola_jadwal($editRow['nama_pelayanan']) : '' ?>" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div class="form-group mb-3" style="margin-bottom: 20px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Lokasi Pelayanan</label>
            <input class="form-control" name="lokasi" type="text" maxlength="150" placeholder="Contoh: Gedung Gereja GPM Waru" value="<?= $editRow ? e_kelola_jadwal($editRow['lokasi']) : '' ?>" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary" type="submit" name="simpan_jadwal" style="background: <?= $editRow ? '#10b981' : '#2563eb' ?>; color: white; padding: 8px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-floppy-disk"></i> <?= $editRow ? 'Simpan Perubahan' : 'Simpan Jadwal Baru' ?>
            </button>
            <?php if ($editRow): ?>
                <a class="btn btn-secondary" href="INDEX.php?page=kelola-jadwal" style="background: #6b7280; color: white; padding: 8px 16px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center;">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABEL DAFTAR JADWAL -->
<div class="card mt-3" style="padding:20px; background: #fff; border-radius: 8px; margin-top: 20px;">
    <h3>Daftar Jadwal Pelayanan</h3>
    <div class="table-responsive">
        <table class="custom-table" style="width:100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background: #f3f4f6; text-align: left;">
                    <th style="padding: 10px;">Hari</th>
                    <th style="padding: 10px;">Jam / Waktu</th>
                    <th style="padding: 10px;">Nama Pelayanan / Ibadah</th>
                    <th style="padding: 10px;">Lokasi</th>
                    <th style="padding: 10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($list && mysqli_num_rows($list) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($list)): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 10px;"><?= e_kelola_jadwal($row['hari']) ?></td>
                    <td style="padding: 10px;"><?= e_kelola_jadwal($row['waktu_tampilan']) ?></td>
                    <td style="padding: 10px;"><?= e_kelola_jadwal($row['nama_pelayanan']) ?></td>
                    <td style="padding: 10px;"><?= e_kelola_jadwal($row['lokasi']) ?></td>
                    <td style="padding: 10px; display: flex; gap: 6px;">
                        <a class="btn btn-sm btn-warning" href="INDEX.php?page=kelola-jadwal&edit=<?= (int)$row['id'] ?>" style="background: #fbbf24; color: #000; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 600;"><i class="fa-solid fa-pen"></i> Edit</a>
                        <a class="btn btn-sm btn-danger" href="INDEX.php?page=kelola-jadwal&delete=<?= (int)$row['id'] ?>" onclick="return confirm('Hapus jadwal ini?');" style="background: #ef4444; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 600;"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="padding: 15px; text-align: center; color: #6b7280;">Belum ada data jadwal pelayanan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= BAGIAN KELOLA WARTA JEMAAT ================= -->
<div class="card mt-4" style="padding:20px; background: #fff; border-radius: 8px; margin-top: 30px;">
    <h3><?= $editWartaRow ? 'Edit Warta Jemaat' : 'Tambah Warta Jemaat (PDF)' ?></h3>
    <form method="post" action="INDEX.php?page=kelola-jadwal" enctype="multipart/form-data">
        <input type="hidden" name="warta_id" value="<?= $editWartaRow ? (int)$editWartaRow['id'] : 0 ?>">
        
        <div class="form-group mb-3" style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Judul Warta</label>
            <input class="form-control" name="judul" type="text" maxlength="150" value="<?= $editWartaRow ? e_kelola_jadwal($editWartaRow['judul']) : '' ?>" placeholder="Contoh: Warta Jemaat Minggu, 10 November 2026" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div class="form-group mb-3" style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Tanggal / Keterangan Waktu</label>
            <input class="form-control" name="tanggal" type="text" maxlength="100" value="<?= $editWartaRow ? e_kelola_jadwal($editWartaRow['tanggal']) : '' ?>" placeholder="Contoh: 10 November 2026" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div class="form-group mb-3" style="margin-bottom: 20px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Upload File PDF Warta</label>
            <?php if ($editWartaRow && !empty($editWartaRow['pdf_file'])): ?>
                <p style="font-size: 13px; color: #059669; margin-bottom: 5px;"><i class="fa-solid fa-file-pdf"></i> File saat ini: <?= e_kelola_jadwal($editWartaRow['pdf_file']) ?></p>
            <?php endif; ?>
            <input type="file" name="pdf_warta" accept=".pdf" class="form-control" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
            <small style="color: #6b7280; font-size: 12px;">Format file wajib .pdf. Warta akan otomatis hilang dalam waktu 1 hari. <?= $editWartaRow ? 'Kosongkan jika tidak ingin mengganti file.' : '' ?></small>
        </div>

        <button class="btn btn-primary" type="submit" name="simpan_warta" style="background: #2563eb; color: white; padding: 8px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer;"><i class="fa-solid fa-cloud-arrow-up"></i> <?= $editWartaRow ? 'Perbarui Warta' : 'Upload Warta Baru' ?></button>
        <?php if ($editWartaRow): ?>
            <a class="btn btn-secondary" href="INDEX.php?page=kelola-jadwal" style="background: #6b7280; color: white; padding: 8px 16px; border-radius:6px; text-decoration:none; font-weight:600; margin-left: 8px;">Batal</a>
        <?php endif; ?>
    </form>

    <h4 style="margin-top: 30px; margin-bottom: 10px;">Daftar Warta Jemaat <small style="font-size: 12px; color: #ef4444; font-weight: normal;">(Otomatis terhapus setelah 1 hari)</small></h4>
    <div class="table-responsive">
        <table class="custom-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f3f4f6; text-align: left;">
                    <th style="padding: 10px;">Judul Warta</th>
                    <th style="padding: 10px;">Tanggal</th>
                    <th style="padding: 10px;">File PDF</th>
                    <th style="padding: 10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($listWarta && mysqli_num_rows($listWarta) > 0): ?>
                <?php while ($wRow = mysqli_fetch_assoc($listWarta)): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 10px;"><?= e_kelola_jadwal($wRow['judul']) ?></td>
                    <td style="padding: 10px;"><?= e_kelola_jadwal($wRow['tanggal']) ?></td>
                    <td style="padding: 10px;"><a href="uploads/warta/<?= e_kelola_jadwal($wRow['pdf_file']) ?>" target="_blank" style="color: #2563eb; text-decoration: none;"><i class="fa-solid fa-file-pdf"></i> <?= e_kelola_jadwal($wRow['pdf_file']) ?></a></td>
                    <td style="padding: 10px; display: flex; gap: 6px;">
                        <a class="btn btn-sm btn-warning" href="INDEX.php?page=kelola-jadwal&edit_warta=<?= (int)$wRow['id'] ?>" style="background: #fbbf24; color: #000; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 600;"><i class="fa-solid fa-pen"></i> Edit</a>
                        <a class="btn btn-sm btn-danger" href="INDEX.php?page=kelola-jadwal&delete_warta=<?= (int)$wRow['id'] ?>" onclick="return confirm('Hapus warta ini?');" style="background: #ef4444; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 600;"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="padding: 15px; text-align: center; color: #6b7280;">Belum ada data warta jemaat atau warta telah kedaluwarsa (> 1 hari).</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tombol Kembali ke Dashboard -->
<div style="display: flex; justify-content: flex-end; margin-top: 30px; margin-bottom: 10px;">
    <a href="INDEX.php?page=admin-dashboard" style="display: inline-flex; align-items: center; gap: 8px; background: #e5e7eb; color: #374151; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>