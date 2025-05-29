<?php
session_start();
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correoElectronico = $_POST['correoElectronico'];
    $password = $_POST['password'];

    $db = new MysqlConnector();
    $conn = $db->connect();

    if (!$conn) {
        echo "Error al conectar a la base de datos: " . $conn->connect_error;
        exit();
    }

    $sql = "SELECT * FROM clientes WHERE correoElectronico = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Error en la consulta SQL: " . $conn->error;
        exit();
    }

    $stmt->bind_param("s", $correoElectronico);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();

        if (password_verify($password, $cliente['password'])) {
            $_SESSION['cliente_id'] = $cliente['idCliente'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];

            // Limpiar carrito al iniciar sesión
            $_SESSION['carrito'] = [];

            header("Location: productos.php");
            exit();
        } else {
            echo "<script>alert('Credenciales incorrectas.');</script>";
        }
    } else {
        echo "<script>alert('Credenciales incorrectas.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Cliente - Juárez Joyería</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Fondo plateado general */
        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", serif;
            background-color: #C0C0C0; /* plateado */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        /* Icono de casita */
        .home-icon {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            color: #b89b5e; /* dorado */
            z-index: 100;
        }

        .home-icon:hover {
            color: #a88c4e; /* dorado más oscuro */
        }

        /* Contenedor de login */
        .login-container {
            background-color: #f8f8f8; /* un gris muy claro para contraste */
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 340px;
            text-align: center;
            position: relative;
        }

        .login-container h2 {
            margin-bottom: 30px;
            color: #b89b5e; /* dorado principal */
            font-size: 1.8rem;
        }

        label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #b89b5e; /* dorado */
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #a88c4e; /* dorado más oscuro */
        }

        .alert {
            color: #a00;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <a href="index.html" class="home-icon" title="Volver al inicio">
        <i class="fas fa-home"></i>
    </a>

    <div class="login-container">
        <h2>Iniciar sesión</h2>
        <form method="POST" action="">
            <label for="correoElectronico">Correo Electrónico:</label>
            <input type="email" name="correoElectronico" id="correoElectronico" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>