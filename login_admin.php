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
        if (password_verify($contrasena, $row['contrasena'])) {
            $_SESSION['admin_id'] = $row['idAdmin'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['rol'] = 'admin';
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f9f9f9;
        }
        .login-container {
            width: 380px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-top: 5px solid #B89B5E;
        }
        .login-header {
            background: #B89B5E;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1.5em;
            font-weight: 600;
        }
        .login-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: #B89B5E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(184, 155, 94, 0.2);
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background: #B89B5E;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-button:hover {
            background: #9C824A;
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .footer-text {
            text-align: center;
            color: #777;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            Acceso Administrador
        </div>
        <div class="login-body">
            <form method="POST" action="login_admin.php">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
                <button type="submit" class="login-button">Ingresar</button>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="footer-text">
                    Sistema de Administración © <?php echo date('Y'); ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>