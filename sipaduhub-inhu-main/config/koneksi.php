<?php
$host = "127.0.0.1";
$user = "root";
$pass = "root123";
$dbname = "db_sipadu_dishub";

$koneksi = mysqli_connect($host, $user, $pass, $dbname);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>