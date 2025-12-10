<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../classes/Comment.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "No autorizado."));
    exit;
}

$database = new Database();
$db = $database->getConnection();
$comment = new Comment($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->publicacion_id) && !empty($data->contenido)) {
    $comment->usuario_id = $_SESSION['user_id'];
    $comment->publicacion_id = $data->publicacion_id;
    $comment->contenido = $data->contenido;

    if ($comment->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Comentario agregado."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo comentar."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos."));
}
?>