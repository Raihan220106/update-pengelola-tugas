<?php
require 'koneksi.php';

// Pastikan koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil data dari tabel mata_kuliah
$sql = "SELECT * FROM mata_kuliah";
$result = $conn->query($sql);

// Pastikan query berhasil dijalankan
if ($result === false) {
    die("Query gagal dijalankan: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mata Kuliah</title>
  <link rel="stylesheet" href="matkul.css" />
</head>
<body>

  <div class="container">
    <aside class="sidebar">
      <div class="logo">📚 TugasKu</div>
      <ul class="nav">
        <li><a href="dashboard.php">🏡 Home</a></li>
        <li class="active"><a href="#">📚 Mata Kuliah</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <header class="header">
        <div class="page-title">Mata Kuliah</div>
        <div class="profile">
          <span class="name">Semester 2</span>
        </div>
      </header>

      <section class="class-grid">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()):
                // Membentuk path gambar
                $filename = strtolower(str_replace(' ', '_', $row['Nama_mk']));
                $imagePath = "images/$filename.png";

                if (!file_exists($imagePath)) {
                    $imagePath = "images/$filename.jpg";
                    
                    if (!file_exists($imagePath)) {
                        $imagePath = "https://via.placeholder.com/280x150?text=No+Image";
                    }
                }
            ?>
            <div class="class-card">
              <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($row['Nama_mk']) ?>">
              <div class="card-body">
                <h3><?= htmlspecialchars($row['Nama_mk']) ?></h3>
                <p><?= htmlspecialchars($row['deskripsi']) ?></p>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>Tidak ada mata kuliah tersedia.</p>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <?php $conn->close(); ?>
</body>
</html>