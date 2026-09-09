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

$nama_file = 'laporan-pengaduan-' . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');

$output = fopen('php://output', 'w');
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM biar Excel baca huruf spesial dengan benar

fputcsv($output, ['No Tiket','Tanggal','Nama Pelapor','No HP','Email','Kategori','Lokasi','Deskripsi','Status']);

while ($row = mysqli_fetch_assoc($data)) {
    fputcsv($output, [
        $row['ticket'],
        date('d-m-Y H:i', strtotime($row['created_at'])),
        $row['nama_pelapor'],
        $row['telepon'],
        $row['email'],
        $row['nama_kategori'],
        $row['lokasi'],
        $row['deskripsi'],
        $label_status[$row['status']]
    ]);
}
fclose($output);
exit;
?>
