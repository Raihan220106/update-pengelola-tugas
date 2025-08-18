<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

require 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Daftar Tugas</title>
  <link rel="stylesheet" href="tugas_saya.css">
</head>
<body>
  <?php if (isset($_SESSION['pesan'])): ?>
  <div class="alert">
    <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
  </div>
  <?php endif; ?>

  <h1>📚 Daftar Tugas Mata Kuliah</h1>

  <!-- Form Filter -->
  <div class="filter-container">
    <form method="GET" action="Tugassaya.php">
      <div>
        <label for="filterMatkul">Mata Kuliah:</label>
        <select id="filterMatkul" name="filter_mk">
          <option value="">Semua</option>
          <?php
            $mk_query = mysqli_query($conn, "SELECT id_mk, Nama_mk FROM mata_kuliah ORDER BY Nama_mk ASC");
            while ($mk = mysqli_fetch_assoc($mk_query)) {
              $selected = ($_GET['filter_mk'] ?? '') == $mk['id_mk'] ? 'selected' : '';
              echo "<option value='{$mk['id_mk']}' $selected>{$mk['Nama_mk']}</option>";
            }
          ?>
        </select>
      </div>

      <div>
        <label for="filterStatus">Status:</label>
        <select id="filterStatus" name="filter_status">
          <option value="">Semua Status</option>
          <option value="belum" <?= ($_GET['filter_status'] ?? '') == 'belum' ? 'selected' : '' ?>>Belum Selesai</option>
          <option value="proses" <?= ($_GET['filter_status'] ?? '') == 'proses' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
          <option value="selesai" <?= ($_GET['filter_status'] ?? '') == 'selesai' ? 'selected' : '' ?>>Selesai</option>
        </select>
      </div>

      <button type="submit">Filter</button>
      <a href="Tugassaya.php">Reset</a>
    </form>
  </div>

  <!-- Daftar Tugas -->
  <div class="task-list">
    <?php
    $id_user = $_SESSION['user_id'];
    $filter_mk = $_GET['filter_mk'] ?? '';
    $filter_status = $_GET['filter_status'] ?? '';

    // Query dengan filter
    $sql = "SELECT t.id_tugas, t.judul, m.Nama_mk, t.deadline, t.Status, t.dibuat_pada, t.tanggal_selesai
            FROM tugas t
            JOIN mata_kuliah m ON t.id_mk = m.id_mk
            WHERE t.id_user = ?";

    $params = [$id_user];
    $types = "i";

    if ($filter_mk) {
        $sql .= " AND t.id_mk = ?";
        $params[] = $filter_mk;
        $types .= "i";
    }
    if ($filter_status) {
        $sql .= " AND t.Status = ?";
        $params[] = $filter_status;
        $types .= "s";
    }

    $sql .= " ORDER BY t.deadline ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            $deadline = new DateTime($row['deadline']);
            $today = new DateTime();
            $interval = $today->diff($deadline);
            $sisa_hari = $interval->format('%r%a');
    ?>
    <div class="card">
        <h4><?= htmlspecialchars($row['judul']) ?></h4>
        <p><strong>Mata Kuliah:</strong> <?= htmlspecialchars($row['Nama_mk']) ?></p>
        <p>
          <strong>Deadline:</strong> <?= date('d F Y', strtotime($row['deadline'])) ?>
          <?php if ($sisa_hari <= 0 && $row['Status'] == 'belum'): ?>
            <span class="badge lewat">(Terlambat)</span>
          <?php elseif ($row['Status'] == 'belum'): ?>
            <span class="badge belum">(H-<?= $sisa_hari ?>)</span>
          <?php endif; ?>
        </p>
        <p><small>Dibuat: <?= date('d M Y', strtotime($row['dibuat_pada'])) ?></small></p>

        //menampilakan tanggal selesai jika ada
        <?php if (!empty($row['tanggal_selesai'])): ?>
        <p><strong>Selesai pada:</strong> <?= date('d F Y', strtotime($row['tanggal_selesai'])) ?></p>
        <?php endif; ?>  

        <span class="badge <?= strtolower($row['Status']) ?>">
          <?= 
            strtolower($row['Status']) == 'belum' ? 'Belum Selesai' : 
            (strtolower($row['Status']) == 'proses' ? 'Sedang Dikerjakan' : 'Selesai') 
          ?>
        </span>
        
        <div class="task-actions">
          <a href="edit-tugas.php?id=<?= $row['id_tugas'] ?>" class="btn-action btn-edit">Edit</a>
          <a href="hapus-tugas.php?id=<?= $row['id_tugas'] ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus tugas ini?')">Hapus</a>
        </div>
    </div>
    <?php 
        endwhile;
    else: 
    ?>
    <div class="card empty">
        <p>
          <?php if ($filter_mk || $filter_status): ?>
            Tidak ada tugas yang sesuai dengan filter yang dipilih.
          <?php else: ?>
            Belum ada tugas. <a href="tambah-tugas.php">Tambah tugas pertama Anda!</a>
          <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
  </div>

  <!-- Statistik Ringkas -->
  <?php
  $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN Status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN Status = 'belum' THEN 1 ELSE 0 END) as belum,
                    SUM(CASE WHEN Status = 'proses' THEN 1 ELSE 0 END) as proses
                  FROM tugas WHERE id_user = ?";
  $stmt = $conn->prepare($stats_query);
  $stmt->bind_param("i", $id_user);
  $stmt->execute();
  $stats = $stmt->get_result()->fetch_assoc();
  ?>

  <div class="summary-card">
    <h3>📊 Ringkasan Tugas</h3>
    <div class="summary-grid">
      <div><div class="jumlah"><?= $stats['total'] ?></div><div>Total Tugas</div></div>
      <div><div class="jumlah selesai"><?= $stats['selesai'] ?></div><div>Selesai</div></div>
      <div><div class="jumlah proses"><?= $stats['proses'] ?></div><div>Dikerjakan</div></div>
      <div><div class="jumlah belum"><?= $stats['belum'] ?></div><div>Belum</div></div>
    </div>
  </div>

<!--rata-rata selisih-->
            <?php
  $avg_query = "SELECT AVG(DATEDIFF(tanggal_selesai, deadline)) as rata 
                FROM tugas 
                WHERE id_user = ? AND Status = 'selesai' AND tanggal_selesai IS NOT NULL";
  $stmt = $conn->prepare($avg_query);
  $stmt->bind_param("i", $id_user);
  $stmt->execute();
  $avg_result = $stmt->get_result()->fetch_assoc();
  $rata_hari = round($avg_result['rata'], 1);
  ?>

  <div class="summary-card">
    <h3>📈 Rata-Rata Penyelesaian</h3>
    <div class="summary-grid">
      <div>
        <?php if ($avg_result['rata'] !== null): ?>
          <?php if ($rata_hari > 0): ?>
            <p>Rata-rata keterlambatan: <strong><?= $rata_hari ?> hari</strong></p>
          <?php elseif ($rata_hari < 0): ?>
            <p>Rata-rata lebih cepat: <strong><?= abs($rata_hari) ?> hari sebelum deadline</strong></p>
          <?php else: ?>
            <p>Tugas diselesaikan tepat waktu.</p>
          <?php endif; ?>
        <?php else: ?>
          <p>Belum ada tugas yang selesai.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="back-link">
    <a href="dashboard.php">← Kembali ke Dashboard</a>
  </div>
</body>
</html>
