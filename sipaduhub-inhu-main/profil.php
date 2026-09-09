<?php
session_start();
include 'config/koneksi.php';
$judul_halaman = "Profil";
$halaman_aktif = "profil";
$base_path = "";
include 'includes/header.php';
?>

<div class="container py-5" style="max-width: 820px;">
  <h2 class="mb-4">Profil SIPADU HUB</h2>

  <div class="card p-4 shadow-sm mb-4">
    <h5>Tentang SIPADU HUB</h5>
    <p>
      SIPADU HUB adalah aplikasi pengaduan masyarakat milik Bidang Lalu Lintas,
      Dinas Perhubungan Kabupaten Indragiri Hulu, yang melayani penyampaian
      keluhan masyarakat terkait transportasi dan lalu lintas secara online.
      Aplikasi ini merupakan sub-domain dari situs resmi Dinas Perhubungan
      Kabupaten Indragiri Hulu.
    </p>
    <!-- GANTI: sesuaikan/tambahkan link resmi situs induk instansi -->
    <a href="#" class="btn btn-outline-primary btn-sm">Kunjungi Situs Resmi Dishub Inhu</a>
  </div>

  <div class="card p-4 shadow-sm mb-4">
    <h5>Tugas Pokok Bidang Lalu Lintas</h5>
    <!-- GANTI: ganti paragraf di bawah sesuai tupoksi resmi Bidang Lalu Lintas Dishub Inhu -->
    <p class="text-muted">
      Bidang Lalu Lintas bertugas merumuskan dan melaksanakan kebijakan teknis di
      bidang manajemen dan rekayasa lalu lintas, meliputi pengaturan, pengawasan,
      dan pengendalian lalu lintas jalan di wilayah Kabupaten Indragiri Hulu, serta
      menindaklanjuti pengaduan masyarakat terkait rambu, marka, kondisi jalan, dan
      angkutan umum.
    </p>
  </div>

  <div class="card p-4 shadow-sm">
    <h5>Layanan yang Tersedia</h5>
    <ul class="mb-0">
      <li>Pengaduan masyarakat terkait lalu lintas, secara tamu maupun login Google</li>
      <li>Pelacakan status pengaduan secara real-time via nomor tiket</li>
      <li>Verifikasi email untuk menjaga keaslian setiap laporan yang masuk</li>
      <li>Transparansi publik melalui halaman Pengaduan Selesai</li>
    </ul>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
