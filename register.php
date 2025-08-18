<?php
// require 'send_email.php';
// sendNotification($email, 'Registrasi Berhasil', 'Halo, akun Anda berhasil didaftarkan di <strong>Sistem Pengingat Tugas</strong>.');
require 'koneksi.php';
$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);


$query_sql = "INSERT INTO users (Nama, email, password) 
            VALUES ('$nama', '$email', '$password')";

if (mysqli_query($conn, $query_sql)) {
    header("Location: index.html");
} else {
    echo "Pendaftaran Gagal: " . mysqli_error($conn);
}

