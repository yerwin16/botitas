<?php
class Comment
{
    private $conn;
    private $table_name = "comentarios";

    public $id;
    public $publicacion_id;
    public $usuario_id;
    public $contenido;
    public $fecha_creacion;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET publicacion_id=:publicacion_id, usuario_id=:usuario_id, contenido=:contenido";
        $stmt = $this->conn->prepare($query);

        $this->contenido = htmlspecialchars(strip_tags($this->contenido));
        $this->publicacion_id = htmlspecialchars(strip_tags($this->publicacion_id));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));

        $stmt->bindParam(":publicacion_id", $this->publicacion_id);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":contenido", $this->contenido);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByPostId($post_id)
    {
        $query = "SELECT c.id, c.contenido, c.fecha_creacion, u.nombre, u.foto_perfil 
                  FROM " . $this->table_name . " c
                  LEFT JOIN usuarios u ON c.usuario_id = u.id
                  WHERE c.publicacion_id = ?
                  ORDER BY c.fecha_creacion ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $post_id);
        $stmt->execute();
        return $stmt;
    }
}
?>