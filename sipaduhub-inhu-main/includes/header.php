<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($judul_halaman) ? $judul_halaman . ' - ' : '' ?>SIPADU HUB</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
  <link href="<?= $base_path ?? '' ?>public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="kop-instansi">
  <div class="container d-flex align-items-center gap-3">
    <div class="logo-box">
      <img src="<?= $base_path ?? '' ?>public/img/logo-dishub.png" alt="Logo Kementerian Perhubungan">
    </div>
    <div>
      <p class="instansi-nama">DINAS PERHUBUNGAN<br>KABUPATEN INDRAGIRI HULU</p>
      <p class="instansi-sub">SIPADU HUB &mdash; Sistem Informasi Pengaduan Masyarakat</p>
    </div>
  </div>
</div>

<?php
// Navbar publik disembunyikan otomatis di halaman admin/petugas
// (halaman admin selalu set $base_path = "../", dan sudah punya navbar sendiri).
$tampilkan_navbar_publik = ($base_path ?? '') === '';
$halaman_aktif = $halaman_aktif ?? '';
$sudah_login_pelapor = isset($_SESSION['id_pelapor']);
?>

<?php if ($tampilkan_navbar_publik): ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-publik">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="index.php">SIPADU HUB</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublik">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navPublik">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'beranda' ? 'active' : '' ?>" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'profil' ? 'active' : '' ?>" href="profil.php">Profil</a></li>
        <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'pengaduan' ? 'active' : '' ?>" href="pengaduan.php">Ajukan Pengaduan</a></li>
        <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'lacak' ? 'active' : '' ?>" href="lacak.php">Lacak Pengaduan</a></li>
        <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'selesai' ? 'active' : '' ?>" href="pengaduan_selesai.php">Pengaduan Selesai</a></li>
        <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'kontak' ? 'active' : '' ?>" href="kontak.php">Kontak</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if ($sudah_login_pelapor): ?>
          <li class="nav-item"><a class="nav-link" href="riwayat_saya.php">Riwayat Saya</a></li>
          <li class="nav-item"><a class="nav-link" href="logout_pelapor.php">Keluar</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login_google.php">Login dengan Google</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php endif; ?>
