<?php
session_start();
include 'config/koneksi.php';
$judul_halaman = "Pengaduan Selesai";
$halaman_aktif = "selesai";
$base_path = "";
include 'includes/header.php';

$filter_kategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;

$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$where = " WHERE p.status = 'selesai' AND p.email_terverifikasi = 1 AND p.disembunyikan = 0";
$types = ""; $params = [];
if ($filter_kategori > 0) {
    $where .= " AND p.id_kategori = ?";
    $types .= "i";
    $params[] = $filter_kategori;
}

$sql_count = "SELECT COUNT(*) AS total FROM pengaduan p" . $where;
$stmt_count = mysqli_prepare($koneksi, $sql_count);
if ($types != '') mysqli_stmt_bind_param($stmt_count, $types, ...$params);
mysqli_stmt_execute($stmt_count);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
$total_halaman = max(1, ceil($total_data / $per_page));

// Hanya kolom yang aman ditampilkan ke publik: tiket, kategori, lokasi (teks umum), tanggal.
// Nama, telepon, email, dan foto pelapor TIDAK ditampilkan di sini.
$sql = "SELECT p.ticket, p.lokasi, p.created_at, k.nama_kategori
        FROM pengaduan p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori"
        . $where . " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$types_data = $types . "ii";
$params_data = array_merge($params, [$per_page, $offset]);
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, $types_data, ...$params_data);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);

$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

function buatLinkHalamanSelesai($halaman, $filter_kategori) {
    return '?' . http_build_query(['kategori' => $filter_kategori, 'page' => $halaman]);
}
?>

<div class="container py-5">
  <h2 class="mb-1">Pengaduan Selesai</h2>
  <p class="text-muted mb-4">Daftar pengaduan masyarakat yang sudah ditindaklanjuti dan diselesaikan. Data pribadi pelapor tidak ditampilkan.</p>

  <form method="GET" class="row g-2 mb-4">
    <div class="col-auto">
      <select name="kategori" class="form-select" onchange="this.form.submit()">
        <option value="0">Semua Kategori</option>
        <?php mysqli_data_seek($kategori_list, 0); while ($k = mysqli_fetch_assoc($kategori_list)): ?>
          <option value="<?= $k['id_kategori'] ?>" <?= $filter_kategori == $k['id_kategori'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
  </form>

  <?php if (mysqli_num_rows($data) == 0): ?>
    <div class="alert alert-secondary">Belum ada pengaduan selesai untuk kategori ini.</div>
  <?php else: ?>
    <div class="card shadow-sm table-scroll-wrap">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr><th>Tiket</th><th>Kategori</th><th>Lokasi</th><th>Tanggal Dilaporkan</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($data)): ?>
            <tr>
              <td><code><?= htmlspecialchars($row['ticket']) ?></code></td>
              <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
              <td><?= htmlspecialchars($row['lokasi']) ?></td>
              <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
              <td><span class="badge bg-success">Selesai</span></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_halaman > 1): ?>
      <nav class="mt-3">
        <ul class="pagination">
          <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= buatLinkHalamanSelesai($i, $filter_kategori) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
