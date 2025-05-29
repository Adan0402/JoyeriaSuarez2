<?php
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $correoElectronico = $_POST['correoElectronico'];
    $direccionPostal = $_POST['direccionPostal'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $pais = $_POST['pais'];
    $codigoPostal = $_POST['codigoPostal'];
    $telefono = $_POST['telefono'];
    $password = $_POST['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $db = new MysqlConnector();
    $conn = $db->connect();

    $sql = "INSERT INTO clientes (nombre, apellidos, correoElectronico, direccionPostal, colonia, ciudad, estado, pais, codigoPostal, password, telefono) 
            VALUES ('$nombre', '$apellidos', '$correoElectronico', '$direccionPostal', '$colonia', '$ciudad', '$estado', '$pais', '$codigoPostal', '$hashedPassword', '$telefono')";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: productos.php");
        exit(); // Importante para detener ejecución
    } else {
        echo "<div style='color: red; font-weight: bold; text-align: center;'>Error: " . mysqli_error($conn) . "</div>";
    }

    $db->close();
} else {
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Cliente | Juárez Joyería</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            color: #b89b5e;
            margin-bottom: 20px;
        }
        p {
            text-align: center;
            color: #555;
            font-size: 14px;
            margin-bottom: 30px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #b89b5e;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #a88c4e;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>¡Únete a Juárez Joyería!</h2>
        <p>Regístrate para acceder a nuestras colecciones exclusivas y recibir ofertas especiales.</p>
        <form method="POST" action="FrmAddCliente.php">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellidos" placeholder="Apellidos" required>
            <input type="email" name="correoElectronico" placeholder="Correo Electrónico" required>
            <input type="text" name="direccionPostal" placeholder="Dirección Postal" required>
            <input type="text" name="colonia" placeholder="Colonia" required>
            <input type="text" name="ciudad" placeholder="Ciudad" required>
            <input type="text" name="estado" placeholder="Estado" required>
            <input type="text" name="pais" placeholder="País" required>
            <input type="text" name="codigoPostal" placeholder="Código Postal" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="telefono" placeholder="telefono" required>
            <input type="submit" value="Registrarme">
        </form>
    </div>
</body>
</html>

<?php
}
?>
