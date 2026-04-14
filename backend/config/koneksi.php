<?php
// koneksi.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kopi_toast"; // Pastikan nama DB sesuai

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>