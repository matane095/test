-- ============================================================
-- Migrasi: Dokumentasi Kegiatan (halaman publik "Pengaduan Selesai")
-- ============================================================
-- Jalankan file ini SEKALI di database db_sipadu_dishub yang aktif.
--   sudo mysql -u root -proot123 db_sipadu_dishub < migrasi_dokumentasi.sql
--
-- Tabel ini menyimpan dokumentasi kegiatan yang diisi manual oleh
-- admin/petugas (judul, deskripsi, foto proses pengerjaan), untuk
-- ditampilkan di halaman publik "Pengaduan Selesai" sebagai bukti
-- kegiatan sudah ditindaklanjuti. Beda dari kolom status='selesai'
-- di tabel pengaduan (yang cuma penanda status internal).
-- ============================================================

CREATE TABLE IF NOT EXISTS `dokumentasi_kegiatan` (
  `id_dokumentasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengaduan` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_dokumentasi`),
  KEY `id_pengaduan` (`id_pengaduan`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `dokumentasi_kegiatan`
  ADD CONSTRAINT `dokumentasi_kegiatan_ibfk_1` FOREIGN KEY (`id_pengaduan`) REFERENCES `pengaduan` (`id_pengaduan`) ON DELETE SET NULL,
  ADD CONSTRAINT `dokumentasi_kegiatan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
