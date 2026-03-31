<?php
header('Content-Type: application/json');
// Sesuaikan path ke file koneksi milikmu
require_once '../config/koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Gunakan POST."]);
    exit;
}

$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Username, email, dan password wajib diisi!"]);
    exit;
}

$cek_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
$cek_result = mysqli_query($conn, $cek_query);

if (mysqli_num_rows($cek_result) > 0) {
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "Username atau email sudah terdaftar!"]);
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Registrasi berhasil! Silakan login untuk mendapatkan API KEY."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Terjadi kesalahan database: " . mysqli_error($conn)]);
}
?>