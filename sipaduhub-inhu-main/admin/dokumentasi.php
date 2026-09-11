<?php
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include '../config/koneksi.php';

$pesan = null;
$error = null;

// --- Tambah dokumentasi ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tanggal_kegiatan = $_POST['tanggal_kegiatan'];
    $id_pengaduan = !empty($_POST['id_pengaduan']) ? (int) $_POST['id_pengaduan'] : null;

    if (strlen($judul) < 3 || strlen($deskripsi) < 5 || empty($tanggal_kegiatan)) {
        $error = "Judul, deskripsi, dan tanggal kegiatan wajib diisi dengan benar.";
    } else {
        $foto_path = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $tipe = mime_content_type($_FILES['foto']['tmp_name']);
            $ukuran_max = 5 * 1024 * 1024;

            if (!in_array($tipe, $allowed) || $_FILES['foto']['size'] > $ukuran_max) {
                $error = "Foto tidak valid. Gunakan format JPG/PNG/WebP, maksimal 5MB.";
            } else {
                $ekstensi = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nama_file = 'kegiatan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
                $tujuan = __DIR__ . '/../uploads/kegiatan/' . $nama_file;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
                    $foto_path = 'uploads/kegiatan/' . $nama_file;
                }
            }
        }

        if (!$error) {
            $id_user = $_SESSION['id_user'];
            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO dokumentasi_kegiatan (id_pengaduan, id_user, judul, deskripsi, foto, tanggal_kegiatan)
                 VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iissss", $id_pengaduan, $id_user, $judul, $deskripsi, $foto_path, $tanggal_kegiatan);
            mysqli_stmt_execute($stmt);
            $pesan = "Dokumentasi kegiatan '$judul' berhasil ditambahkan dan langsung tampil di halaman publik.";
        }
    }
}

// --- Hapus dokumentasi ---
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "SELECT foto FROM dokumentasi_kegiatan WHERE id_dokumentasi = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_hapus);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($row) {
        if ($row['foto'] && file_exists(__DIR__ . '/../' . $row['foto'])) {
            unlink(__DIR__ . '/../' . $row['foto']);
        }
        $stmt2 = mysqli_prepare($koneksi, "DELETE FROM dokumentasi_kegiatan WHERE id_dokumentasi = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id_hapus);
        mysqli_stmt_execute($stmt2);
    }
    header("Location: dokumentasi.php");
    exit;
}

// Dropdown pengaduan yang bisa dikaitkan (yang sudah diproses/selesai, terbaru dulu)
$daftar_pengaduan = mysqli_query($koneksi,
    "SELECT id_pengaduan, ticket, lokasi FROM pengaduan
     WHERE email_terverifikasi = 1 AND status IN ('diproses','selesai')
     ORDER BY created_at DESC LIMIT 100");

$daftar_dokumentasi = mysqli_query($koneksi,
    "SELECT d.*, p.ticket FROM dokumentasi_kegiatan d
     LEFT JOIN pengaduan p ON d.id_pengaduan = p.id_pengaduan
     ORDER BY d.tanggal_kegiatan DESC, d.created_at DESC");

$judul_halaman = "Dokumentasi Kegiatan";
$base_path = "../";
include '../includes/header.php';
?>

<nav class="navbar navbar-petugas navbar-dark">
  <div class="container flex-wrap gap-2">
    <span class="navbar-brand mb-0"><i class="bi bi-person-badge"></i> Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
    <div class="d-flex flex-wrap gap-2">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-1">Dokumentasi Kegiatan</h4>
  <p class="text-muted mb-4">Isi di sini setiap kegiatan penanganan pengaduan yang sudah dikerjakan di lapangan. Entri ini langsung muncul di halaman publik "Pengaduan Selesai".</p>

  <?php if ($pesan): ?><div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="kartu-formulir mb-4">
    <h6 class="mb-3 font-display"><i class="bi bi-camera" style="color: var(--color-accent-dark);"></i> Tambah Dokumentasi Baru</h6>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="aksi" value="tambah">

      <div class="col-md-8">
        <label class="form-label">Judul Kegiatan</label>
        <input type="text" name="judul" class="form-control" placeholder="cth. Perbaikan Rambu Lalu Lintas Jl. Sudirman" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tanggal Kegiatan</label>
        <input type="date" name="tanggal_kegiatan" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>

      <div class="col-md-8">
        <label class="form-label">Kaitkan dengan Pengaduan (opsional)</label>
        <select name="id_pengaduan" class="form-select">
          <option value="">Tidak terkait pengaduan tertentu</option>
          <?php while ($p = mysqli_fetch_assoc($daftar_pengaduan)): ?>
            <option value="<?= $p['id_pengaduan'] ?>"><?= htmlspecialchars($p['ticket']) ?> &mdash; <?= htmlspecialchars($p['lokasi']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Foto Kegiatan</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
      </div>

      <div class="col-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Ceritakan singkat apa yang sudah dikerjakan..." required></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-success">Simpan Dokumentasi</button>
      </div>
    </form>
  </div>

  <h6 class="mb-3 font-display">Dokumentasi yang Sudah Ditambahkan</h6>
  <?php if (mysqli_num_rows($daftar_dokumentasi) == 0): ?>
    <div class="alert alert-secondary">Belum ada dokumentasi kegiatan.</div>
  <?php else: ?>
    <div class="card shadow-sm" style="overflow:hidden;">
      <div class="table-scroll-wrap">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-dark">
            <tr><th>Foto</th><th>Judul</th><th>Tiket Terkait</th><th>Tanggal</th><th></th></tr>
          </thead>
          <tbody>
            <?php while ($d = mysqli_fetch_assoc($daftar_dokumentasi)): ?>
              <tr>
                <td style="width:80px;">
                  <?php if ($d['foto']): ?>
                    <img src="../<?= htmlspecialchars($d['foto']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:6px;">
                  <?php else: ?>
                    <span class="text-muted small">Tanpa foto</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($d['judul']) ?></td>
                <td><?= $d['ticket'] ? '<code>' . htmlspecialchars($d['ticket']) . '</code>' : '<span class="text-muted small">&mdash;</span>' ?></td>
                <td><?= date('d M Y', strtotime($d['tanggal_kegiatan'])) ?></td>
                <td>
                  <a href="dokumentasi.php?hapus=<?= $d['id_dokumentasi'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus dokumentasi ini?')">Hapus</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
