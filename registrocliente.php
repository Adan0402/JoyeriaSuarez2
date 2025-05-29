<?php
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correoElectronico = $_POST['correoElectronico'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar contraseña

    $db = new MysqlConnector();
    $conn = $db->connect();

    // Insertamos solo nombre, correo y contraseña
    $sql = "INSERT INTO clientes (nombre, correoElectronico, contrasena)
            VALUES ('$nombre', '$correoElectronico', '$contrasena')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>Registro exitoso. ¡Ahora puedes iniciar sesión!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }

    $db->close();
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Cliente</title>
    <link rel="stylesheet" href="style.css"> <!-- Tu archivo de estilos -->
</head>
<body>
    <h2>Registro de Nuevo Cliente</h2>
    <form method="POST" action="registroCliente.php">
        Nombre: <input type="text" name="nombre" required><br><br>
        Correo Electrónico: <input type="email" name="correoElectronico" required><br><br>
        Contraseña: <input type="password" name="contrasena" required><br><br>
        <input type="submit" value="Registrarse">
    </form>
</body>
</html>
<?php
}
?>
