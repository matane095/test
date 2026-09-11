<?php
session_start();
include 'config/koneksi.php';
include 'includes/kirim_email.php';

$ticket = isset($_GET['ticket']) ? trim($_GET['ticket']) : '';
$pesan_error = '';
$pesan_sukses = '';

// Batasi percobaan kode salah per tiket (simpan penghitung di session)
$kunci_percobaan = 'otp_gagal_' . $ticket;
if (!isset($_SESSION[$kunci_percobaan])) $_SESSION[$kunci_percobaan] = 0;

function ambilPengaduan($koneksi, $ticket)
{
    $stmt = mysqli_prepare($koneksi,
        "SELECT id_pengaduan, nama_pelapor, email, email_terverifikasi, otp_code, otp_expired
         FROM pengaduan WHERE ticket = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $ticket);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = ambilPengaduan($koneksi, $ticket);

if (!$data) {
    $pesan_error = "Nomor tiket tidak ditemukan.";
} elseif ($data['email_terverifikasi'] == 1) {
    $pesan_sukses = "Pengaduan ini sudah terverifikasi sebelumnya.";
}

// --- Proses kirim ulang OTP ---
if ($data && $data['email_terverifikasi'] == 0 && isset($_POST['aksi']) && $_POST['aksi'] === 'kirim_ulang') {
    $kunci_cooldown = 'otp_resend_at_' . $ticket;
    $boleh_kirim = !isset($_SESSION[$kunci_cooldown]) || (time() - $_SESSION[$kunci_cooldown]) >= 60;

    if (!$boleh_kirim) {
        $pesan_error = "Tunggu sebentar sebelum minta kirim ulang lagi.";
    } else {
        $otp_baru = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expired_baru = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $stmt = mysqli_prepare($koneksi,
            "UPDATE pengaduan SET otp_code = ?, otp_expired = ? WHERE id_pengaduan = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $otp_baru, $expired_baru, $data['id_pengaduan']);
        mysqli_stmt_execute($stmt);

        kirimOtpEmail($data['email'], $data['nama_pelapor'], $otp_baru, $ticket);

        $_SESSION[$kunci_cooldown] = time();
        $_SESSION[$kunci_percobaan] = 0;
        $pesan_sukses = "Kode baru sudah dikirim ke email kamu.";
        $data = ambilPengaduan($koneksi, $ticket); // refresh data
    }
}

// --- Proses submit kode OTP ---
if ($data && $data['email_terverifikasi'] == 0 && isset($_POST['aksi']) && $_POST['aksi'] === 'verifikasi') {

    if ($_SESSION[$kunci_percobaan] >= 5) {
        $pesan_error = "Terlalu banyak percobaan salah. Silakan minta kirim ulang kode.";
    } else {
        $kode_input = trim($_POST['kode_otp'] ?? '');
        $kode_benar = $kode_input !== '' && $kode_input === $data['otp_code'];
        $belum_kadaluarsa = $data['otp_expired'] && strtotime($data['otp_expired']) >= time();

        if ($kode_benar && $belum_kadaluarsa) {
            $stmt = mysqli_prepare($koneksi,
                "UPDATE pengaduan SET email_terverifikasi = 1, otp_code = NULL, otp_expired = NULL WHERE id_pengaduan = ?");
            mysqli_stmt_bind_param($stmt, "i", $data['id_pengaduan']);
            mysqli_stmt_execute($stmt);

            $stmt2 = mysqli_prepare($koneksi,
                "INSERT INTO riwayat_status (id_pengaduan, status, catatan) VALUES (?, 'diterima', 'Pengaduan diterima sistem')");
            mysqli_stmt_bind_param($stmt2, "i", $data['id_pengaduan']);
            mysqli_stmt_execute($stmt2);

            unset($_SESSION[$kunci_percobaan]);
            header("Location: pengaduan.php?tiket=" . urlencode($ticket));
            exit;
        } else {
            $_SESSION[$kunci_percobaan]++;
            $pesan_error = !$belum_kadaluarsa
                ? "Kode sudah kadaluarsa. Minta kirim ulang."
                : "Kode salah. Percobaan tersisa: " . (5 - $_SESSION[$kunci_percobaan]);
        }
    }
}

$judul_halaman = "Verifikasi Pengaduan";
$base_path = "";
include 'includes/header.php';
?>

<div class="container py-5" style="max-width: 480px;">
  <div class="text-center mb-4">
    <i class="bi bi-envelope-paper" style="font-size:30px; color: var(--color-brand);"></i>
    <h4 class="mt-2 mb-1">Verifikasi Email Pengaduan</h4>
    <p class="text-muted mb-0">Masukkan kode 6 digit yang dikirim ke email kamu untuk tiket <strong><?= htmlspecialchars($ticket) ?></strong>.</p>
  </div>

  <?php if ($pesan_error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($pesan_error) ?></div>
  <?php endif; ?>
  <?php if ($pesan_sukses): ?>
    <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
  <?php endif; ?>

  <?php if ($data && $data['email_terverifikasi'] == 0): ?>
    <form method="POST" class="kartu-formulir">
      <input type="hidden" name="aksi" value="verifikasi">
      <div class="mb-3">
        <label class="form-label">Kode OTP</label>
        <input type="text" name="kode_otp" maxlength="6" pattern="[0-9]{6}" class="form-control text-center" style="letter-spacing:6px; font-size:24px;" autofocus>
      </div>
      <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
    </form>

    <form method="POST" class="mt-3 text-center">
      <input type="hidden" name="aksi" value="kirim_ulang">
      <button type="submit" class="btn btn-link">Kirim ulang kode</button>
    </form>
  <?php endif; ?>

  <a href="index.php" class="d-block mt-3 text-center">&larr; Kembali ke beranda</a>
</div>

<?php include 'includes/footer.php'; ?>
