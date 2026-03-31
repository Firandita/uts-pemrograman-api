<?php
// register.php
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['username']) && isset($data['password'])) {
        $user = $conn->real_escape_string($data['username']);
        $email = isset($data['email']) ? $conn->real_escape_string($data['email']) : '';
        $pass = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Membuat API KEY unik (Ini yang diminta soal UTS)
        $api_key = "KOPI-" . bin2hex(random_bytes(8)); 

        $sql = "INSERT INTO users (username, email, password, api_key) VALUES ('$user', '$email', '$pass', '$api_key')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "User berhasil didaftarkan",
                "api_key" => $api_key // Tampilkan agar bisa dicopy user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metode harus POST"]);
}
?>