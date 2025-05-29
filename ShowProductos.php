<?php
include_once("MysqlConnector.php");
$db   = new MysqlConnector();
$conn = $db->connect();

// Cargar tiendas y líneas
$tiendas = $conn->query("SELECT * FROM tiendas ORDER BY descripcion");
$lineas  = $conn->query("SELECT * FROM linea_articulos ORDER BY descripcion");

// Filtros
$idTienda = isset($_GET['idTienda']) && is_numeric($_GET['idTienda']) ? (int)$_GET['idTienda'] : 0;
$idLinea  = isset($_GET['idLinea']) && is_numeric($_GET['idLinea']) ? (int)$_GET['idLinea'] : 0;

// Consulta (mostrar todos los artículos, incluyendo deshabilitados)
if ($idTienda > 0) {
    $sql = "
        SELECT a.*, e.cantidad
        FROM articulos a
        JOIN existencias e ON a.idArticulo = e.idArticulo
        WHERE e.idTienda = ? AND e.cantidad > 0
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
  <title>Catálogo de Productos - Vista Administrador</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/stylo_adminpro.css">
</head>
<body>

<header>
  <div class="home-icon">
    <a href="index.php" title="Volver al inicio">
      <i class="fas fa-home"></i>
    </a>
  </div>
  <h1>Catálogo de Productos (Administrador)</h1>
</header>
<nav>
  <div class="filter-container">
    <form method="GET" id="formTienda">
      <select name="idTienda" onchange="document.getElementById('formTienda').submit()" class="filter-select">
        <option value="">— Selecciona una tienda —</option>
        <?php while($t = $tiendas->fetch_assoc()): ?>
          <option value="<?= $t['idTienda'] ?>" <?= $t['idTienda']==$idTienda?'selected':'' ?>>
            <?= htmlspecialchars($t['descripcion']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <?php if($idLinea > 0): ?>
        <input type="hidden" name="idLinea" value="<?= $idLinea ?>">
      <?php endif; ?>
    </form>

    <div class="category-links">
      <a href="Showproductos.php?idTienda=<?= $idTienda ?>" class="<?= $idLinea===0?'active':'' ?>">Todas</a>
      <?php while($l = $lineas->fetch_assoc()): ?>
        <a href="Showproductos.php?idTienda=<?= $idTienda ?>&idLinea=<?= $l['idLinea'] ?>"
           class="<?= $l['idLinea']===$idLinea?'active':'' ?>">
          <?= htmlspecialchars($l['descripcion']) ?>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
</nav>

<div class="container">
  <?php if ($idTienda === 0): ?>
    <p class="info-message">Seleccione una tienda para visualizar los productos disponibles.</p>
  <?php elseif ($productos && $productos->num_rows): ?>
    <div class="grid">
      <?php while($p = $productos->fetch_assoc()): ?>
        <div class="card <?= $p['activo'] ? '' : 'disabled' ?>">
          <img src="uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['descripcion']) ?>" class="product-image">
          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($p['descripcion']) ?></h3>
            <p class="card-text"><?= htmlspecialchars($p['caracteristicas']) ?></p>
            <p class="card-price">$<?= number_format($p['precio'],2) ?></p>
            <p class="card-stock">Disponibles: <?= $p['cantidad'] ?></p>
            <p class="card-text">Estado: <?= $p['activo'] ? 'Activo' : 'Inactivo' ?></p>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="info-message">No hay productos disponibles en esta tienda o categoría.</p>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Suárez Joyería</p>
</footer>

<?php
if (isset($stmt)) $stmt->close();
$conn->close();
?>