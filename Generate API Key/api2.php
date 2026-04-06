<?php
header('Content-Type: application/json');
require_once 'koneksi2.php'; // Pastikan file koneksi.php isinya hanya koneksi DB

$headers = apache_request_headers();
// Menangani case sensitivity (beberapa server menggunakan x-api-key huruf kecil)
$api_key_input = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : (isset($headers['x-api-key']) ? $headers['x-api-key'] : '');

if (empty($api_key_input)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "API KEY diperlukan!"]);
    exit;
}

// Gunakan variabel $api_key_input di dalam query
$sql_user = "SELECT * FROM users WHERE api_key = '$api_key_input' AND api_key IS NOT NULL";
$cek_user = mysqli_query($conn, $sql_user);

if (mysqli_num_rows($cek_user) > 0) {
    $sql_menu = "SELECT * FROM menu_items";
    $query_menu = mysqli_query($conn, $sql_menu);
    
    $data_menu = [];
    while ($row = mysqli_fetch_assoc($query_menu)) {
        $data_menu[] = $row;
    }

    echo json_encode([
        "status" => "success", 
        "message" => "Data berhasil diambil",
        "data" => $data_menu
    ]);
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "API KEY tidak valid!"]);
}
?>