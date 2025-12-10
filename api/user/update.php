<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../classes/User.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "No autorizado."));
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$user->id = $_SESSION['user_id'];
$messages = array();

// Update Info
if (isset($_POST['nombre'])) {
    $user->nombre = $_POST['nombre'];
    $user->descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $user->update();
}

// Helper for file upload
function uploadImage($file_key)
{
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../uploads/";
        $file_ext = strtolower(pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid() . '_' . $file_key . '.' . $file_ext;
            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_dir . $new_filename)) {
                return "uploads/" . $new_filename;
            }
        }
    }
    return false;
}

// Update Profile Pic
$new_profile_pic = uploadImage('foto_perfil');
if ($new_profile_pic) {
    $user->updatePhotos('profile', $new_profile_pic);
    $messages[] = "Foto perfil actualizada.";
}

// Update Cover Pic
$new_cover_pic = uploadImage('foto_portada');
if ($new_cover_pic) {
    $user->updatePhotos('cover', $new_cover_pic);
    $messages[] = "Foto portada actualizada.";
}

echo json_encode(array("message" => "Perfil actualizado. " . implode(" ", $messages)));
?>