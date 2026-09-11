<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
include '../config/koneksi.php';
$judul_halaman = "Dashboard Petugas";
$base_path = "../";
include '../includes/header.php';

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$tampilkan_spam = isset($_GET['tampilkan_spam']) && $_GET['tampilkan_spam'] == '1';

$per_page = 15;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$where = " WHERE 1=1 AND p.email_terverifikasi = 1";
$types = ""; $params = [];

if (!$tampilkan_spam) { $where .= " AND p.disembunyikan = 0"; }
if ($filter_status != '') { $where .= " AND p.status = ?"; $types .= "s"; $params[] = $filter_status; }
if ($filter_kategori > 0) { $where .= " AND p.id_kategori = ?"; $types .= "i"; $params[] = $filter_kategori; }
if ($search != '') {
    $where .= " AND (p.ticket LIKE ? OR p.nama_pelapor LIKE ?)";
    $types .= "ss";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql_count = "SELECT COUNT(*) AS total FROM pengaduan p" . $where;
$stmt_count = mysqli_prepare($koneksi, $sql_count);
if ($types != '') mysqli_stmt_bind_param($stmt_count, $types, ...$params);
mysqli_stmt_execute($stmt_count);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
$total_halaman = max(1, ceil($total_data / $per_page));

$sql = "SELECT p.*, k.nama_kategori FROM pengaduan p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori" . $where . " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$types_data = $types . "ii";
$params_data = array_merge($params, [$per_page, $offset]);

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, $types_data, ...$params_data);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);

$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori");

// Ringkasan cepat (semua data terverifikasi, terlepas dari filter yang sedang aktif)
$ringkas = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
      COUNT(*) AS total,
      SUM(status = 'diterima') AS total_diterima,
      SUM(status = 'diproses') AS total_diproses,
      SUM(status = 'selesai')  AS total_selesai
    FROM pengaduan
    WHERE email_terverifikasi = 1 AND disembunyikan = 0
"));

$label_status = ['diterima'=>'Diterima','diproses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'];
$warna_status = ['diterima'=>'primary','diproses'=>'warning','selesai'=>'success','ditolak'=>'danger'];

function buatLinkHalaman($halaman, $filter_status, $filter_kategori, $search, $tampilkan_spam) {
    return '?' . http_build_query([
        'status' => $filter_status, 'kategori' => $filter_kategori, 'search' => $search,
        'tampilkan_spam' => $tampilkan_spam ? '1' : '0', 'page' => $halaman
    ]);
}
?>

