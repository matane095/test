<?php
session_start();
$judul_halaman = "Kontak";
$halaman_aktif = "kontak";
$base_path = "";
include 'includes/header.php';
?>

<div class="section-heading">
  <div class="container">
    <h1><i class="bi bi-telephone"></i> Kontak</h1>
    <p>Hubungi layanan pengaduan Bidang Lalu Lintas Dinas Perhubungan Kabupaten Indragiri Hulu.</p>
  </div>
</div>

<div class="container py-5" style="max-width: 720px;">
  <div class="kartu-formulir mb-4">
    <p class="text-muted mb-4">
      Untuk pertanyaan seputar SIPADU HUB atau kendala teknis dalam mengajukan
      pengaduan, silakan hubungi Bidang Lalu Lintas Dinas Perhubungan
      Kabupaten Indragiri Hulu melalui kontak berikut.
    </p>

    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
      <!-- GANTI: isi alamat kantor Bidang Lalu Lintas / Dishub Inhu yang sebenarnya -->
      <li class="d-flex gap-3 align-items-start">
        <span class="ikon-kontak"><i class="bi bi-geo-alt"></i></span>
        <span><strong class="d-block">Alamat</strong><span class="text-muted">Kantor Dinas Perhubungan Kabupaten Indragiri Hulu, [GANTI: alamat lengkap]</span></span>
      </li>
      <!-- GANTI: isi nomor telepon/WA layanan pengaduan -->
      <li class="d-flex gap-3 align-items-start">
        <span class="ikon-kontak"><i class="bi bi-whatsapp"></i></span>
        <span><strong class="d-block">Telepon / WhatsApp</strong><span class="text-muted">[GANTI: nomor telepon/WA]</span></span>
      </li>
      <!-- GANTI: isi email resmi layanan pengaduan -->
      <li class="d-flex gap-3 align-items-start">
        <span class="ikon-kontak"><i class="bi bi-envelope"></i></span>
        <span><strong class="d-block">Email</strong><span class="text-muted">[GANTI: email layanan]</span></span>
      </li>
      <li class="d-flex gap-3 align-items-start">
        <span class="ikon-kontak"><i class="bi bi-clock"></i></span>
        <span><strong class="d-block">Jam Layanan</strong><span class="text-muted">Senin&ndash;Jumat, 08.00&ndash;16.00 WIB (di luar jam tersebut, pengaduan tetap bisa diajukan online kapan saja)</span></span>
      </li>
    </ul>
  </div>

  <div class="kotak-info mb-0">
    <i class="bi bi-lightbulb"></i> Untuk pengaduan resmi, gunakan halaman <a href="pengaduan.php">Ajukan Pengaduan</a> agar tercatat dan bisa dilacak statusnya.
  </div>
</div>

<?php include 'includes/footer.php'; ?>
