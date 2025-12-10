<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/Database.php';
include_once '../../classes/User.php';

if (isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $user->id = $_SESSION['user_id'];
    $userData = $user->getUser();

    echo json_encode(array(
        "logged_in" => true,
        "user" => $userData
    ));
} else {
    echo json_encode(array("logged_in" => false));
}
?>