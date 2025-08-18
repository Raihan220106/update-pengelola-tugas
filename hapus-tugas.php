<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

if (isset($_GET['id'])) {
    $id_tugas = intval($_GET['id']);
    $id_user = $_SESSION['user_id'];

    // Pastikan tugas yang akan dihapus milik user yang sedang login
    $stmt = $conn->prepare("DELETE FROM tugas WHERE id_tugas = ? AND id_user = ?");
    $stmt->bind_param("ii", $id_tugas, $id_user);

    if ($stmt->execute()) {
        $_SESSION['pesan'] = "Tugas berhasil dihapus.";
    } else {
        $_SESSION['pesan'] = "Gagal menghapus tugas.";
    }

    $stmt->close();
} else {
    $_SESSION['pesan'] = "Tugas tidak ditemukan.";
}

// Redirect kembali ke halaman daftar tugas
header("Location: Tugassaya.php");
exit;
?>
