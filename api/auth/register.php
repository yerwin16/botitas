<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->nombre) &&
    !empty($data->correo) &&
    !empty($data->password)
) {
    $user->nombre = $data->nombre;
    $user->correo = $data->correo;
    $user->password = $data->password;

    if ($user->register()) {
        http_response_code(201);
        echo json_encode(array("message" => "Usuario creado exitosamente."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo crear el usuario. Posiblemente el correo ya existe."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos."));
}
?>