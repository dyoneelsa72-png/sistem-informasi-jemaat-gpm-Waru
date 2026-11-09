<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function e_jadwal($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$jadwalRows = [];
$resultJadwal = mysqli_query($conn, "SELECT id, nama_pelayanan, hari, waktu, waktu_tampilan, keterangan_minggu, lokasi FROM jadwal_pelayanan ORDER BY id ASC");
if ($resultJadwal) {
    while ($row = mysqli_fetch_assoc($resultJadwal)) $jadwalRows[] = $row;
}

// Ambil data warta jemaat untuk pengunjung
$wartaRows = [];
$resultWarta = mysqli_query($conn, "SELECT id, judul, tanggal, pdf_file FROM warta_jemaat ORDER BY id DESC");
if ($resultWarta) {
    while ($wRow = mysqli_fetch_assoc($resultWarta)) $wartaRows[] = $wRow;
}

function label_waktu_jadwal($row) {
    if (!empty($row['waktu_tampilan'])) return $row['waktu_tampilan'];
    if (!empty($row['waktu'])) {
        $jam = substr($row['waktu'], 0, 5);
        return $jam === '16:00' ? '04.00 (16.00 WIT)' : str_replace(':', '.', $jam) . ' WIT';
    }
    $nama = strtolower($row['nama_pelayanan'] ?? '');
    if (strpos($nama, 'katekisasi minggu') !== false) return 'Setelah Ibadah Minggu';
    if (strpos($nama, 'ibadah gatris') !== false) return 'Sesudah Sore';
    return 'Sesuai Jadwal';
}
?>

<section class="page-header">
    <h1><i class="fa-solid fa-calendar-days"></i> Tampilan Informasi Pelayanan Dalam Jemaat</h1>
    <p>Jadwal Ibadah Minggu dan Pelayanan Harian Jemaat GPM Waru</p>
</section>

<?php if (($_SESSION['role'] ?? '') === 'admin'): ?>

<?php endif; ?>

<!-- TABEL JADWAL PELAYANAN -->
<div class="card mt-3">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Jam / Waktu</th>
                    <th>Nama Pelayanan / Ibadah</th>
                    <th>Lokasi Pelayanan</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($jadwalRows): foreach ($jadwalRows as $row):
                $hariLabel = !empty($row['keterangan_minggu']) ? $row['keterangan_minggu'] : $row['hari'];
                if (strcasecmp($hariLabel, 'Minggu ke-4') === 0) $hariLabel = 'Minggu Ke-4';
                $badgeClass = $hariLabel === 'Minggu ke-4' || $hariLabel === 'Minggu Ke-4' ? 'bg-danger' : ( $row['hari'] === 'Minggu' ? 'bg-primary' : ($row['hari'] === 'Senin' ? 'bg-secondary' : ($row['hari'] === 'Jumat' ? 'bg-warning' : ($row['hari'] === 'Sabtu' ? 'bg-info' : 'bg-secondary'))));
                $highlight = stripos($hariLabel, 'Minggu ke-4') !== false;
            ?>
                <tr class="<?= $highlight ? 'highlight-row' : '' ?>">
                    <td><span class="badge <?= $badgeClass ?>"><?= e_jadwal($hariLabel) ?></span></td>
                    <td><?= e_jadwal(label_waktu_jadwal($row)) ?></td>
                    <td><?= $highlight ? '<strong>' . e_jadwal($row['nama_pelayanan']) . '</strong>' : e_jadwal($row['nama_pelayanan']) ?></td>
                    <td><?= e_jadwal($row['lokasi']) ?></td>
            
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="<?= (($_SESSION['role'] ?? '') === 'admin') ? 5 : 4 ?>">Belum ada data jadwal pelayanan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= FITUR WARTA JEMAAT (DI BAGIAN BAWAH) ================= -->
<div class="card mt-4" style="margin-top: 30px; padding: 20px; background: #fff; border-radius: 8px;">
    <h3><i class="fa-solid fa-book-open"></i> Warta Jemaat</h3>
    <p style="color: #6b7280; font-size: 14px;">Silakan pilih dan lihat dokumen Warta Jemaat mingguan secara langsung melalui pratinjau di bawah ini.</p>
    
    <div class="table-responsive" style="margin-top: 15px;">
        <table class="custom-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f3f4f6; text-align: left;">
                    <th style="padding: 10px;">Judul Warta</th>
                    <th style="padding: 10px;">Tanggal / Keterangan</th>
                    <th style="padding: 10px;">Aksi Pratinjau</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($wartaRows): foreach ($wartaRows as $wRow): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 10px; font-weight: 600;"><?= e_jadwal($wRow['judul']) ?></td>
                    <td style="padding: 10px;"><?= e_jadwal($wRow['tanggal']) ?></td>
                    <td style="padding: 10px;">
                        <?php if (!empty($wRow['pdf_file']) && file_exists('uploads/warta/' . $wRow['pdf_file'])): ?>
                            <button type="button" class="btn btn-sm btn-info" onclick="bukaModalPdf('uploads/warta/<?= e_jadwal($wRow['pdf_file']) ?>', '<?= e_jadwal($wRow['judul']) ?>')" style="background: #0284c7; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-eye"></i> Lihat Warta
                            </button>
                        <?php else: ?>
                            <span style="color: #9ca3af; font-size: 13px;">File PDF belum tersedia</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" style="padding: 15px; text-align: center; color: #6b7280;">Belum ada dokumen Warta Jemaat yang diunggah.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL VIEWER PDF WARTA (Tanpa Toolbar Download Langsung) -->
<div id="pdfModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; width: 85%; max-width: 900px; max-height: 90vh; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="padding: 14px 20px; background: #1f2937; color: #fff; display: flex; justify-content: space-between; align-items: center;">
            <h4 id="pdfModalTitle" style="margin: 0; font-size: 16px;">Pratinjau Warta Jemaat</h4>
            <button type="button" onclick="tutupModalPdf()" style="background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="padding: 15px; flex-grow: 1; height: 75vh; background: #f9fafb;">
            <!-- Menggunakan #toolbar=0&navpanes=0 untuk menyembunyikan tool download bawaan browser -->
            <iframe id="pdfFrame" src="" style="width: 100%; height: 100%; border: 1px solid #d1d5db; border-radius: 6px;"></iframe>
        </div>
    </div>
</div>

<script>
function bukaModalPdf(pdfUrl, title) {
    document.getElementById('pdfModalTitle').innerText = 'Pratinjau: ' + title;
    document.getElementById('pdfFrame').src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0';
    document.getElementById('pdfModal').style.display = 'flex';
}

function tutupModalPdf() {
    document.getElementById('pdfModal').style.display = 'none';
    document.getElementById('pdfFrame').src = '';
}

window.onclick = function(event) {
    let modal = document.getElementById('pdfModal');
    if (event.target === modal) {
        tutupModalPdf();
    }
}
</script>