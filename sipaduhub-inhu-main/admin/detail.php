<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
include '../config/koneksi.php';

$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'update_status') {
    $status_baru = $_POST['status'];
    $catatan = $_POST['catatan'];
    $id_user = $_SESSION['id_user'];

    $stmt = mysqli_prepare($koneksi, "UPDATE pengaduan SET status = ? WHERE id_pengaduan = ?");
    mysqli_stmt_bind_param($stmt, "si", $status_baru, $id);
    mysqli_stmt_execute($stmt);

    $stmt2 = mysqli_prepare($koneksi,
        "INSERT INTO riwayat_status (id_pengaduan, id_user, status, catatan) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt2, "iiss", $id, $id_user, $status_baru, $catatan);
    mysqli_stmt_execute($stmt2);

    header("Location: detail.php?id=$id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'toggle_sembunyikan') {
    $stmt = mysqli_prepare($koneksi, "UPDATE pengaduan SET disembunyikan = 1 - disembunyikan WHERE id_pengaduan = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: detail.php?id=$id");
    exit;
}

$stmt = mysqli_prepare($koneksi,
    "SELECT p.*, k.nama_kategori FROM pengaduan p 
     LEFT JOIN kategori k ON p.id_kategori = k.id_kategori WHERE p.id_pengaduan = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Pengaduan tidak ditemukan.");
}

$stmt2 = mysqli_prepare($koneksi,
    "SELECT r.*, u.nama AS nama_petugas FROM riwayat_status r 
     LEFT JOIN users u ON r.id_user = u.id_user 
     WHERE r.id_pengaduan = ? ORDER BY r.waktu ASC");
mysqli_stmt_bind_param($stmt2, "i", $id);
mysqli_stmt_execute($stmt2);
$riwayat = mysqli_stmt_get_result($stmt2);

$label_status = ['diterima'=>'Diterima','diproses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'];
$warna_status = ['diterima'=>'primary','diproses'=>'warning','selesai'=>'success','ditolak'=>'danger'];

$judul_halaman = "Detail Pengaduan";
$base_path = "../";
include '../includes/header.php';
?>

<nav class="navbar navbar-petugas navbar-dark">
  <div class="container flex-wrap gap-2">
    <span class="navbar-brand mb-0"><i class="bi bi-person-badge"></i> Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
    <div class="d-flex flex-wrap gap-2">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
      <a href="dokumentasi.php" class="btn btn-outline-light btn-sm">Dokumentasi Kegiatan</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4" style="max-width:700px;">
  <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-3">&larr; Kembali ke Dashboard</a>

  <div class="kartu-formulir mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h5 class="mb-0 font-display"><?= htmlspecialchars($data['ticket']) ?></h5>
      <div class="d-flex gap-2">
        <span class="badge bg-<?= $warna_status[$data['status']] ?? 'primary' ?>"><?= $label_status[$data['status']] ?></span>
        <?php if ($data['disembunyikan']): ?>
          <span class="badge bg-dark">Disembunyikan (Spam)</span>
        <?php endif; ?>
      </div>
    </div>
    <table class="table table-borderless mb-0">
      <tr><td class="text-muted" style="width:150px;">Pelapor</td><td><?= htmlspecialchars($data['nama_pelapor']) ?></td></tr>
      <tr><td class="text-muted">Nomor HP</td><td><?= htmlspecialchars($data['telepon']) ?></td></tr>
      <tr><td class="text-muted">Email</td><td><?= $data['email'] ? htmlspecialchars($data['email']) : '<span class="text-muted">Tidak diisi</span>' ?></td></tr>
      <tr><td class="text-muted">Kategori</td><td><?= htmlspecialchars($data['nama_kategori']) ?></td></tr>
      <tr><td class="text-muted">Lokasi</td><td><?= htmlspecialchars($data['lokasi']) ?></td></tr>
      <tr><td class="text-muted">Deskripsi</td><td><?= htmlspecialchars($data['deskripsi']) ?></td></tr>
      <tr><td class="text-muted">Diajukan</td><td><?= date('d M Y, H:i', strtotime($data['created_at'])) ?></td></tr>
      <tr><td class="text-muted">Foto</td>
        <td>
          <?php if ($data['foto']): ?>
            <img src="../<?= htmlspecialchars($data['foto']) ?>" class="img-fluid rounded" style="max-height:220px;">
          <?php else: ?>
            <span class="text-muted">Tidak ada foto</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr><td class="text-muted">Titik Lokasi</td>
        <td>
          <?php if ($data['latitude'] && $data['longitude']): ?>
            <a href="https://www.openstreetmap.org/?mlat=<?= $data['latitude'] ?>&mlon=<?= $data['longitude'] ?>#map=17/<?= $data['latitude'] ?>/<?= $data['longitude'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm">🗺️ Buka di Peta</a>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $data['latitude'] ?>,<?= $data['longitude'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">🧭 Rute ke Lokasi</a>
          <?php else: ?>
            <span class="text-muted">Tidak ditandai</span>
          <?php endif; ?>
        </td>
      </tr>
    </table>

    <form method="POST" class="mt-3">
      <input type="hidden" name="aksi" value="toggle_sembunyikan">
      <button type="submit" class="btn btn-sm btn-outline-dark" onclick="return confirm('<?= $data['disembunyikan'] ? 'Tampilkan kembali pengaduan ini?' : 'Sembunyikan pengaduan ini sebagai spam?' ?>')">
        <?= $data['disembunyikan'] ? '👁️ Tampilkan Kembali' : '🚫 Sembunyikan (Tandai Spam)' ?>
      </button>
    </form>
  </div>

  <div class="kartu-formulir mb-4">
    <h6 class="mb-3 font-display">Perbarui Status</h6>
    <form method="POST">
      <input type="hidden" name="aksi" value="update_status">
      <div class="mb-3">
        <select name="status" class="form-select">
          <?php foreach ($label_status as $key => $label): ?>
            <option value="<?= $key ?>" <?= $data['status'] == $key ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan untuk pelapor (opsional)"></textarea>
      </div>
      <button type="submit" class="btn btn-success">Simpan Perubahan</button>
    </form>
  </div>

  <div class="kartu-formulir">
    <h6 class="mb-3 font-display">Riwayat Status</h6>
    <ul class="list-group list-group-flush">
      <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
        <li class="list-group-item px-0">
          <span class="badge bg-<?= $warna_status[$r['status']] ?? 'primary' ?> me-2"><?= $label_status[$r['status']] ?></span>
          <span class="text-muted small">
            <?= date('d M Y, H:i', strtotime($r['waktu'])) ?>
            <?= $r['nama_petugas'] ? ' oleh ' . htmlspecialchars($r['nama_petugas']) : '' ?>
          </span>
          <?php if ($r['catatan']): ?>
            <div class="mt-1"><?= htmlspecialchars($r['catatan']) ?></div>
          <?php endif; ?>
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
</div>

<?php include '../includes/footer.php'; ?>