<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginCliente.php");
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$cliente_nombre = $_SESSION['cliente_nombre'];

$db = new MysqlConnector();
$conn = $db->connect();

$query = "SELECT o.*, od.idArticulo, od.cantidad, a.descripcion, od.precio_unitario 
          FROM ordenes o
          JOIN orden_detalle od ON o.idOrden = od.idOrden
          JOIN articulos a ON od.idArticulo = a.idArticulo
          WHERE o.idCliente = ? AND (o.estado = 'aceptada' OR o.estado = 'pendiente')
          ORDER BY o.fecha DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

$ordenes = [];
while ($row = $result->fetch_assoc()) {
    $idOrden = $row['idOrden'];
    if (!isset($ordenes[$idOrden])) {
        $ordenes[$idOrden] = [
            'fecha' => $row['fecha'],
            'estado' => $row['estado'],
            'detalles' => []
        ];
    }
    $ordenes[$idOrden]['detalles'][] = [
        'descripcion' => $row['descripcion'],
        'cantidad' => $row['cantidad'],
        'precio' => $row['precio_unitario']
    ];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Compras - Joyería Suárez</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="css/stylo_perfil.css">
</head>
<body>

<header>
  <div class="header-content">
    <h1>Bienvenido, <?= htmlspecialchars($cliente_nombre) ?></h1>
    <a href="productos.php" class="productos-btn"><i class="fas fa-box"></i> Productos</a>
  </div>
</header>

<div class="container">
  <h2>Mis Compras</h2>

  <!-- Menú desplegable -->
  <div class="profile-menu">
    <button onclick="toggleMenu()" class="btn">Opciones de Perfil ▼</button>
    <div id="menuOpciones" class="dropdown-menu">
      <a href="editar_cliente.php">Editar mi perfil</a>
      <a href="cambiar_contraseña.php">Cambiar mi contraseña</a>
      <a href="ver_compras.php">Ver mis compras</a>
    </div>
  </div>

  <?php if (empty($ordenes)): ?>
    <p class="no-orders">No tienes compras registradas aún.</p>
  <?php else: ?>
    <?php foreach ($ordenes as $idOrden => $orden): ?>
      <div class="orden">
        <div class="order-header">
          <p><strong>Orden #<?= $idOrden ?></strong></p>
          <p>Fecha: <?= $orden['fecha'] ?></p>
          <p class="estado <?= $orden['estado'] === 'pendiente' ? 'pendiente' : 'aprobada' ?>">
            Estado: <?= ucfirst($orden['estado']) ?>
          </p>
        </div>

        <div class="detalles" id="ticket-<?= $idOrden ?>">
          <h4>Detalles:</h4>
          <?php foreach ($orden['detalles'] as $d): ?>
            <p><?= htmlspecialchars($d['descripcion']) ?> — <?= $d['cantidad'] ?> x $<?= number_format($d['precio'], 2) ?></p>
          <?php endforeach; ?>
          <p class="order-total"><strong>Total: $
            <?= number_format(array_sum(array_map(function($d) {
              return $d['cantidad'] * $d['precio'];
            }, $orden['detalles'])), 2) ?>
          </strong></p>
        </div>

        <button onclick="imprimirTicket(<?= $idOrden ?>)" class="btn-imprimir">
          <i class="fas fa-print"></i> Imprimir Ticket
        </button>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Joyería Suárez</p>
</footer>

<script>
  function imprimirTicket(id) {
    var contenido = document.getElementById('ticket-' + id).innerHTML;
    var ventana = window.open('', '_blank', 'width=800,height=600');
    ventana.document.write('<html><head><title>Ticket de Compra</title></head><body style="font-family: Times New Roman; color: #000;">');
    ventana.document.write(contenido);
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.print();
  }

  function toggleMenu() {
    var menu = document.getElementById("menuOpciones");
    menu.style.display = (menu.style.display === "none" || menu.style.display === "") ? "block" : "none";
  }

  window.onclick = function(event) {
    if (!event.target.matches('.btn')) {
      var menu = document.getElementById("menuOpciones");
      if (menu && menu.style.display === "block") {
        menu.style.display = "none";
      }
    }
  }
</script>

</body>
</html>