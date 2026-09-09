<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}
include '../config/koneksi.php';

$sejak_id = isset($_GET['sejak_id']) ? (int) $_GET['sejak_id'] : 0;

$stmt = mysqli_prepare($koneksi,
    "SELECT id_pengaduan, ticket, nama_pelapor, lokasi FROM pengaduan WHERE id_pengaduan > ? AND email_terverifikasi = 1 ORDER BY id_pengaduan ASC");
mysqli_stmt_bind_param($stmt, "i", $sejak_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
$max_id = $sejak_id;
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    if ($row['id_pengaduan'] > $max_id) $max_id = $row['id_pengaduan'];
}

header('Content-Type: application/json');
echo json_encode(['baru' => $data, 'max_id' => $max_id]);
?>
