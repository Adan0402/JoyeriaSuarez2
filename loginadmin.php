<?php
session_start();
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $db = new MysqlConnector();
    $conn = $db->connect();

    // Seguridad: consulta preparada
    $stmt = $conn->prepare("SELECT * FROM admins WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Corrección aquí: usar $contrasena en lugar de $password
        if (password_verify($contrasena, $row['contrasena'])) {
            // Guardar info del admin en sesión
            $_SESSION['admin_id'] = $row['idAdmin'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['rol'] = 'admin';

            // Redirigir al dashboard
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "Usuario de administrador no encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Formulario HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px; }
        form { background: #fff; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        input[type=submit] { background: #5cb85c; color: white; border: none; cursor: pointer; }
        input[type=submit]:hover { background: #4cae4c; }
        p { text-align: center; color: red; }
    </style>
</head>
<body>

<h2 style="text-align: center;">Acceso Administrador</h2>

<form method="POST" action="login_admin.php">
    <label>Usuario:</label>
    <input type="text" name="usuario" required>

    <label>Contraseña:</label>
    <input type="password" name="contrasena" required>

    <input type="submit" value="Ingresar">
</form>

<?php
if (isset($error_message)) {
    echo "<p>$error_message</p>";
}
?>

</body>
</html>


