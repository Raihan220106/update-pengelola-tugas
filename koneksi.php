<?php
$servername = "localhost";
$database   = "sistem_pengingat"; // nama database
$username   = "root";              // default XAMPP username
$password   = "";                  // default XAMPP password kosong

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>
