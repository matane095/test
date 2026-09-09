# Setup Fitur Verifikasi OTP Email

## 1. Daftar Brevo (kalau belum)
1. Buat akun di https://www.brevo.com (gratis, limit 300 email/hari)
2. Login → menu **SMTP & API** → tab **SMTP**
3. Catat: SMTP login (bukan email Anda, formatnya beda) dan SMTP key
4. Menu **Senders & IP** → **Senders** → verifikasi minimal satu alamat email pengirim (misal email Gmail dinas sementara). Alamat ini yang dipakai di `SMTP_FROM_EMAIL`.

## 2. Install PHPMailer
Jalankan di root project, di dalam Codespace:
```bash
composer require phpmailer/phpmailer
```
Kalau `composer` belum ada:
```bash
sudo apt install -y composer
```

## 3. Konfigurasi email
```bash
cp config/email_config.example.php config/email_config.php
```
Lalu edit `config/email_config.php`, isi `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_FROM_EMAIL` sesuai punya Anda di Brevo.

## 4. Jalankan migrasi database
```bash
sudo mysql -u root -proot123 db_sipadu_dishub < migrasi_otp.sql
```
File ini menghapus kolom `kontak` yang sudah tidak dipakai (bug lama) dan menambah 3 kolom baru untuk OTP. Data pengaduan lama otomatis dianggap sudah terverifikasi, tidak akan hilang dari dashboard.

## 5. Ganti file
Timpa file-file berikut dengan versi baru yang saya berikan:
- `simpan.php`
- `index.php`
- `verifikasi_otp.php` (file baru)
- `includes/kirim_email.php` (file baru)
- `admin/dashboard.php`
- `admin/cek_pengaduan_baru.php`
- `admin/statistik.php`
- `admin/export_excel.php`
- `admin/export_pdf.php`
- `.gitignore`

## 6. Testing
1. Isi form pengaduan sebagai tamu dengan email asli Anda
2. Harus diarahkan ke halaman `verifikasi_otp.php`, cek inbox (dan folder spam) untuk kode 6 digit
3. Masukkan kode salah 5x → harus muncul pesan "terlalu banyak percobaan"
4. Klik "Kirim ulang kode" → cek email baru masuk, kode lama tidak berlaku lagi
5. Masukkan kode benar → harus redirect ke halaman sukses dan pengaduan baru muncul di dashboard admin
6. Coba juga jalur login Google → pengaduan harus langsung masuk tanpa diminta OTP

## Catatan
- Kalau nanti pindah dari Codespace ke hosting permanen, jangan lupa jalankan lagi `composer install` di server baru (folder `vendor/` sengaja di-gitignore, tidak ikut ter-clone).
- Kalau domain `.go.id` sudah aktif dari Kominfo, cukup ganti `SMTP_FROM_EMAIL` di `config/email_config.php`, tidak perlu ubah kode apa pun.
