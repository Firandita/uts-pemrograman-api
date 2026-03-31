<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Gunakan POST."]);
    exit;
}

$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Username dan password wajib diisi!"]);
    exit;
}

$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {
        // Generate API KEY baru setiap kali login (Sesuai tugas No 2)
        $api_key = "KOPI-" . bin2hex(random_bytes(8));
        $user_id = $user['id'];

        $update_sql = "UPDATE users SET api_key = '$api_key' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
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
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Password salah!"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Username tidak ditemukan!"]);
}
?>