<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($judul_halaman) ? $judul_halaman . ' - ' : '' ?>SIPADU HUB</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
  <link href="<?= $base_path ?? '' ?>public/css/style.css" rel="stylesheet">
</head>
<body>

<?php
// Navbar publik disembunyikan otomatis di halaman admin/petugas
// (halaman admin selalu set $base_path = "../", dan sudah punya navbar sendiri).
$tampilkan_navbar_publik = ($base_path ?? '') === '';
$halaman_aktif = $halaman_aktif ?? '';
$sudah_login_pelapor = isset($_SESSION['id_pelapor']);
?>

<nav class="navbar navbar-expand-lg navbar-dark site-header">
  <div class="container flex-wrap py-2">
    <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
      <span class="site-logo">
        <img src="<?= $base_path ?? '' ?>public/img/logo-dishub.png" alt="Logo Kementerian Perhubungan">
      </span>
      <span>
        <span class="d-block site-brand-instansi">DINAS PERHUBUNGAN KABUPATEN INDRAGIRI HULU</span>
        <span class="d-block site-brand-sub">SIPADU HUB &mdash; Sistem Informasi Pengaduan Masyarakat</span>
      </span>
    </a>

    <?php if ($tampilkan_navbar_publik): ?>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublik">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navPublik">
        <?php $grup_layanan_aktif = in_array($halaman_aktif, ['pengaduan', 'lacak', 'selesai']); ?>
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'beranda' ? 'active' : '' ?>" href="index.php"><i class="bi bi-house-door"></i> Beranda</a></li>
          <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'profil' ? 'active' : '' ?>" href="profil.php"><i class="bi bi-info-circle"></i> Profil</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= $grup_layanan_aktif ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-signpost-split"></i> Layanan Pengaduan</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item <?= $halaman_aktif === 'pengaduan' ? 'active' : '' ?>" href="pengaduan.php"><i class="bi bi-pencil-square"></i> Ajukan Pengaduan</a></li>
              <li><a class="dropdown-item <?= $halaman_aktif === 'lacak' ? 'active' : '' ?>" href="lacak.php"><i class="bi bi-search"></i> Lacak Pengaduan</a></li>
              <li><a class="dropdown-item <?= $halaman_aktif === 'selesai' ? 'active' : '' ?>" href="pengaduan_selesai.php"><i class="bi bi-check2-circle"></i> Pengaduan Selesai</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link <?= $halaman_aktif === 'kontak' ? 'active' : '' ?>" href="kontak.php"><i class="bi bi-telephone"></i> Kontak</a></li>
          <li class="nav-item ms-lg-2">
            <?php if ($sudah_login_pelapor): ?>
              <div class="dropdown">
                <a class="nav-link dropdown-toggle akun-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['pelapor_nama']) ?></a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="riwayat_saya.php"><i class="bi bi-clock-history"></i> Riwayat Saya</a></li>
                  <li><a class="dropdown-item" href="logout_pelapor.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                </ul>
              </div>
            <?php else: ?>
              <a class="btn btn-sm btn-tombol-login" href="login_google.php"><i class="bi bi-google"></i> Login dengan Google</a>
            <?php endif; ?>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>
