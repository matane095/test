<?php
// ============================================================
// TEMPLATE konfigurasi email untuk kirim OTP verifikasi.
// Copy file ini jadi "email_config.php" di folder yang sama,
// lalu isi dengan kredensial Brevo Anda yang asli.
// File "email_config.php" (tanpa "example") HARUS di-gitignore,
// sama seperti perlakuan google_config.php.
// ============================================================

// Login SMTP Brevo (bukan email pribadi Anda).
// Lihat di dashboard Brevo: SMTP & API > SMTP
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'ISI_SMTP_LOGIN_BREVO_ANDA'); // contoh: 8a1b2c001@smtp-brevo.com
define('SMTP_PASSWORD', 'ISI_SMTP_KEY_BREVO_ANDA');   // "SMTP key", beda dari password akun Brevo

// Alamat & nama pengirim. Alamat ini HARUS sudah diverifikasi
// (sender identity) di dashboard Brevo sebelum bisa dipakai kirim.
define('SMTP_FROM_EMAIL', 'noreply@domain-sementara-anda.com');
define('SMTP_FROM_NAME', 'SIPADU HUB - Dinas Perhubungan Kab. Indragiri Hulu');
