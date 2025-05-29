<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginCliente.php");
    exit;
}

$db   = new MysqlConnector();
$conn = $db->connect();

$clienteNombre = $_SESSION['cliente_nombre'] ?? 'Cliente';
$mensaje = "";

// Eliminar producto si se envía quitar_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quitar_id'])) {
    $quitarId = (int) $_POST['quitar_id'];
    if (isset($_SESSION['carrito'][$quitarId])) {
        unset($_SESSION['carrito'][$quitarId]);
        $mensaje = "Artículo eliminado del carrito.";
    }
}

// Mostrar mensaje si carrito vacío
if (empty($_SESSION['carrito'])) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Carrito Vacío - Joyería Suárez</title>
        <link rel='stylesheet' href='css/stylo_showcarrito.css'>
    </head>
    <body>
        <header>Joyería Suárez</header>
        <div class='container'>
            <h2>Hola, {$clienteNombre}</h2>
            <div class='message'>
                <p>Tu carrito está vacío.</p>
                <a href='productos.php' class='btn'>Ver Productos</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// Obtener productos del carrito
$ids = array_keys($_SESSION['carrito']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT idArticulo, descripcion, precio, imagen FROM articulos WHERE idArticulo IN ($placeholders)");
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$totalGeneral = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tu Carrito - Joyería Suárez</title>
  <link rel="stylesheet" href="css/stylo_showcarrito.css">
</head>
<body>
  <header>Joyería Suárez</header>
  <div class="container">
    <h2>Bienvenido, <?= htmlspecialchars($clienteNombre) ?>!</h2>

    <?php if ($mensaje): ?>
      <div class="mensaje-exito"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <h3>Tu Carrito</h3>
    <table>
      <tr>
        <th>Imagen</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
        <th>Acción</th>
      </tr>
      <?php while ($art = $result->fetch_assoc()):
        $id = $art['idArticulo'];
        $cant = $_SESSION['carrito'][$id]['cantidad'] ?? 0;
        if ($cant <= 0) continue;
        $precio = (float)$art['precio'];
        $sub = $precio * $cant;
        $totalGeneral += $sub;
      ?>
      <tr>
        <td><img src="uploads/<?= htmlspecialchars($art['imagen']) ?>" alt="Producto"></td>
        <td><?= htmlspecialchars($art['descripcion']) ?></td>
        <td>$<?= number_format($precio, 2) ?></td>
        <td><?= $cant ?></td>
        <td>$<?= number_format($sub, 2) ?></td>
        <td>
          <form method="POST">
            <input type="hidden" name="quitar_id" value="<?= $id ?>">
            <button type="submit" class="btn btn-remove">Quitar</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>

    <p class="total"><strong>Total:</strong> $<?= number_format($totalGeneral, 2) ?></p>

    <div class="acciones">
      <a href="productos.php" class="btn">Seguir Comprando</a>
      <a href="guardar_compra.php" class="btn">Finalizar Compra</a>
    </div>
  </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>