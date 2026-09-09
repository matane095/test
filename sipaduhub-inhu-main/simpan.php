<?php
session_start();
include 'config/koneksi.php';
include 'includes/kirim_email.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['alamat_situs'])) {
        header("Location: pengaduan.php?error=honeypot");
        exit;
    }

    $jawaban_user = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
    $jawaban_benar = isset($_SESSION['captcha_answer']) ? $_SESSION['captcha_answer'] : null;
    unset($_SESSION['captcha_answer']);

    if ($jawaban_benar === null || (int)$jawaban_user !== (int)$jawaban_benar) {
        header("Location: pengaduan.php?error=captcha");
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $batas_waktu = date('Y-m-d H:i:s', strtotime('-10 minutes'));

    $stmt_cek = mysqli_prepare($koneksi,
        "SELECT COUNT(*) AS jumlah FROM pengaduan WHERE ip_pelapor = ? AND created_at > ?");
    mysqli_stmt_bind_param($stmt_cek, "ss", $ip, $batas_waktu);
    mysqli_stmt_execute($stmt_cek);
    $hasil_cek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));

    if ($hasil_cek['jumlah'] >= 50) { // TODO: pastikan angka ini sudah wajar sebelum production
        header("Location: pengaduan.php?error=limit");
        exit;
    }

    $nama = trim($_POST['nama']);
    $telepon = trim($_POST['telepon']);
    $email = trim($_POST['email']);
    $id_kategori = (int) $_POST['id_kategori'];
    $lokasi = trim($_POST['lokasi']);
    $deskripsi = trim($_POST['deskripsi']);

    $error = false;
    if (strlen($nama) < 3) $error = true;
    if (!preg_match('/^(\+62|62|0)8[1-9][0-9]{7,11}$/', $telepon)) $error = true;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = true; // email sekarang WAJIB
    if ($id_kategori <= 0) $error = true;
    if (strlen($lokasi) < 3) $error = true;
    if (strlen($deskripsi) < 5) $error = true;

    if ($error) {
        $_SESSION['form_old'] = $_POST;
        header("Location: pengaduan.php?error=validasi");
        exit;
    }

    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $tipe = mime_content_type($_FILES['foto']['tmp_name']);
        $ukuran_max = 5 * 1024 * 1024;

        if (!in_array($tipe, $allowed) || $_FILES['foto']['size'] > $ukuran_max) {
            $_SESSION['form_old'] = $_POST;
            header("Location: pengaduan.php?error=foto");
            exit;
        }

        $ekstensi = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = 'pengaduan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
        $tujuan = __DIR__ . '/uploads/pengaduan/' . $nama_file;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
            $foto_path = 'uploads/pengaduan/' . $nama_file;
        }
    }

    $id_pelapor = isset($_SESSION['id_pelapor']) ? $_SESSION['id_pelapor'] : null;
    $latitude = !empty($_POST['latitude']) ? (float) $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float) $_POST['longitude'] : null;

    $tanggal = date('ymd');
    $random = strtoupper(substr(md5(uniqid()), 0, 4));
    $ticket = "HUB-$tanggal-$random";

    // Kalau pelapor login via Google, email sudah diverifikasi Google
    // sebelumnya, jadi tidak perlu OTP lagi.
    $sudah_login_google = isset($_SESSION['id_pelapor']);
    $email_terverifikasi = $sudah_login_google ? 1 : 0;
    $otp_code = null;
    $otp_expired = null;

    if (!$sudah_login_google) {
        $otp_code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_expired = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    }

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO pengaduan (ticket, nama_pelapor, telepon, email, email_terverifikasi, otp_code, otp_expired, id_kategori, lokasi, deskripsi, status, id_pelapor, ip_pelapor, foto, latitude, longitude)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'diterima', ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssissississdd",
        $ticket, $nama, $telepon, $email, $email_terverifikasi, $otp_code, $otp_expired,
        $id_kategori, $lokasi, $deskripsi, $id_pelapor, $ip, $foto_path, $latitude, $longitude);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
        exit;
    }

    $id_pengaduan = mysqli_insert_id($koneksi);

    if ($sudah_login_google) {
        // Sudah terverifikasi, langsung catat riwayat dan tampilkan sukses seperti biasa
        $stmt2 = mysqli_prepare($koneksi,
            "INSERT INTO riwayat_status (id_pengaduan, status, catatan) VALUES (?, 'diterima', 'Pengaduan diterima sistem')");
        mysqli_stmt_bind_param($stmt2, "i", $id_pengaduan);
        mysqli_stmt_execute($stmt2);

        header("Location: pengaduan.php?tiket=" . $ticket);
        exit;
    }

    // Tamu: kirim OTP dulu, belum masuk riwayat_status sampai terverifikasi
    $terkirim = kirimOtpEmail($email, $nama, $otp_code, $ticket);

    if (!$terkirim) {
        // Gagal kirim email, batalkan supaya pelapor tidak stuck dengan
        // pengaduan yang tidak akan pernah bisa diverifikasi
        $stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM pengaduan WHERE id_pengaduan = ?");
        mysqli_stmt_bind_param($stmt_hapus, "i", $id_pengaduan);
        mysqli_stmt_execute($stmt_hapus);

        $_SESSION['form_old'] = $_POST;
        header("Location: pengaduan.php?error=emailgagal");
        exit;
    }

    header("Location: verifikasi_otp.php?ticket=" . $ticket);
    exit;
}
?>
