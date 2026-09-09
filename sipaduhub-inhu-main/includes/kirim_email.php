<?php
// ============================================================
// Helper untuk kirim email OTP verifikasi pengaduan.
// Pakai PHPMailer (install via composer, lihat catatan setup).
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Kirim kode OTP ke email pelapor.
 *
 * @param string $tujuan_email
 * @param string $nama_pelapor
 * @param string $kode_otp
 * @param string $ticket
 * @return bool true kalau berhasil terkirim, false kalau gagal
 */
function kirimOtpEmail(string $tujuan_email, string $nama_pelapor, string $kode_otp, string $ticket): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($tujuan_email, $nama_pelapor);

        $mail->isHTML(true);
        $mail->Subject = "Kode Verifikasi Pengaduan Anda - $ticket";
        $mail->Body    = "
            <p>Halo <strong>" . htmlspecialchars($nama_pelapor) . "</strong>,</p>
            <p>Terima kasih sudah melapor melalui SIPADU HUB. Nomor tiket pengaduan Anda:</p>
            <p style='font-size:20px; font-weight:bold;'>$ticket</p>
            <p>Untuk mengaktifkan pengaduan ini, masukkan kode verifikasi berikut di halaman verifikasi:</p>
            <p style='font-size:28px; font-weight:bold; letter-spacing:4px;'>$kode_otp</p>
            <p>Kode berlaku selama 15 menit. Jika Anda tidak merasa mengirim pengaduan ini, abaikan email ini.</p>
            <hr>
            <p style='color:#888; font-size:12px;'>Dinas Perhubungan Kabupaten Indragiri Hulu</p>
        ";
        $mail->AltBody = "Kode verifikasi pengaduan Anda ($ticket): $kode_otp. Berlaku 15 menit.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Gagal kirim email OTP: ' . $mail->ErrorInfo);
        return false;
    }
}
