<?php
session_start();
$judul_halaman = "Kontak";
$halaman_aktif = "kontak";
$base_path = "";
include 'includes/header.php';
?>

<div class="container py-5" style="max-width: 720px;">
  <h2 class="mb-4">Kontak Layanan Pengaduan</h2>

  <div class="card p-4 shadow-sm mb-4">
    <p class="text-muted mb-4">
      Untuk pertanyaan seputar SIPADU HUB atau kendala teknis dalam mengajukan
      pengaduan, silakan hubungi Bidang Lalu Lintas Dinas Perhubungan
      Kabupaten Indragiri Hulu melalui kontak berikut.
    </p>

    <ul class="list-unstyled mb-0">
      <!-- GANTI: isi alamat kantor Bidang Lalu Lintas / Dishub Inhu yang sebenarnya -->
      <li class="mb-3">
        <strong>Alamat</strong><br>
        <span class="text-muted">Kantor Dinas Perhubungan Kabupaten Indragiri Hulu, [GANTI: alamat lengkap]</span>
      </li>
      <!-- GANTI: isi nomor telepon/WA layanan pengaduan -->
      <li class="mb-3">
        <strong>Telepon / WhatsApp</strong><br>
        <span class="text-muted">[GANTI: nomor telepon/WA]</span>
      </li>
      <!-- GANTI: isi email resmi layanan pengaduan -->
      <li class="mb-3">
        <strong>Email</strong><br>
        <span class="text-muted">[GANTI: email layanan]</span>
      </li>
      <li class="mb-0">
        <strong>Jam Layanan</strong><br>
        <span class="text-muted">Senin&ndash;Jumat, 08.00&ndash;16.00 WIB (di luar jam tersebut, pengaduan tetap bisa diajukan online kapan saja)</span>
      </li>
    </ul>
  </div>

  <div class="alert alert-info mb-0" style="border-left: 4px solid var(--dishub-ungu);">
    💡 Untuk pengaduan resmi, gunakan halaman <a href="pengaduan.php">Ajukan Pengaduan</a> agar tercatat dan bisa dilacak statusnya.
  </div>
</div>

<?php include 'includes/footer.php'; ?>
