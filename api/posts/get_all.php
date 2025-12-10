<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/Database.php';
include_once '../../classes/Post.php';
include_once '../../classes/Comment.php';

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);
$comment = new Comment($db);

$stmt = $post->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $posts_arr = array();
    $posts_arr["records"] = array(); // Para mantener estructura estándar

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // Obtener comentarios para este post
        $comment_stmt = $comment->readByPostId($id);
        $comments_list = array();
        while ($comment_row = $comment_stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments_list[] = $comment_row;
        }

        $post_item = array(
            "id" => $id,
            "contenido" => $contenido,
            "imagen" => $imagen,
            "fecha_creacion" => $fecha_creacion,
            "autor" => $nombre,
            "autor_foto" => $foto_perfil,
            "autor_id" => $user_id,
            "comentarios" => $comments_list
        );

        array_push($posts_arr["records"], $post_item);
    }
    http_response_code(200);
    echo json_encode($posts_arr);
} else {
    http_response_code(200); // 200 OK pero con lista vacía
    echo json_encode(array("records" => []));
}
?>