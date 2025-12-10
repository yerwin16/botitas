<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../classes/Post.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "No autorizado."));
    exit;
}

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

$post->usuario_id = $_SESSION['user_id'];
$post->contenido = isset($_POST['contenido']) ? $_POST['contenido'] : '';

$image_url = '';

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../uploads/";
    // Rename file to unique id
    $file_ext = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($file_ext, $allowed)) {
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $image_url = "uploads/" . $new_filename;
        }
    }
}

$post->imagen = $image_url;

if ($post->create()) {
    http_response_code(201);
    echo json_encode(array("message" => "Publicación creada."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "No se pudo crear la publicación."));
}
?>