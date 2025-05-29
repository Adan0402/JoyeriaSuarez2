<?php
class MysqlConnector {
    private $server;
    private $connUser;
    private $connPassword;
    private $connDb;
    var $connection;

    function __construct() {
        // Dirección IP del contenedor MySQL
        $this->server = "bdatos";  
        $this->connUser = "root";
        $this->connPassword = "root";
        $this->connDb = "joyeria";  
    }

    public function connect() {
        $this->connection = mysqli_connect(
            $this->server, $this->connUser, $this->connPassword, $this->connDb
        );

        // Verifica si la conexión fue exitosa
        if (!$this->connection) {
            echo "Error al intentar conectar: " . mysqli_connect_error();
            exit();
        }
        return $this->connection;
    }

    public function close() {
        mysqli_close($this->connection);  // Cierra la conexión a la base de datos
    }
}
?>
