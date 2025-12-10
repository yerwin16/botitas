<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->correo) && !empty($data->password)) {
    $user->correo = $data->correo;
    $user->password = $data->password;

    if ($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->nombre;

        http_response_code(200);
        echo json_encode(array(
            "message" => "Login exitoso.",
            "user_id" => $user->id,
            "nombre" => $user->nombre,
            "foto_perfil" => $user->foto_perfil
        ));
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Credenciales inválidas."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos."));
}
?>