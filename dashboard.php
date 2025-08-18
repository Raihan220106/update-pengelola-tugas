<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

require 'koneksi.php';

$pesan = "";
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']);
}

// Ambil filter jika ada
$id_user = $_SESSION['user_id'];
$filter_mk = $_GET['filter_mk'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// Query tugas dengan filter
$sql = "SELECT t.id_tugas, t.judul, m.Nama_mk, t.deadline, t.Status, t.tanggal_selesai, t.dibuat_pada
        FROM tugas t
        JOIN mata_kuliah m ON t.id_mk = m.id_mk
        WHERE t.id_user = ?";
if ($filter_mk) $sql .= " AND t.id_mk = " . intval($filter_mk);
if ($filter_status) $sql .= " AND t.Status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
$sql .= " ORDER BY t.deadline ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

// Query untuk Chart
$queryChart = "SELECT Status, COUNT(*) as jumlah FROM tugas WHERE id_user = ? GROUP BY Status";
$stmtChart = $conn->prepare($queryChart);
$stmtChart->bind_param("i", $id_user);
$stmtChart->execute();
$resultChart = $stmtChart->get_result();
$status_labels = [];
$status_values = [];
while ($row = $resultChart->fetch_assoc()) {
    $status_labels[] = $row['Status'];
    $status_values[] = $row['jumlah'];
}

// Ambil data untuk Ringkasan Tugas
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN Status = 'belum' THEN 1 ELSE 0 END) as belum,
    SUM(CASE WHEN Status = 'proses' THEN 1 ELSE 0 END) as proses
FROM tugas WHERE id_user = ?";
$stmt_stats = $conn->prepare($stats_query);
$stmt_stats->bind_param("i", $id_user);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

//menghitung rata -rata penyelesaian tugas
$queryAvg = "SELECT AVG(DATEDIFF(tanggal_selesai, deadline)) AS rata_selesai 
             FROM tugas 
             WHERE id_user = ? AND Status = 'selesai' AND tanggal_selesai IS NOT NULL";
$stmtAvg = $conn->prepare($queryAvg);
$stmtAvg->bind_param("i", $id_user);
$stmtAvg->execute();
$resultAvg = $stmtAvg->get_result()->fetch_assoc();
$rata_selesai = is_null($resultAvg['rata_selesai']) ? 0 : round($resultAvg['rata_selesai'], 1);

//untuk menyimpan ke tabel statistik_user
$cek = $conn->prepare("SELECT id_user FROM statistik_user WHERE id_user = ?");
$cek->bind_param("i", $id_user);
$cek->execute();
$cek_result = $cek->get_result();

