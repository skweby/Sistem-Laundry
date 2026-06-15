<?php
// config.php
$host = "localhost";
$user = "root";     
$pass = "";         
$db   = "ilham_laundry";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>