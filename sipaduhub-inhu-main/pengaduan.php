<?php
session_start();
include 'config/koneksi.php';
$judul_halaman = "Ajukan Pengaduan";
$halaman_aktif = "pengaduan";
$base_path = "";
include 'includes/header.php';

$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori");
$tiket_baru = isset($_GET['tiket']) ? $_GET['tiket'] : null;
$sudah_login = isset($_SESSION['id_pelapor']);

$angka1 = rand(1, 9);
$angka2 = rand(1, 9);
$_SESSION['captcha_answer'] = $angka1 + $angka2;

$pesan_error = [
    'honeypot' => 'Pengiriman terdeteksi sebagai bot dan ditolak.',
    'captcha'  => 'Jawaban captcha salah, silakan coba lagi.',
    'limit'    => 'Kamu sudah mengirim terlalu banyak pengaduan dalam waktu singkat. Coba lagi beberapa menit lagi.',
    'foto'     => 'Foto tidak valid. Gunakan format JPG/PNG/WebP, maksimal 5MB.',
    'validasi' => 'Ada data yang belum valid, silakan periksa kembali form di bawah.',
    'emailgagal' => 'Gagal mengirim email verifikasi. Periksa kembali alamat email kamu, atau coba beberapa saat lagi.',
];
$error_code = isset($_GET['error']) ? $_GET['error'] : null;

$old = isset($_SESSION['form_old']) ? $_SESSION['form_old'] : [];
$field_errors = isset($_SESSION['field_errors']) ? $_SESSION['field_errors'] : [];
unset($_SESSION['form_old'], $_SESSION['field_errors']);

