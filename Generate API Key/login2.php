<?php
header('Content-Type: application/json');
require_once 'koneksi2.php';

// Memastikan request menggunakan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Gunakan POST."]);
    exit;
}

// Menangkap data input
$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validasi form kosong
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Username dan password wajib diisi!"]);
    exit;
}

// Cari data user berdasarkan username di database
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Verifikasi kecocokan password yang diinput dengan hash di database
    if (password_verify($password, $user['password'])) {
        
        // Buat API KEY unik (Contoh hasil: KOPI-a1b2c3d4e5f6g7h8)
        $api_key = "KOPI-" . bin2hex(random_bytes(8));
        $user_id = $user['id'];

        // Update API KEY user tersebut di database
        $update_sql = "UPDATE users SET api_key = '$api_key' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            // Berhasil login dan berhasil generate API KEY
            echo json_encode([
                "status" => "success",
                "message" => "Login berhasil!",
                "api_key" => $api_key
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Gagal membuat API KEY."]);
        }
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(["status" => "error", "message" => "Password yang Anda masukkan salah!"]);
    }
} else {
    http_response_code(404); // Not Found
    echo json_encode(["status" => "error", "message" => "Username tidak ditemukan!"]);
}
?>