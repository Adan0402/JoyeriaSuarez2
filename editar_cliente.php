<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginCliente.php");
    exit();
}

$db = new MysqlConnector();
$conn = $db->connect();

$cliente_id = $_SESSION['cliente_id'];

// Obtener los datos actuales del cliente
$query = "SELECT nombre, apellidos, correoElectronico, direccionPostal, telefono, colonia, ciudad, estado, pais FROM clientes WHERE idCliente = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuevoNombre = $_POST['nombre'];
    $nuevoApellido = $_POST['apellidos'];
    $nuevoCorreo = $_POST['correo'];
    $nuevaDireccion = $_POST['direccion'];
    $nuevoTelefono = $_POST['telefono'];
    $nuevaColonia = $_POST['colonia'];
    $nuevaCiudad = $_POST['ciudad'];
    $nuevoEstado = $_POST['estado'];
    $nuevoPais = $_POST['pais'];

    $conn = $db->connect();
    $query = "UPDATE clientes SET nombre = ?, apellidos = ?, correoElectronico = ?, direccionPostal = ?, colonia = ?, ciudad = ?, estado = ?, pais = ?, telefono = ? WHERE idCliente = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssi", $nuevoNombre, $nuevoApellido, $nuevoCorreo, $nuevaDireccion, $nuevaColonia, $nuevaCiudad, $nuevoEstado, $nuevoPais, $nuevoTelefono, $cliente_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: perfil.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil - Joyería Suárez</title>
  <style>
    body {
      font-family: 'Georgia', serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
      color: #c0c0c0;
    }

    header {
      background-color: #000;
      color: #c0c0c0;
      padding: 20px 0;
      text-align: center;
      font-size: 28px;
      letter-spacing: 2px;
    }

    .container {
      max-width: 600px;
      margin: 50px auto;
      background-color: #fdfdfd;
      padding: 40px;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    h2 {
      color: #bfa046;
      text-align: center;
      margin-bottom: 30px;
      font-weight: normal;
    }

    label {
      display: block;
      margin-top: 20px;
      margin-bottom: 5px;
      color: #444;
      font-weight: bold;
    }

    input[type="text"],
    input[type="email"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      background-color: #fff;
    }

    button {
      background-color: #d4af37;
      color: #000;
      border: none;
      padding: 14px 24px;
      margin-top: 30px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #c19e32;
    }

    .btn-volver {
      display: block;
      margin-top: 20px;
      text-align: center;
      color: #bfa046;
      text-decoration: none;
      font-weight: bold;
    }

    .btn-volver:hover {
      text-decoration: underline;
      color: #a7892c;
    }

    footer {
      background-color: #000;
      color: #d4af37;
      text-align: center;
      padding: 15px 0;
      margin-top: 50px;
      font-size: 14px;
    }
  </style>
</head>
<body>

<header>
  Joyería Suárez - Editar Perfil
</header>

<div class="container">
  <h2>Actualiza tus datos</h2>
  <form method="post">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>

    <label for="apellidos">Apellidos:</label>
    <input type="text" name="apellidos" value="<?= htmlspecialchars($cliente['apellidos']) ?>" required>

    <label for="correo">Correo electrónico:</label>
    <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correoElectronico']) ?>" required>

    <label for="direccion">Dirección:</label>
    <input type="text" name="direccion" value="<?= htmlspecialchars($cliente['direccionPostal']) ?>" required>

    <label for="colonia">Colonia:</label>
    <input type="text" name="colonia" value="<?= htmlspecialchars($cliente['colonia']) ?>" required>

    <label for="ciudad">Ciudad:</label>
    <input type="text" name="ciudad" value="<?= htmlspecialchars($cliente['ciudad']) ?>" required>

    <label for="estado">Estado:</label>
    <input type="text" name="estado" value="<?= htmlspecialchars($cliente['estado']) ?>" required>

    <label for="pais">País:</label>
    <input type="text" name="pais" value="<?= htmlspecialchars($cliente['pais']) ?>" required>

    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>" required>

    <button type="submit">Guardar cambios</button>
  </form>

  <a class="btn-volver" href="perfil.php">← Volver a mi perfil</a>
</div>

<footer>
  &copy; <?= date('Y') ?> Joyería Suárez. Todos los derechos reservados.
</footer>

</body>
</html>
