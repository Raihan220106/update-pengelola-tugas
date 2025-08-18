<?php
//email notification
session_start();
// require 'send_email.php';

// Cek apakah user login
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

// Koneksi ke database
require 'koneksi.php';

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil daftar mata kuliah dari database
$query = mysqli_query($conn, "SELECT id_mk, Nama_mk FROM mata_kuliah ORDER BY Nama_mk ASC");

$pesan = ""; // Variabel untuk menyimpan pesan

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $id_mk = $_POST['id_mk'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    $tanggal_selesai = ($status === 'selesai') ? date('Y-m-d') : null;
    $id_user = $_SESSION['user_id'];

// Validasi sederhana
    if (!empty($judul) && !empty($id_mk) && !empty($deadline) && !empty($status)) {
        
      if (!DateTime::createFromFormat('Y-m-d', $deadline)) {
            echo "Format tanggal tidak valid.";
            exit;
        }
        
      // Query INSERT
        $sql = "INSERT INTO tugas (id_user, id_mk, judul, deadline, Status, tanggal_selesai) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $id_user, $id_mk, $judul, $deadline, $status, $tanggal_selesai);


        if ($stmt->execute()) {
            // Redirect ke dashboard setelah berhasil
            $_SESSION['pesan'] = "Tugas berhasil ditambahkan!";
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Gagal menyimpan data.";
        }
    } else {
        echo "Semua field harus diisi.";
    }
}
?>
    
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tambah Tugas</title>
  <link rel="stylesheet" href="style_tugas.css">
</head>
<body>
  <div class="container">
    <h2>Tambah Tugas Baru</h2>

    <!-- Form tambah tugas -->
    <form action="tambah-tugas.php" method="post" class="form-tugas">
      <label for="judul">Judul Tugas:</label>
      <input type="text" id="judul" name="judul" required />

      <label for="matkul">Mata Kuliah:</label>
      <select id="matkul" name="id_mk" required>
        <option value="">-- Pilih Mata Kuliah --</option>
        <?php
        if ($query && mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                $selected = (isset($id_mk) && $id_mk == $row['id_mk']) ? 'selected' : '';
                echo "<option value='{$row['id_mk']}' $selected>{$row['Nama_mk']}</option>";
            }
        } else {
            echo "<option value=''>Tidak ada mata kuliah tersedia</option>";
        }
        ?>
      </select>

      <label for="deadline">Deadline:</label>
      <input type="date" id="deadline" name="deadline" value="<?= isset($deadline) ? htmlspecialchars($deadline) : '' ?>" required>

      <label for="status">Status Tugas:</label>
      <select id="status" name="status" required>
        <option value="belum" <?= (isset($status) && $status == 'belum') ? 'selected' : '' ?>>Belum Selesai</option>
        <option value="proses" <?= (isset($status) && $status == 'proses') ? 'selected' : '' ?>>Sedang Dikerjakan</option>
        <option value="selesai" <?= (isset($status) && $status == 'selesai') ? 'selected' : '' ?>>Selesai</option>
      </select>

      <button type="submit" class="btn-submit">Simpan Tugas</button>
      <a href="dashboard.php" class="btn-kembali">← Kembali</a>
    </form>
  </div>
</body>
</html>