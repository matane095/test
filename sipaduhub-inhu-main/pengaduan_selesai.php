<?php
session_start();
include 'config/koneksi.php';
$judul_halaman = "Pengaduan Selesai";
$halaman_aktif = "selesai";
$base_path = "";
include 'includes/header.php';

$per_page = 9;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$total_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM dokumentasi_kegiatan"))['total'];
$total_halaman = max(1, ceil($total_data / $per_page));

$stmt = mysqli_prepare($koneksi,
    "SELECT d.*, p.ticket, p.lokasi, k.nama_kategori
     FROM dokumentasi_kegiatan d
     LEFT JOIN pengaduan p ON d.id_pengaduan = p.id_pengaduan
     LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
     ORDER BY d.tanggal_kegiatan DESC, d.created_at DESC
     LIMIT ? OFFSET ?");
mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);
?>

<div class="section-heading">
  <div class="container">
    <h1><i class="bi bi-check2-circle"></i> Pengaduan Selesai</h1>
    <p>Dokumentasi kegiatan penanganan pengaduan masyarakat yang sudah ditindaklanjuti oleh Bidang Lalu Lintas di lapangan.</p>
  </div>
</div>

<div class="container py-5">
  <?php if (mysqli_num_rows($data) == 0): ?>
    <div class="alert alert-secondary">Belum ada dokumentasi kegiatan yang ditambahkan.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <div class="col-md-6 col-lg-4">
          <div class="kartu-galeri">
            <?php if ($row['foto']): ?>
              <img src="<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
            <?php else: ?>
              <div class="kartu-galeri-placeholder"><i class="bi bi-image"></i></div>
            <?php endif; ?>
            <div class="kartu-galeri-isi">
              <div class="text-muted small mb-1"><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal_kegiatan'])) ?></div>
              <h6 class="mb-2"><?= htmlspecialchars($row['judul']) ?></h6>
              <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
              <?php if ($row['ticket']): ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <span class="badge badge-kategori fw-normal"><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></span>
                  <span class="badge bg-light text-dark border fw-normal"><code><?= htmlspecialchars($row['ticket']) ?></code></span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <?php if ($total_halaman > 1): ?>
      <nav class="mt-4">
        <ul class="pagination">
          <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
