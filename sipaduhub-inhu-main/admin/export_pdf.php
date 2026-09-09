<?php
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include '../config/koneksi.php';

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT p.*, k.nama_kategori FROM pengaduan p LEFT JOIN kategori k ON p.id_kategori=k.id_kategori WHERE p.email_terverifikasi = 1";
$types = ""; $params = [];
if ($filter_status != '') { $sql .= " AND p.status=?"; $types .= "s"; $params[] = $filter_status; }
if ($filter_kategori > 0) { $sql .= " AND p.id_kategori=?"; $types .= "i"; $params[] = $filter_kategori; }
if ($search != '') { $sql .= " AND (p.ticket LIKE ? OR p.nama_pelapor LIKE ?)"; $types .= "ss"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.created_at DESC";

$stmt = mysqli_prepare($koneksi, $sql);
if ($types != '') mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);

$label_status = ['diterima'=>'Diterima','diproses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'];
$total = mysqli_num_rows($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Pengaduan - SIPADU HUB</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; color:#1E2226; padding: 24px; }
  .kop { text-align:center; border-bottom: 3px solid #301D6E; padding-bottom:12px; margin-bottom:20px; }
  .kop h1 { font-size:16px; margin:0; color:#301D6E; }
  .kop p { margin:2px 0; font-size:11px; }
  table { width:100%; border-collapse: collapse; margin-top:10px; }
  th, td { border:1px solid #ccc; padding:6px 8px; text-align:left; font-size:11px; }
  th { background:#301D6E; color:#fff; }
  .meta { display:flex; justify-content:space-between; margin-bottom:10px; font-size:11px; }
  .btn-print {
    background:#301D6E; color:#fff; border:none; padding:10px 18px; border-radius:6px;
    font-size:13px; cursor:pointer; margin-bottom:16px;
  }
  @media print {
    .btn-print { display:none; }
    body { padding:0; }
  }
</style>
</head>
<body>

<button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>

<div class="kop">
  <h1>DINAS PERHUBUNGAN KABUPATEN INDRAGIRI HULU</h1>
  <p>Laporan Data Pengaduan Masyarakat &mdash; SIPADU HUB</p>
</div>

<div class="meta">
  <span>Tanggal cetak: <?= date('d M Y, H:i') ?></span>
  <span>Total data: <?= $total ?> pengaduan</span>
</div>

<table>
  <thead>
    <tr><th>No</th><th>Tiket</th><th>Tanggal</th><th>Pelapor</th><th>No HP</th><th>Kategori</th><th>Lokasi</th><th>Status</th></tr>
  </thead>
  <tbody>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['ticket']) ?></td>
        <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
        <td><?= htmlspecialchars($row['nama_pelapor']) ?></td>
        <td><?= htmlspecialchars($row['telepon']) ?></td>
        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
        <td><?= htmlspecialchars($row['lokasi']) ?></td>
        <td><?= $label_status[$row['status']] ?></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

</body>
</html>