function isi($old, $key, $default = '') {
    return isset($old[$key]) ? htmlspecialchars($old[$key]) : htmlspecialchars($default);
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <h2 class="mb-1">Form Pengaduan Masyarakat</h2>
      <p class="text-muted mb-0">Sampaikan keluhan terkait transportasi dan lalu lintas di wilayah Kabupaten Indragiri Hulu.</p>
    </div>

    <?php if ($sudah_login): ?>
      <div class="text-end">
        <div class="small">Masuk sebagai <strong><?= htmlspecialchars($_SESSION['pelapor_nama']) ?></strong></div>
        <a href="riwayat_saya.php" class="btn btn-outline-primary btn-sm mt-1">Riwayat Pengaduan Saya</a>
        <a href="logout_pelapor.php" class="btn btn-outline-secondary btn-sm mt-1">Keluar</a>
      </div>
    <?php else: ?>
      <a href="login_google.php" class="btn btn-outline-dark btn-sm mt-1">Login dengan Google</a>
    <?php endif; ?>
  </div>

  <a href="lacak.php" class="btn btn-outline-primary btn-sm mb-4 mt-3">Lacak Pengaduan Saya</a>

  <?php if (!$sudah_login): ?>
    <div class="alert alert-info mt-3" style="border-left: 4px solid var(--dishub-ungu);">
      💡 <strong>Tips:</strong> Setelah mengirim pengaduan, <strong>catat nomor tiket Anda</strong> untuk melacak status nanti. 
      Atau, <a href="login_google.php">login dengan Google</a> supaya semua riwayat pengaduan Anda tersimpan otomatis dan bisa dilihat kapan saja tanpa perlu mengingat nomor tiket.
    </div>
  <?php endif; ?>

  <?php if ($tiket_baru): ?>
    <div class="alert alert-success">
      Pengaduan berhasil dikirim! Simpan nomor tiket kamu: <strong><?= htmlspecialchars($tiket_baru) ?></strong>
    </div>
  <?php endif; ?>

  <?php if ($error_code && isset($pesan_error[$error_code])): ?>
    <div class="alert alert-danger"><?= $pesan_error[$error_code] ?></div>
  <?php endif; ?>

  <form action="simpan.php" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm" id="form-pengaduan" novalidate>
    <div class="mb-3">
      <label class="form-label">Nama Pelapor</label>
      <input type="text" name="nama" id="f-nama" class="form-control" value="<?= $sudah_login ? htmlspecialchars($_SESSION['pelapor_nama']) : isi($old, 'nama') ?>">
      <div class="invalid-feedback" id="err-nama"></div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Nomor HP</label>
        <input type="tel" name="telepon" id="f-telepon" class="form-control" placeholder="08xxxxxxxxxx" value="<?= isi($old, 'telepon') ?>">
        <div class="invalid-feedback" id="err-telepon"></div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" id="f-email" class="form-control" placeholder="nama@email.com" value="<?= $sudah_login ? htmlspecialchars($_SESSION['pelapor_email']) : isi($old, 'email') ?>">
        <div class="form-text">Kode verifikasi akan dikirim ke email ini sebelum pengaduan diproses.</div>
        <div class="invalid-feedback" id="err-email"></div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Kategori</label>
      <select name="id_kategori" id="f-kategori" class="form-select">
        <option value="">Pilih kategori</option>
        <?php while ($row = mysqli_fetch_assoc($kategori_list)): ?>
          <option value="<?= $row['id_kategori'] ?>" <?= (isset($old['id_kategori']) && $old['id_kategori'] == $row['id_kategori']) ? 'selected' : '' ?>><?= htmlspecialchars($row['nama_kategori']) ?></option>
        <?php endwhile; ?>
      </select>
      <div class="invalid-feedback" id="err-kategori"></div>
    </div>

    <div class="mb-3">
      <label class="form-label">Lokasi Kejadian (alamat/keterangan)</label>
      <input type="text" name="lokasi" id="f-lokasi" class="form-control" placeholder="Jl. ... / Kelurahan ..." value="<?= isi($old, 'lokasi') ?>">
      <div class="invalid-feedback" id="err-lokasi"></div>
    </div>

    <div class="mb-3">
      <label class="form-label">Titik Lokasi di Peta (opsional, bantu petugas menemukan lokasi lebih cepat)</label>
      <div class="mb-2">
        <button type="button" id="btn-lokasi-saya" class="btn btn-outline-primary btn-sm">📍 Gunakan Lokasi Saya</button>
        <span class="text-muted small ms-2">atau klik langsung di peta</span>
      </div>
      <div id="peta-lokasi" style="height:280px;border-radius:8px;border:1px solid #DFDCCE;"></div>
      <div id="koordinat-text" class="small text-muted mt-1">Belum ada titik dipilih.</div>
      <input type="hidden" name="latitude" id="latitude">
      <input type="hidden" name="longitude" id="longitude">
    </div>

    <div class="mb-3">
      <label class="form-label">Deskripsi Kejadian</label>
      <textarea name="deskripsi" id="f-deskripsi" class="form-control" rows="4"><?= isi($old, 'deskripsi') ?></textarea>
      <div class="invalid-feedback" id="err-deskripsi"></div>
    </div>

    <div class="mb-3">
      <label class="form-label">Foto Kejadian (opsional, JPG/PNG/WebP maks 5MB)</label>
      <input type="file" name="foto" class="form-control" accept="image/*">
    </div>

    <div style="position:absolute; left:-9999px;" aria-hidden="true">
      <label>Alamat Situs (kosongkan)</label>
      <input type="text" name="alamat_situs" tabindex="-1" autocomplete="off">
    </div>

    <div class="mb-3">
      <label class="form-label">Verifikasi: berapa hasil dari <?= $angka1 ?> + <?= $angka2 ?>?</label>
      <input type="text" name="captcha" id="f-captcha" class="form-control" style="max-width:120px;">
      <div class="invalid-feedback" id="err-captcha"></div>
    </div>

    <button type="submit" class="btn btn-success">Kirim Pengaduan</button>
  </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  var petaAwal = [-0.4031, 102.5661];
  var peta = L.map('peta-lokasi').setView(petaAwal, 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(peta);

  var marker = null;

  function pasangTitik(lat, lng) {
    if (marker) { peta.removeLayer(marker); }
    marker = L.marker([lat, lng]).addTo(peta);
    peta.setView([lat, lng], 16);
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('koordinat-text').innerText =
      'Titik dipilih: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
  }

  peta.on('click', function(e) {
    pasangTitik(e.latlng.lat, e.latlng.lng);
  });

  document.getElementById('btn-lokasi-saya').addEventListener('click', function() {
    if (!navigator.geolocation) {
      alert('Browser tidak mendukung deteksi lokasi otomatis.');
      return;
    }
    document.getElementById('koordinat-text').innerText = 'Mendeteksi lokasi...';
    navigator.geolocation.getCurrentPosition(function(pos) {
      pasangTitik(pos.coords.latitude, pos.coords.longitude);
    }, function() {
      document.getElementById('koordinat-text').innerText = 'Gagal mendeteksi lokasi. Coba klik langsung di peta.';
    });
  });

  function tampilkanError(idInput, idError, pesan) {
    document.getElementById(idInput).classList.add('is-invalid');
    var el = document.getElementById(idError);
    el.textContent = pesan;
    el.style.display = 'block';
  }
  function bersihkanError(idInput, idError) {
    document.getElementById(idInput).classList.remove('is-invalid');
    var el = document.getElementById(idError);
    el.textContent = '';
    el.style.display = 'none';
  }

  document.getElementById('form-pengaduan').addEventListener('submit', function(e) {
    var valid = true;

    var kolom = ['nama','telepon','email','kategori','lokasi','deskripsi','captcha'];
    kolom.forEach(function(k) { bersihkanError('f-' + k, 'err-' + k); });

    var nama = document.getElementById('f-nama').value.trim();
    if (nama.length < 3) {
      tampilkanError('f-nama', 'err-nama', 'Nama minimal 3 karakter.');
      valid = false;
    }

    var telepon = document.getElementById('f-telepon').value.trim();
    var regexTelepon = /^(\+62|62|0)8[1-9][0-9]{7,11}$/;
    if (!regexTelepon.test(telepon)) {
      tampilkanError('f-telepon', 'err-telepon', 'Nomor HP tidak valid. Contoh: 081234567890');
      valid = false;
    }

    var email = document.getElementById('f-email').value.trim();
    var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '' || !regexEmail.test(email)) {
      tampilkanError('f-email', 'err-email', 'Email wajib diisi dengan format yang benar.');
      valid = false;
    }

    var kategori = document.getElementById('f-kategori').value;
    if (kategori === '') {
      tampilkanError('f-kategori', 'err-kategori', 'Pilih salah satu kategori.');
      valid = false;
    }

    var lokasi = document.getElementById('f-lokasi').value.trim();
    if (lokasi.length < 3) {
      tampilkanError('f-lokasi', 'err-lokasi', 'Lokasi wajib diisi.');
      valid = false;
    }

    var deskripsi = document.getElementById('f-deskripsi').value.trim();
    if (deskripsi.length < 5) {
      tampilkanError('f-deskripsi', 'err-deskripsi', 'Deskripsi minimal 5 karakter.');
      valid = false;
    }

    var captcha = document.getElementById('f-captcha').value.trim();
    if (captcha === '') {
      tampilkanError('f-captcha', 'err-captcha', 'Jawaban verifikasi wajib diisi.');
      valid = false;
    }

    if (!valid) {
      e.preventDefault();
      var elPertamaError = document.querySelector('.is-invalid');
      if (elPertamaError) {
        elPertamaError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });
</script>

<?php include 'includes/footer.php'; ?>
