<?php
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Halaman ini hanya untuk role Admin. <a href='dashboard.php'>Kembali ke Dashboard</a>");
}
include '../config/koneksi.php';

$pesan = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'] === 'admin' ? 'admin' : 'petugas';

    if (strlen($nama) < 3 || strlen($username) < 3 || strlen($password) < 6) {
        $error = "Data tidak valid. Nama & username minimal 3 karakter, password minimal 6 karakter.";
    } else {
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id_user FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_cek)) > 0) {
            $error = "Username sudah dipakai, pilih yang lain.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, password, role, aktif) VALUES (?, ?, ?, ?, 1)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $hash, $role);
            mysqli_stmt_execute($stmt);
            $pesan = "Akun petugas '$nama' berhasil ditambahkan.";
        }
    }
}

if (isset($_GET['toggle'])) {
    $id_toggle = (int) $_GET['toggle'];
    if ($id_toggle != $_SESSION['id_user']) {
        $stmt = mysqli_prepare($koneksi, "UPDATE users SET aktif = 1 - aktif WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_toggle);
        mysqli_stmt_execute($stmt);
    }
    header("Location: kelola_petugas.php");
    exit;
}

$daftar_user = mysqli_query($koneksi, "SELECT * FROM users ORDER BY created_at DESC");

$judul_halaman = "Kelola Petugas";
$base_path = "../";
include '../includes/header.php';
?>

<nav class="navbar navbar-petugas navbar-dark">
  <div class="container">
    <span class="navbar-brand mb-0">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
    <div>
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-4">Kelola Akun Petugas</h4>

  <?php if ($pesan): ?><div class="alert alert-success"><?= $pesan ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <div class="card p-4 shadow-sm mb-4">
    <h6 class="mb-3">Tambah Petugas Baru</h6>
    <form method="POST" class="row g-2">
      <input type="hidden" name="aksi" value="tambah">
      <div class="col-md-3"><input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required></div>
      <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
      <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password (min 6 karakter)" required></div>
      <div class="col-md-2">
        <select name="role" class="form-select">
          <option value="petugas">Petugas</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
    </form>
  </div>

  <div class="card shadow-sm" style="overflow:hidden;">
    <div class="table-scroll-wrap">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr><th>Nama</th><th>Username</th><th>Role</th><th>Status</th><th>Dibuat</th><th></th></tr>
        </thead>
        <tbody>
          <?php while ($u = mysqli_fetch_assoc($daftar_user)): ?>
            <tr>
              <td><?= htmlspecialchars($u['nama']) ?></td>
              <td><?= htmlspecialchars($u['username']) ?></td>
              <td><span class="badge bg-<?= $u['role']=='admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
              <td><span class="badge bg-<?= $u['aktif'] ? 'success' : 'danger' ?>"><?= $u['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
              <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <?php if ($u['id_user'] != $_SESSION['id_user']): ?>
                  <a href="kelola_petugas.php?toggle=<?= $u['id_user'] ?>" class="btn btn-sm btn-outline-<?= $u['aktif'] ? 'danger' : 'success' ?>" onclick="return confirm('Yakin mau <?= $u['aktif'] ? 'nonaktifkan' : 'aktifkan' ?> akun ini?')">
                    <?= $u['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                  </a>
                <?php else: ?>
                  <span class="text-muted small">Akun kamu</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>