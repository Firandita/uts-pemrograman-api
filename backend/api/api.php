<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

// --- LOGIKA CEK API KEY (Dari temanmu) ---
$headers = apache_request_headers();
$api_key_input = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : (isset($headers['x-api-key']) ? $headers['x-api-key'] : '');

if (empty($api_key_input)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "API KEY diperlukan!"]);
    exit;
}

$sql_user = "SELECT * FROM users WHERE api_key = '$api_key_input' AND api_key IS NOT NULL";
$cek_user = mysqli_query($conn, $sql_user);

if (mysqli_num_rows($cek_user) == 0) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "API KEY tidak valid!"]);
    exit;
}

// --- LOGIKA CRUD MENU ---
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = mysqli_query($conn, "SELECT * FROM menu_items");
        $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

   case 'POST':
        // Membaca input JSON dari Postman
        $input = json_decode(file_get_contents("php://input"), true);
        
        // 1. Tangkap semua data dari JSON input (Gunakan isset agar tidak muncul Warning jika ada data kosong)
        $nama_item = isset($input['nama_item']) ? mysqli_real_escape_string($conn, $input['nama_item']) : '';
        $size      = isset($input['size'])      ? mysqli_real_escape_string($conn, $input['size']) : '';
        $category  = isset($input['category'])  ? mysqli_real_escape_string($conn, $input['category']) : '';
        $harga     = isset($input['harga'])     ? intval($input['harga']) : 0;
        $notes     = isset($input['notes'])     ? mysqli_real_escape_string($conn, $input['notes']) : '';

        // 2. Cek validasi minimal (nama_item tidak boleh kosong)
        if (empty($nama_item)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nama item wajib diisi!"]);
            break;
        }

        // 3. Susun Query INSERT yang benar sesuai urutan kolom
        $sql = "INSERT INTO menu_items (nama_item, size, category, harga, notes) 
                VALUES ('$nama_item', '$size', '$category', '$harga', '$notes')";
        
        if(mysqli_query($conn, $sql)) {
            http_response_code(201); // Created
            echo json_encode(["status" => "success", "message" => "Menu berhasil ditambah"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Gagal: " . mysqli_error($conn)]);
        }
        break;

    // belum ku tambahin delete sama update dll/ besok aja yaa, yang penting bisa register sama login dulu, terus bisa nambah menu, nanti tinggal di expand lagi buat fitur lainnya

}
?>