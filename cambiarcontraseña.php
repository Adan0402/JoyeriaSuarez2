<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginCliente.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_SESSION['cliente_id'];
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    $db = new MysqlConnector();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT contrasena FROM clientes WHERE idCliente = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->bind_result($contrasenaGuardada);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($contrasena_actual, $contrasenaGuardada)) {
        $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE clientes SET contrasena = ? WHERE idCliente = ?");
        $stmt->bind_param("si", $hash, $cliente_id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "✅ Contraseña actualizada con éxito.";
    } else {
        $mensaje = "❌ La contraseña actual es incorrecta.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cambiar Contraseña - Joyería Suárez</title>
  <style>
    body {
      font-family: 'Georgia', serif;
      background-color: #fff;
      color: #333;
      padding: 0;
      margin: 0;
    }

    header {
      background-color: #000;
      color: #d4af37;
      text-align: center;
      padding: 20px;
      font-size: 24px;
    }

    .container {
      max-width: 500px;
      margin: 50px auto;
      background-color: #f9f9f9;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
    }

    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background-color: #d4af37;
      color: #000;
      border: none;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }

    .mensaje {
      margin-top: 15px;
      text-align: center;
      font-weight: bold;
      color: #b22222;
    }

    .volver {
      text-align: center;
      margin-top: 20px;
    }

    .volver a {
      color: #bfa046;
      text-decoration: none;
      font-weight: bold;
    }

    .volver a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<header>
  Cambiar Contraseña
</header>

<div class="container">
  <form method="post">
    <label for="contrasena_actual">Contraseña actual:</label>
    <input type="password" name="contrasena_actual" required>

    <label for="nueva_contrasena">Nueva contraseña:</label>
    <input type="password" name="nueva_contrasena" required>

    <button type="submit">Actualizar Contraseña</button>
  </form>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <div class="volver">
    <a href="perfil.php">← Volver al perfil</a>
  </div>
</div>

</body>
</html>
