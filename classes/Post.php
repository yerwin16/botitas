<?php
class Post
{
    private $conn;
    private $table_name = "publicaciones";

    public $id;
    public $usuario_id;
    public $contenido;
    public $imagen;
    public $fecha_creacion;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET usuario_id=:usuario_id, contenido=:contenido, imagen=:imagen";
        $stmt = $this->conn->prepare($query);

        $this->contenido = htmlspecialchars(strip_tags($this->contenido));
        $this->imagen = htmlspecialchars(strip_tags($this->imagen));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));

        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":contenido", $this->contenido);
        $stmt->bindParam(":imagen", $this->imagen);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT p.id, p.contenido, p.imagen, p.fecha_creacion, u.nombre, u.foto_perfil, u.id as user_id 
                  FROM " . $this->table_name . " p
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  ORDER BY p.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByUserId($user_id)
    {
        $query = "SELECT p.id, p.contenido, p.imagen, p.fecha_creacion, u.nombre, u.foto_perfil, u.id as user_id 
                  FROM " . $this->table_name . " p
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.usuario_id = ?
                  ORDER BY p.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }
}
?>