<nav class="navbar navbar-petugas navbar-dark">
  <div class="container flex-wrap gap-2">
    <span class="navbar-brand mb-0"><i class="bi bi-person-badge"></i> Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
    <div class="d-flex flex-wrap gap-2">
      <button id="btn-notif" class="btn btn-outline-light btn-sm">🔔 Aktifkan Notifikasi</button>
      <a href="statistik.php" class="btn btn-outline-light btn-sm">Statistik</a>
      <a href="dokumentasi.php" class="btn btn-outline-light btn-sm">Dokumentasi Kegiatan</a>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="kelola_petugas.php" class="btn btn-outline-light btn-sm">Kelola Petugas</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-4">Dashboard Pengaduan</h4>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin">
        <div class="label">Total Pengaduan</div>
        <div class="angka"><?= (int) ($ringkas['total'] ?? 0) ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin aksen-abu">
        <div class="label">Diterima</div>
        <div class="angka"><?= (int) ($ringkas['total_diterima'] ?? 0) ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin aksen-emas">
        <div class="label">Diproses</div>
        <div class="angka"><?= (int) ($ringkas['total_diproses'] ?? 0) ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kartu-ringkas-admin aksen-hijau">
        <div class="label">Selesai</div>
        <div class="angka"><?= (int) ($ringkas['total_selesai'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <div class="kartu-formulir mb-3 py-3 px-3 px-md-4">
    <form method="GET" class="row g-2 mb-2">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Cari tiket / nama..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <?php foreach ($label_status as $key => $label): ?>
            <option value="<?= $key ?>" <?= $filter_status == $key ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="kategori" class="form-select">
          <option value="">Semua Kategori</option>
          <?php mysqli_data_seek($kategori_list, 0); while ($k = mysqli_fetch_assoc($kategori_list)): ?>
            <option value="<?= $k['id_kategori'] ?>" <?= $filter_kategori == $k['id_kategori'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
      </div>
      <div class="col-12">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" name="tampilkan_spam" value="1" id="cek-spam" <?= $tampilkan_spam ? 'checked' : '' ?> onchange="this.form.submit()">
          <label class="form-check-label small" for="cek-spam">Tampilkan pengaduan yang disembunyikan (spam)</label>
        </div>
      </div>
    </form>
  </div>

  <div class="d-flex gap-2 mb-3">
    <a href="export_excel.php?<?= http_build_query(['status'=>$filter_status,'kategori'=>$filter_kategori,'search'=>$search]) ?>" class="btn btn-outline-success btn-sm">📊 Export Excel</a>
    <a href="export_pdf.php?<?= http_build_query(['status'=>$filter_status,'kategori'=>$filter_kategori,'search'=>$search]) ?>" target="_blank" class="btn btn-outline-danger btn-sm">🖨️ Cetak / PDF</a>
  </div>

  <div class="card shadow-sm" style="overflow:hidden;">
    <div class="table-scroll-wrap">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr><th>Tiket</th><th>Tanggal</th><th>Pelapor</th><th>Kategori</th><th>Lokasi</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($data) == 0): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengaduan yang cocok.</td></tr>
          <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($data)): ?>
              <tr class="<?= $row['disembunyikan'] ? 'table-secondary' : '' ?>">
                <td><code><?= htmlspecialchars($row['ticket']) ?></code></td>
                <td><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                <td><?= htmlspecialchars($row['nama_pelapor']) ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td><?= htmlspecialchars($row['lokasi']) ?></td>
                <td>
                  <span class="badge bg-<?= $warna_status[$row['status']] ?>"><?= $label_status[$row['status']] ?></span>
                  <?php if ($row['disembunyikan']): ?><span class="badge bg-dark">Spam</span><?php endif; ?>
                </td>
                <td><a href="detail.php?id=<?= $row['id_pengaduan'] ?>" class="btn btn-sm btn-outline-primary">Kelola</a></td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($total_halaman > 1): ?>
    <nav class="mt-3">
      <ul class="pagination pagination-sm justify-content-center flex-wrap">
        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= buatLinkHalaman($i, $filter_status, $filter_kategori, $search, $tampilkan_spam) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <div class="text-center text-muted small">Menampilkan halaman <?= $page ?> dari <?= $total_halaman ?> (<?= $total_data ?> total pengaduan)</div>
  <?php endif; ?>
</div>

<?php
// Baseline notifikasi cuma hitung yang sudah terverifikasi, supaya pengaduan
// tamu yang masih menunggu OTP tetap memicu notif begitu berhasil diverifikasi
$max_id_result = mysqli_query($koneksi, "SELECT MAX(id_pengaduan) AS max_id FROM pengaduan WHERE email_terverifikasi = 1");
$max_id_saat_ini = mysqli_fetch_assoc($max_id_result)['max_id'] ?? 0;
?>

<div id="toast-container" style="position:fixed; bottom:20px; right:20px; z-index:1050; display:flex; flex-direction:column; gap:12px; max-width:400px; width:90%;"></div>

<script>
(function() {
  var btnNotif = document.getElementById('btn-notif');

  function updateTombolNotif() {
    if (!('Notification' in window)) {
      btnNotif.textContent = '🔕 Browser tidak mendukung';
      btnNotif.disabled = true;
      return;
    }
    if (Notification.permission === 'granted') {
      btnNotif.textContent = '🔔 Notifikasi Aktif';
    } else {
      btnNotif.textContent = '🔔 Aktifkan Notifikasi';
    }
  }

  btnNotif.addEventListener('click', function() {
    Notification.requestPermission().then(updateTombolNotif);
  });
  updateTombolNotif();

  var judulAsli = document.title;
  var jumlahBaru = 0;

  function mainkanBunyi() {
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var osc = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = 880;
      gain.gain.setValueAtTime(0.15, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
      osc.start();
      osc.stop(ctx.currentTime + 0.4);
    } catch (e) {}
  }

  function tampilkanToast(p) {
    var container = document.getElementById('toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast-pengaduan';
    toast.innerHTML =
      '<div class="toast-judul">📥 Pengaduan Baru</div>' +
      '<div class="toast-isi"><strong>' + p.ticket + '</strong><br>' + p.nama_pelapor + ' — ' + p.lokasi + '</div>';

    toast.addEventListener('click', function() {
      window.location.href = 'detail.php?id=' + p.id_pengaduan;
    });

    container.appendChild(toast);

    setTimeout(function() {
      toast.style.transition = 'opacity 0.4s';
      toast.style.opacity = '0';
      setTimeout(function() { toast.remove(); }, 400);
    }, 8000);
  }

  if (!localStorage.getItem('sipadu_id_terakhir')) {
    localStorage.setItem('sipadu_id_terakhir', <?= $max_id_saat_ini ?>);
  }

  function cekPengaduanBaru() {
    var idTerakhir = localStorage.getItem('sipadu_id_terakhir') || 0;

    fetch('cek_pengaduan_baru.php?sejak_id=' + idTerakhir)
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.baru && data.baru.length > 0) {
          data.baru.forEach(function(p) {
            tampilkanToast(p);
            if (Notification.permission === 'granted') {
              var notif = new Notification('Pengaduan Baru Masuk', {
                body: p.ticket + ' — ' + p.nama_pelapor + ' (' + p.lokasi + ')',
                icon: '../public/img/logo-dishub.png'
              });
              notif.onclick = function() {
                window.focus();
                window.location.href = 'detail.php?id=' + p.id_pengaduan;
              };
            }
          });
          mainkanBunyi();
          jumlahBaru += data.baru.length;
          document.title = '(' + jumlahBaru + ') ' + judulAsli;
          localStorage.setItem('sipadu_id_terakhir', data.max_id);
        }
      })
      .catch(function() {});
  }

  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
      jumlahBaru = 0;
      document.title = judulAsli;
    }
  });

  setInterval(cekPengaduanBaru, 15000);
})();
</script>

<?php include '../includes/footer.php'; ?>
