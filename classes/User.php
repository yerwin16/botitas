<?php
class User
{
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $correo;
    public $password;
    public $foto_perfil;
    public $foto_portada;
    public $descripcion;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Registrar Usuario
    public function register()
    {
        $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre, correo=:correo, password=:password";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->password = htmlspecialchars(strip_tags($this->password));

        // Hash password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":password", $password_hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login Usuario
    public function login()
    {
        $query = "SELECT id, nombre, password, foto_perfil, foto_portada, descripcion FROM " . $this->table_name . " WHERE correo = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);

        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $stmt->bindParam(1, $this->correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->nombre = $row['nombre'];
                $this->foto_perfil = $row['foto_perfil'];
                $this->foto_portada = $row['foto_portada'];
                $this->descripcion = $row['descripcion'];
                return true;
            }
        }
        return false;
    }

    // Obtener datos del usuario
    public function getUser()
    {
        $query = "SELECT id, nombre, correo, foto_perfil, foto_portada, descripcion, fecha_registro FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar perfil
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET nombre=:nombre, descripcion=:descripcion WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar fotos
    public function updatePhotos($type, $filename)
    {
        $field = ($type === 'profile') ? 'foto_perfil' : 'foto_portada';
        $query = "UPDATE " . $this->table_name . " SET " . $field . "=:filename WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $filename = htmlspecialchars(strip_tags($filename));

        $stmt->bindParam(":filename", $filename);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>