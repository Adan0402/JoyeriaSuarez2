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
    $password_actual = $_POST['contrasena_actual'];
    $nueva_password = $_POST['nueva_contrasena'];

    $db = new MysqlConnector();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT password FROM clientes WHERE idCliente = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->bind_result($passwordGuardada);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password_actual, $passwordGuardada)) {
        $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE clientes SET password = ? WHERE idCliente = ?");
        $stmt->bind_param("si", $hash, $cliente_id);
        
        if ($stmt->execute()) {
            $mensaje = "✅ Contraseña actualizada con éxito.";
        } else {
            $mensaje = "❌ Error al actualizar la contraseña.";
        }
        
        $stmt->close();
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/stylo_cambiarcontraseña.css">
</head>
<body>

<div class="home-icon">
  <a href="perfil.php" title="Volver al perfil">
    <i class="fas fa-home"></i>
  </a>
</div>

<header>
  Cambiar Contraseña
</header>

<div class="container">
  <h1>Actualizar Credenciales</h1>
  
  <form method="post">
    <label for="contrasena_actual">Contraseña actual:</label>
    <input type="password" name="contrasena_actual" id="contrasena_actual" required>

    <label for="nueva_contrasena">Nueva contraseña:</label>
    <input type="password" name="nueva_contrasena" id="nueva_contrasena" required>

    <button type="submit">Actualizar Contraseña</button>
  </form>

  <?php if ($mensaje): ?>
    <div class="mensaje <?= strpos($mensaje, '✅') !== false ? 'mensaje-success' : 'mensaje-error' ?>">
      <?= htmlspecialchars($mensaje) ?>
    </div>
  <?php endif; ?>

  <div class="volver">
    <a href="perfil.php">
      <i class="fas fa-arrow-left"></i> Volver al perfil
    </a>
  </div>
</div>

</body>
</html>