if ($cek_result->num_rows > 0) {
    // Update data statistik termasuk rata-rata
    $update_stat = $conn->prepare("UPDATE statistik_user 
        SET total_tugas=?, tugas_selesai=?, tugas_proses=?, tugas_belum=?, rata_waktu_selesai=?
        WHERE id_user=?");
    $update_stat->bind_param("iiiiii", $stats['total'], $stats['selesai'], $stats['proses'], $stats['belum'], $rata_selesai, $id_user);
    $update_stat->execute();
} else {
    // Insert data statistik termasuk rata-rata
    $insert_stat = $conn->prepare("INSERT INTO statistik_user 
        (id_user, total_tugas, tugas_selesai, tugas_proses, tugas_belum, rata_waktu_selesai) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stat->bind_param("iiiiii", $id_user, $stats['total'], $stats['selesai'], $stats['proses'], $stats['belum'], $rata_selesai);
    $insert_stat->execute();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Tugas</title>
  <link rel="stylesheet" href="style_dashboard.css">

</head>
<body>
  <nav class="navbar">
    <div class="logo">📚 TugasKu</div>
    <ul class="nav-menu">
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="Tugassaya.php">Tugas Saya</a></li>
      <li><a href="matkul.php">Mata Kuliah</a></li>
      <li><a href="index.html">Logout</a></li>
    </ul>
  </nav>

  <div class="container">
    <aside class="sidebar">
      <center><h1>Semester 2</h1></center>
      <h3>Prodi: Data Science</h3>
      <h3>Tahun Ajaran 2025/2026</h3>
      <form method="get" action="dashboard.php">
        <label>Mata Kuliah:</label>
        <select name="filter_mk">
          <option value="">Semua</option>
          <?php
            $mk_query = mysqli_query($conn, "SELECT id_mk, Nama_mk FROM mata_kuliah");
            while ($mk = mysqli_fetch_assoc($mk_query)) {
              $selected = ($filter_mk == $mk['id_mk']) ? 'selected' : '';
              echo "<option value='{$mk['id_mk']}' $selected>{$mk['Nama_mk']}</option>";
            }
          ?>
        </select>
        <label>Status Tugas:</label>
        <select name="filter_status">
          <option value="">Semua</option>
          <option value="belum" <?= $filter_status == 'belum' ? 'selected' : '' ?>>Belum Selesai</option>
          <option value="proses" <?= $filter_status == 'proses' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
          <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
        </select>
        <br><br>
        <button type="submit">Filter</button>
      </form>
      <!-- Tambahan tampilkan rata-rata -->
      <div class="sidebar-summary">
        <p><strong>📊 Rata-rata Waktu Selesai:</strong> <?= $rata_selesai ?> hari</p>
      </div>

      <canvas id="statusChart" width="200" height="200"></canvas>
    </aside>

    <main class="main-content">
      <div class="main-wrapper">
      <?php if (!empty($pesan)): ?>
        <div class="alert-success"><?= $pesan ?></div>
      <?php endif; ?>

      <h2 id="welcome-message">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
      <a href="tambah-tugas.php" class="btn-tambah">+Tambah Tugas</a>

      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
          $deadline = new DateTime($row['deadline']);
          $today = new DateTime(date('Y-m-d'));
          $interval = $today->diff($deadline);
          $sisa_hari = $interval->format('%r%a');
        ?>
        <div class="card">
          <h4><?= htmlspecialchars($row['judul']) ?></h4>
          <p>Mata Kuliah: <?= htmlspecialchars($row['Nama_mk']) ?></p>
          <p>
            Deadline: <?= date('d F Y', strtotime($row['deadline'])) ?>
            <?php if ($sisa_hari <= 0 && ($row['Status'] == 'belum' || $row['Status'] == 'proses')): ?>
              <span class="sisa-hari">(Lewat)</span>
            <?php elseif (in_array($row['Status'], ['belum', 'proses'])): ?>
              <span class="sisa-hari">(H-<?= $sisa_hari ?>)</span>
            <?php endif; ?>
          </p>
          <p><small>Dibuat: <?= date('d M Y', strtotime($row['dibuat_pada'])) ?></small></p>
          <!-- Status Badge -->
          <span class="badge <?= strtolower($row['Status']) ?>">
            <?= 
              strtolower($row['Status']) == 'belum' ? 'Belum Selesai' : 
              (strtolower($row['Status']) == 'proses' ? 'Sedang Dikerjakan' : 'Selesai') 
            ?>
          </span>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card"><p>Belum ada tugas.</p></div>
      <?php endif; ?>

    <!--menampilkan rata-rata penyelesaian tugas--->
      <div class="card summary">
        <p><strong>📈 Rata-rata Penyelesaian Tugas:</strong> 
        <?= $rata_selesai >= 0 ? "{$rata_selesai} hari lebih lambat dari deadline" : abs($rata_selesai) . " hari lebih cepat dari deadline" ?>
        </p>
      </div>

      </div>
    </main>
  </div>

  <!-- Chart -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($status_labels) ?>,
            datasets: [{
                data: <?= json_encode($status_values) ?>,
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'DISTRIBUSI STATUS TUGAS' }
            }
        }
    });
  </script>
</body>
</html>
