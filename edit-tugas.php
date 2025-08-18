<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

// Ambil ID tugas
$id_tugas = $_GET['id'] ?? null;
if (!$id_tugas) {
    die("ID tugas tidak ditemukan.");
}

// Ambil data tugas berdasarkan ID
$query = "SELECT * FROM tugas WHERE id_tugas = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();

if (!$tugas) {
    die("Data tugas tidak ditemukan.");
}

// Ambil daftar mata kuliah
$query_mk = mysqli_query($conn, "SELECT id_mk, Nama_mk FROM mata_kuliah ORDER BY Nama_mk ASC");

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $id_mk = $_POST['id_mk'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];

    if (!empty($judul) && !empty($id_mk) && !empty($deadline) && !empty($status)) {
        if (!DateTime::createFromFormat('Y-m-d', $deadline)) {
            echo "Format tanggal tidak valid.";
            exit;
        }

        if ($status === 'selesai') {
            $tanggal_selesai = date('Y-m-d');
            $sql = "UPDATE tugas SET judul=?, id_mk=?, deadline=?, Status=?, tanggal_selesai=? WHERE id_tugas=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisssi", $judul, $id_mk, $deadline, $status, $tanggal_selesai, $id_tugas);
        } else {
            // jika status bukan "selesai", kosongkan tanggal_selesai
            $sql = "UPDATE tugas SET judul=?, id_mk=?, deadline=?, Status=?, tanggal_selesai=NULL WHERE id_tugas=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissi", $judul, $id_mk, $deadline, $status, $id_tugas);
        }

        if ($stmt->execute()) {
            $_SESSION['pesan'] = "Tugas berhasil diperbarui!";
            header("Location: Tugassaya.php");
            exit;
        } else {
             echo "Gagal mengupdate tugas. Error: " . $stmt->error;
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
  <title>Edit Tugas</title>
  <link rel="stylesheet" href="css/style_tugas.css">
</head>
<body>
  <div class="container">
    <h2>Edit Tugas</h2>

    <!-- Form Edit Tugas -->
    <form action="edit-tugas.php?id=<?= $id_tugas ?>" method="post" class="form-tugas">
      <label for="judul">Judul Tugas:</label>
      <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($tugas['judul']) ?>" required />

      <label for="matkul">Mata Kuliah:</label>
      <select id="matkul" name="id_mk" required>
        <option value="">-- Pilih Mata Kuliah --</option>
        <?php while ($row = mysqli_fetch_assoc($query_mk)) :
          $selected = ($tugas['id_mk'] == $row['id_mk']) ? 'selected' : '';
        ?>
          <option value="<?= $row['id_mk'] ?>" <?= $selected ?>>
            <?= htmlspecialchars($row['Nama_mk']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="deadline">Deadline:</label>
      <input type="date" id="deadline" name="deadline" value="<?= $tugas['deadline'] ?>" required>

      <label for="status">Status Tugas:</label>
      <select id="status" name="status" required>
        <option value="belum" <?= $tugas['Status'] == 'belum' ? 'selected' : '' ?>>Belum Selesai</option>
        <option value="proses" <?= $tugas['Status'] == 'proses' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
        <option value="selesai" <?= $tugas['Status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
      </select>

      <button type="submit" class="btn-submit">Simpan Perubahan</button>
      <a href="Tugassaya.php" class="btn-kembali">← Kembali</a>
    </form>
  </div>
</body>
</html>
