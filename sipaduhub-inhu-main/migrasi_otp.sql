-- ============================================================
-- Migrasi: Verifikasi OTP Email + Perbaikan Bug Kolom `kontak`
-- ============================================================
-- Jalankan file ini SEKALI di database db_sipadu_dishub yang aktif.
-- Cara jalankan (dari terminal Codespace):
--   sudo mysql -u root -proot123 db_sipadu_dishub < migrasi_otp.sql
--
-- Aman dijalankan di database yang sudah ada isinya (data lama
-- otomatis dianggap sudah terverifikasi, tidak akan hilang dari
-- dashboard admin).
-- ============================================================

-- 1. Hapus kolom `kontak` yang sudah tidak dipakai simpan.php,
--    tapi masih NOT NULL tanpa default di database.
--    Ini penyebab error "Field 'kontak' doesn't have a default value"
--    kalau MySQL jalan dengan sql_mode strict.
ALTER TABLE pengaduan DROP COLUMN kontak;

-- 2. Tambah kolom untuk mekanisme verifikasi OTP email
ALTER TABLE pengaduan
  ADD COLUMN email_terverifikasi TINYINT(1) NOT NULL DEFAULT 0 AFTER email,
  ADD COLUMN otp_code VARCHAR(6) DEFAULT NULL AFTER email_terverifikasi,
  ADD COLUMN otp_expired DATETIME DEFAULT NULL AFTER otp_code;

-- 3. Data pengaduan yang SUDAH ADA sebelum fitur ini dianggap valid,
--    supaya tidak tiba-tiba hilang dari dashboard admin setelah
--    query admin ditambah filter email_terverifikasi = 1.
UPDATE pengaduan SET email_terverifikasi = 1 WHERE email_terverifikasi = 0;
