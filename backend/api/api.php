<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

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


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = mysqli_query($conn, "SELECT * FROM menu_items");
        $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

   case 'POST':
     
        $input = json_decode(file_get_contents("php://input"), true);
        
        $nama_item = isset($input['nama_item']) ? mysqli_real_escape_string($conn, $input['nama_item']) : '';
        $size      = isset($input['size'])      ? mysqli_real_escape_string($conn, $input['size']) : '';
        $category  = isset($input['category'])  ? mysqli_real_escape_string($conn, $input['category']) : '';
        $harga     = isset($input['harga'])     ? intval($input['harga']) : 0;
        $notes     = isset($input['notes'])     ? mysqli_real_escape_string($conn, $input['notes']) : '';


        if (empty($nama_item)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nama item wajib diisi!"]);
            break;
        }

        $sql = "INSERT INTO menu_items (nama_item, size, category, harga, notes) 
                VALUES ('$nama_item', '$size', '$category', '$harga', '$notes')";
        
        if(mysqli_query($conn, $sql)) {
            http_response_code(201); 
            echo json_encode(["status" => "success", "message" => "Menu berhasil ditambah"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Gagal: " . mysqli_error($conn)]);
        }
        break;

   case 'PUT':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id === 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID item wajib disertakan di URL (?id=...)"]);
            break;
        }

        $input = json_decode(file_get_contents("php://input"), true);


        $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM menu_items WHERE id = $id"));
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Item tidak ditemukan!"]);
            break;
        }

        $nama_item = isset($input['nama_item']) ? mysqli_real_escape_string($conn, $input['nama_item']) : $existing['nama_item'];
        $size      = isset($input['size'])      ? mysqli_real_escape_string($conn, $input['size'])      : $existing['size'];
        $category  = isset($input['category'])  ? mysqli_real_escape_string($conn, $input['category'])  : $existing['category'];
        $harga     = isset($input['harga'])     ? intval($input['harga'])                               : $existing['harga'];
        $notes     = isset($input['notes'])     ? mysqli_real_escape_string($conn, $input['notes'])     : $existing['notes'];

        $sql = "UPDATE menu_items SET 
                    nama_item = '$nama_item', 
                    size      = '$size', 
                    category  = '$category', 
                    harga     = $harga, 
                    notes     = '$notes'
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(["status" => "success", "message" => "Menu berhasil diupdate"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Gagal: " . mysqli_error($conn)]);
        }
        break;

    case 'DELETE':
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id === 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID item wajib disertakan di URL (?id=...)"]);
            break;
        }

        
        $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM menu_items WHERE id = $id"));
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Item tidak ditemukan!"]);
            break;
        }

        if (mysqli_query($conn, "DELETE FROM menu_items WHERE id = $id")) {
            echo json_encode(["status" => "success", "message" => "Menu berhasil dihapus"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Gagal: " . mysqli_error($conn)]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method tidak didukung"]);
        break;

}
?>