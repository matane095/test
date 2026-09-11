<?php
session_start();
include 'config/koneksi.php';
$judul_halaman = "Profil";
$halaman_aktif = "profil";
$base_path = "";
include 'includes/header.php';
?>

<div class="section-heading">
  <div class="container">
    <h1><i class="bi bi-info-circle"></i> Profil</h1>
    <p>Mengenal SIPADU HUB dan Bidang Lalu Lintas Dinas Perhubungan Kabupaten Indragiri Hulu.</p>
  </div>
</div>

<div class="container py-5" style="max-width: 820px;">
  <div class="kartu-formulir mb-4">
    <h5 class="mb-3"><i class="bi bi-signpost-split" style="color: var(--color-accent-dark);"></i> Tentang SIPADU HUB</h5>
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

  <div class="kartu-formulir mb-4">
    <h5 class="mb-3"><i class="bi bi-diagram-3" style="color: var(--color-accent-dark);"></i> Tugas Pokok Bidang Lalu Lintas</h5>
    <!-- GANTI: ganti paragraf di bawah sesuai tupoksi resmi Bidang Lalu Lintas Dishub Inhu -->
    <p class="text-muted">
      Bidang Lalu Lintas bertugas merumuskan dan melaksanakan kebijakan teknis di
      bidang manajemen dan rekayasa lalu lintas, meliputi pengaturan, pengawasan,
      dan pengendalian lalu lintas jalan di wilayah Kabupaten Indragiri Hulu, serta
      menindaklanjuti pengaduan masyarakat terkait rambu, marka, kondisi jalan, dan
      angkutan umum.
    </p>
  </div>

  <div class="kartu-formulir">
    <h5 class="mb-3"><i class="bi bi-stars" style="color: var(--color-accent-dark);"></i> Layanan yang Tersedia</h5>
    <ul class="list-unstyled mb-0">
      <li class="mb-2"><i class="bi bi-check2 text-success"></i> Pengaduan masyarakat terkait lalu lintas, secara tamu maupun login Google</li>
      <li class="mb-2"><i class="bi bi-check2 text-success"></i> Pelacakan status pengaduan secara real-time via nomor tiket</li>
      <li class="mb-2"><i class="bi bi-check2 text-success"></i> Verifikasi email untuk menjaga keaslian setiap laporan yang masuk</li>
      <li class="mb-0"><i class="bi bi-check2 text-success"></i> Transparansi publik melalui halaman Pengaduan Selesai</li>
    </ul>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
