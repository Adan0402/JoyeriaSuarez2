<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginCliente.php");
    exit;
}

include_once("MysqlConnector.php");
$db   = new MysqlConnector();
$conn = $db->connect();

// 1) Cargamos tiendas y categorías
$tiendas = $conn->query("SELECT * FROM tiendas ORDER BY descripcion");
$lineas  = $conn->query("SELECT * FROM linea_articulos ORDER BY descripcion");

// 2) Leemos filtros desde GET
$idTienda = isset($_GET['idTienda']) && is_numeric($_GET['idTienda']) ? (int)$_GET['idTienda'] : 0;
$idLinea  = isset($_GET['idLinea']) && is_numeric($_GET['idLinea']) ? (int)$_GET['idLinea'] : 0;

// 3) Preparamos la consulta (solo artículos activos)
if ($idTienda > 0) {
    $sql = "
      SELECT a.*, e.cantidad
      FROM articulos a
      JOIN existencias e ON a.idArticulo = e.idArticulo
      WHERE e.idTienda = ? AND e.cantidad > 0 AND a.activo = 1
    ";
    if ($idLinea > 0) {
        $sql .= " AND a.idLinea = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idTienda, $idLinea);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idTienda);
    }
    $stmt->execute();
    $productos = $stmt->get_result();
} else {
    $productos = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Catálogo de Productos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/stylo_productos.css">
</head>
<body>
<header>
  <div class="top-links">
    <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
    <a href="carrito.php"><i class="fas fa-shopping-cart"></i> Carrito</a>
  </div>
  <a href="logoutcliente.php" class="logout-link" title="Cerrar sesión">
    <i class="fas fa-sign-out-alt"></i> Salir
  </a>
  <h1>Catálogo de Productos</h1>
  <?php if (isset($_GET['bienvenida']) && isset($_SESSION['nombre'])): ?>
    <p class="welcome-message">
      ¡Bienvenido/a <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>!
    </p>
  <?php endif; ?>
  <nav>
    <!-- Selector de tienda -->
    <form method="GET" action="productos.php" id="formTienda">
      <select name="idTienda" onchange="document.getElementById('formTienda').submit()">
        <option value="">— Selecciona tu tienda —</option>
        <?php while($t = $tiendas->fetch_assoc()): ?>
          <option value="<?= $t['idTienda'] ?>" <?= $t['idTienda']==$idTienda?'selected':''?>>
            <?= htmlspecialchars($t['descripcion']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <?php if($idLinea>0): ?>
        <input type="hidden" name="idLinea" value="<?= $idLinea ?>">
      <?php endif; ?>
    </form>

    <!-- Enlaces de categoría -->
    <div class="categorias">
      <a href="productos.php?idTienda=<?= $idTienda ?>" class="<?= $idLinea===0?'active':'' ?>">Todas</a>
      <?php while($l=$lineas->fetch_assoc()): ?>
        <a href="productos.php?idTienda=<?= $idTienda ?>&idLinea=<?= $l['idLinea'] ?>"
           class="<?= $l['idLinea']===$idLinea?'active':'' ?>">
          <?= htmlspecialchars($l['descripcion']) ?>
        </a>
      <?php endwhile; ?>
    </div>
  </nav>
</header>

<div class="container">
  <?php if ($idTienda===0): ?>
    <p class="info-message">Por favor selecciona una tienda para ver sus productos.</p>
  <?php elseif ($productos && $productos->num_rows): ?>
    <div class="grid">
      <?php while($p=$productos->fetch_assoc()): ?>
        <div class="card">
          <img src="uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['descripcion']) ?>" class="product-image">
          <div class="card-body">
            <h3><?= htmlspecialchars($p['descripcion']) ?></h3>
            <p class="caracteristicas"><?= htmlspecialchars($p['caracteristicas']) ?></p>
            <p class="price">$<?= number_format($p['precio'],2) ?></p>
            <p class="stock">Disponibles: <?= $p['cantidad'] ?></p>
            <form method="POST" action="agregar_al_carrito.php" class="add-to-cart-form">
              <input type="hidden" name="idArticulo" value="<?= $p['idArticulo'] ?>">
              <input type="hidden" name="idTienda"    value="<?= $idTienda ?>">
              <input type="number" name="cantidad" value="1" min="1" max="<?= $p['cantidad'] ?>" required class="quantity-input">
              <input type="submit" value="Agregar" class="add-button">
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="info-message">No hay productos disponibles en esta tienda/categoría.</p>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Juárez Joyería</p>
</footer>

<?php
if (isset($stmt) && $stmt) $stmt->close();
$conn->close();
?>