<?php
require 'koneksi.php';

session_start();

$email = $_POST['email'];
$password = $_POST['password'];

$query_sql = "SELECT * FROM users 
            WHERE email = '$email' AND password = '$password'";

$result = mysqli_query($conn, $query_sql);

if (mysqli_num_rows($result) > 0) {
    // Get user data
    $user = mysqli_fetch_assoc($result);

    // Set session variables
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['nama'] = $user['nama'];  // Assuming your users table has a 'nama' column
    $_SESSION['email'] = $user['email'];
    
    header("Location: dashboard.php");
} else {
    echo "<center><h1>Email atau Password Anda Salah. Silahkan Coba Login Kenbali.</h1>
        <button><strong><a href='index.html'>Login</a></strong></button></center>";
}
?>