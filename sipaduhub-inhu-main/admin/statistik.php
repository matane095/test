<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
include '../config/koneksi.php';
$judul_halaman = "Statistik";
$base_path = "../";
include '../includes/header.php';

$total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pengaduan WHERE email_terverifikasi = 1"))['jumlah'];
$selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pengaduan WHERE status='selesai' AND email_terverifikasi = 1"))['jumlah'];
$diproses = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pengaduan WHERE status='diproses' AND email_terverifikasi = 1"))['jumlah'];
$ditolak = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pengaduan WHERE status='ditolak' AND email_terverifikasi = 1"))['jumlah'];

$kategori_result = mysqli_query($koneksi,
    "SELECT k.nama_kategori, COUNT(p.id_pengaduan) AS jumlah
     FROM kategori k LEFT JOIN pengaduan p ON k.id_kategori = p.id_kategori AND p.email_terverifikasi = 1
     GROUP BY k.id_kategori ORDER BY jumlah DESC");
$label_kategori = [];
$data_kategori = [];
while ($row = mysqli_fetch_assoc($kategori_result)) {
    $label_kategori[] = $row['nama_kategori'];
    $data_kategori[] = (int) $row['jumlah'];
}

// Data per bulan (6 bulan terakhir)
$bulan_result = mysqli_query($koneksi,
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS bulan, COUNT(*) AS jumlah
     FROM pengaduan
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND email_terverifikasi = 1
     GROUP BY bulan ORDER BY bulan ASC");
$label_bulan = [];
$data_bulan = [];
$nama_bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
while ($row = mysqli_fetch_assoc($bulan_result)) {
    $parts = explode('-', $row['bulan']);
    $label_bulan[] = $nama_bulan[$parts[1]] . ' ' . $parts[0];
    $data_bulan[] = (int) $row['jumlah'];
}

$status_result = mysqli_query($koneksi, "SELECT status, COUNT(*) AS jumlah FROM pengaduan WHERE email_terverifikasi = 1 GROUP BY status");
$label_status_chart = ['diterima'=>'Diterima','diproses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'];
$data_status = ['diterima'=>0,'diproses'=>0,'selesai'=>0,'ditolak'=>0];
while ($row = mysqli_fetch_assoc($status_result)) {
    $data_status[$row['status']] = (int) $row['jumlah'];
}
?>

<nav class="navbar navbar-petugas navbar-dark">
  <div class="container flex-wrap gap-2">
    <span class="navbar-brand mb-0"><i class="bi bi-person-badge"></i> Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
    <div class="d-flex flex-wrap gap-2">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
      <a href="dokumentasi.php" class="btn btn-outline-light btn-sm">Dokumentasi Kegiatan</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-4">Statistik Pengaduan</h4>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin">
        <div class="label">Total Pengaduan</div>
        <div class="angka"><?= $total ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin aksen-emas">
        <div class="label">Sedang Diproses</div>
        <div class="angka" style="color: var(--color-status-diproses-text);"><?= $diproses ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin aksen-hijau">
        <div class="label">Selesai</div>
        <div class="angka" style="color: var(--color-status-selesai-text);"><?= $selesai ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin" style="border-top-color: var(--color-status-ditolak-text);">
        <div class="label">Ditolak</div>
        <div class="angka" style="color: var(--color-status-ditolak-text);"><?= $ditolak ?></div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="kartu-formulir">
        <h6 class="mb-3 font-display">Pengaduan per Kategori</h6>
        <canvas id="chartKategori"></canvas>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="kartu-formulir">
        <h6 class="mb-3 font-display">Distribusi Status</h6>
        <canvas id="chartStatus"></canvas>
      </div>
    </div>
    <div class="col-12">
      <div class="kartu-formulir">
        <h6 class="mb-3 font-display">Tren Pengaduan 6 Bulan Terakhir</h6>
        <canvas id="chartTren"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
new Chart(document.getElementById('chartKategori'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($label_kategori) ?>,
    datasets: [{
      label: 'Jumlah Pengaduan',
      data: <?= json_encode($data_kategori) ?>,
      backgroundColor: '#301D6E',
      borderRadius: 4
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});

new Chart(document.getElementById('chartStatus'), {
  type: 'doughnut',
  data: {
    labels: ['Diterima', 'Diproses', 'Selesai', 'Ditolak'],
    datasets: [{
      data: [<?= $data_status['diterima'] ?>, <?= $data_status['diproses'] ?>, <?= $data_status['selesai'] ?>, <?= $data_status['ditolak'] ?>],
      backgroundColor: ['#2461B3', '#FFC412', '#1E8E5A', '#C6303E']
    }]
  },
  options: { responsive: true }
});

new Chart(document.getElementById('chartTren'), {
  type: 'line',
  data: {
    labels: <?= json_encode($label_bulan) ?>,
    datasets: [{
      label: 'Jumlah Pengaduan',
      data: <?= json_encode($data_bulan) ?>,
      borderColor: '#301D6E',
      backgroundColor: 'rgba(48,29,110,0.10)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});
</script>

<?php include '../includes/footer.php'; ?>
