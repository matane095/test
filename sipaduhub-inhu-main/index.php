<?php
session_start();
include 'config/koneksi.php';
$judul_halaman = "Beranda";
$halaman_aktif = "beranda";
$base_path = "";
include 'includes/header.php';

// Statistik ringkas bulan berjalan (hanya yang sudah terverifikasi email & tidak spam)
$awal_bulan = date('Y-m-01 00:00:00');
$stat = mysqli_query($koneksi, "
    SELECT
      SUM(status = 'diterima') AS jml_diterima,
      SUM(status = 'diproses') AS jml_diproses,
      SUM(status = 'selesai')  AS jml_selesai,
      COUNT(*) AS jml_total
    FROM pengaduan
    WHERE email_terverifikasi = 1 AND disembunyikan = 0 AND created_at >= '$awal_bulan'
");
$stat = mysqli_fetch_assoc($stat);

$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<div class="hero-beranda">
  <div class="container">
    <span class="hero-kicker"><i class="bi bi-geo-alt"></i> Kabupaten Indragiri Hulu</span>
    <h1><i class="bi bi-signpost-split"></i> SIPADU HUB</h1>
    <p class="lead-copy mb-4">
      Sistem Informasi Pengaduan Masyarakat Bidang Lalu Lintas, Dinas Perhubungan
      Kabupaten Indragiri Hulu. Sampaikan keluhan seputar transportasi dan lalu lintas
      di wilayah Indragiri Hulu secara online, cepat, dan bisa dilacak statusnya.
    </p>
    <div class="hero-aksi d-flex gap-2 flex-wrap">
      <a href="pengaduan.php" class="btn btn-lg btn-hero-primer">Ajukan Pengaduan</a>
      <a href="lacak.php" class="btn btn-lg btn-hero-sekunder">Lacak Status Pengaduan</a>
    </div>
  </div>
</div>
<div class="marka-jalan"></div>

<div class="container py-5">

  <h5 class="mb-3">Ringkasan Bulan Ini</h5>
  <div class="row g-3 mb-5">
    <div class="col-6 col-md-3">
      <div class="kartu-statistik">
        <i class="bi bi-inbox"></i>
        <div class="angka"><?= (int) ($stat['jml_total'] ?? 0) ?></div>
        <div class="text-muted small">Total Pengaduan</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-statistik">
        <i class="bi bi-envelope-check"></i>
        <div class="angka"><?= (int) ($stat['jml_diterima'] ?? 0) ?></div>
        <div class="text-muted small">Diterima</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-statistik aksen-emas">
        <i class="bi bi-hourglass-split"></i>
        <div class="angka"><?= (int) ($stat['jml_diproses'] ?? 0) ?></div>
        <div class="text-muted small">Diproses</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-statistik aksen-hijau">
        <i class="bi bi-check2-circle"></i>
        <div class="angka"><?= (int) ($stat['jml_selesai'] ?? 0) ?></div>
        <div class="text-muted small">Selesai</div>
      </div>
    </div>
  </div>
  <p class="text-muted small mt-n4 mb-5">
    Lihat daftar lengkap pengaduan yang sudah selesai di halaman
    <a href="pengaduan_selesai.php">Pengaduan Selesai</a>.
  </p>

  <h5 class="mb-3">Cara Mengajukan Pengaduan</h5>
  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="kartu-langkah">
        <div class="nomor-langkah">1</div>
        <h6><i class="bi bi-pencil-square text-muted"></i> Isi Form &amp; Verifikasi Email</h6>
        <p class="text-muted small mb-0">Isi form pengaduan beserta lokasi kejadian, lalu masukkan kode OTP yang dikirim ke email Anda (bisa dilewati jika login dengan Google).</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kartu-langkah">
        <div class="nomor-langkah">2</div>
        <h6><i class="bi bi-cone-striped text-muted"></i> Petugas Menindaklanjuti</h6>
        <p class="text-muted small mb-0">Petugas Bidang Lalu Lintas meninjau dan memproses pengaduan sesuai kategori dan prioritas.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kartu-langkah">
        <div class="nomor-langkah">3</div>
        <h6><i class="bi bi-search text-muted"></i> Lacak Status</h6>
        <p class="text-muted small mb-0">Gunakan nomor tiket (atau riwayat otomatis jika login Google) untuk memantau perkembangan pengaduan Anda.</p>
      </div>
    </div>
  </div>

  <h5 class="mb-3">Kategori Pengaduan yang Dilayani</h5>
  <div class="d-flex flex-wrap gap-2 mb-3">
    <?php while ($k = mysqli_fetch_assoc($kategori_list)): ?>
      <span class="badge badge-kategori fw-normal px-3 py-2"><?= htmlspecialchars($k['nama_kategori']) ?></span>
    <?php endwhile; ?>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
