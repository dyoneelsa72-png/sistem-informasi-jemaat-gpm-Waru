<?php
$facilityResult = mysqli_query($conn, "SELECT * FROM fasilitas_gereja ORDER BY id ASC");
$facilityRows = [];
if ($facilityResult) { while ($facilityRow = mysqli_fetch_assoc($facilityResult)) $facilityRows[] = $facilityRow; }
?>
<section class="page-header">
    <h1><i class="fa-solid fa-building"></i> Fasilitas Gereja</h1>
    <p>Sarana dan prasarana penunjang pelayanan Jemaat GPM Waru</p>
</section>
<div class="grid-3 mt-3">
<?php if ($facilityRows): foreach ($facilityRows as $f): ?>
    <article class="card facility-card">
        <?php if (!empty($f['foto']) && is_file(__DIR__ . '/../' . $f['foto'])): ?>
            <img src="<?= htmlspecialchars($f['foto']) ?>" alt="<?= htmlspecialchars($f['nama_fasilitas']) ?>" class="facility-photo">
        <?php else: ?>
            <div class="facility-icon"><i class="fa-solid fa-church"></i></div>
        <?php endif; ?>
        <h3><?= (int)$f['jumlah'] ?> <?= htmlspecialchars($f['nama_fasilitas']) ?></h3>
        <p><?= nl2br(htmlspecialchars($f['deskripsi'] ?? '')) ?></p>
    </article>
<?php endforeach; else: ?>
    <div class="card facility-card"><div class="facility-icon"><i class="fa-solid fa-house-user"></i></div><h3>Pastori</h3><p>Fasilitas tempat tinggal hamba Tuhan / Pendeta Jemaat GPM Waru.</p></div>
    <div class="card facility-card"><div class="facility-icon"><i class="fa-solid fa-church"></i></div><h3>Gedung Gereja</h3><p>Gedung Gereja Rehobot dan Gedung Gereja Imanuel.</p></div>
    <div class="card facility-card"><div class="facility-icon"><i class="fa-solid fa-boxes-packing"></i></div><h3>Inventaris Gereja</h3><p>Perlengkapan peribadahan dan pelayanan jemaat.</p></div>
<?php endif; ?>
</div>
