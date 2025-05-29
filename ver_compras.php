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

// Obtener la dirección de la tienda
$direccion_tienda = "Dirección no disponible";
$queryTienda = "SELECT direccion FROM tiendas LIMIT 1";
$resultTienda = $conn->query($queryTienda);
if ($resultTienda && $rowTienda = $resultTienda->fetch_assoc()) {
    $direccion_tienda = $rowTienda['direccion'];
}
$direccion_tienda_escaped = htmlspecialchars($direccion_tienda, ENT_QUOTES);

// Consulta de órdenes y detalles
$query = "SELECT o.idOrden, o.fecha, o.estado, od.idArticulo, od.cantidad, od.precio_unitario, a.descripcion 
          FROM ordenes o
          LEFT JOIN orden_detalle od ON o.idOrden = od.idOrden
          LEFT JOIN articulos a ON od.idArticulo = a.idArticulo
          WHERE o.idCliente = ?
          ORDER BY o.fecha DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conn->error);
}
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
    if ($row['descripcion']) {
        $ordenes[$idOrden]['detalles'][] = [
            'descripcion' => $row['descripcion'],
            'cantidad' => $row['cantidad'],
            'precio' => $row['precio_unitario']
        ];
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Compras - Suárez Joyería</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: #f8f5f0; /* Fondo beige claro */
      margin: 0;
      padding: 0;
      position: relative;
    }
    
    /* Icono de casita */
    .home-icon {
      position: fixed;
      top: 25px;
      left: 25px;
      z-index: 100;
    }
    
    .home-icon a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: #b89b5e; /* Dorado */
      color: white;
      border-radius: 50%;
      text-decoration: none;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }
    
    .home-icon a:hover {
      background: #a88c4e; /* Dorado oscuro */
      transform: scale(1.1);
    }
    
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 60px 30px 30px;
    }
    
    h2 {
      color: #b89b5e; /* Dorado */
      text-align: center;
      margin-bottom: 30px;
      font-size: 28px;
      position: relative;
      padding-bottom: 15px;
    }
    
    h2:after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 2px;
      background: #b89b5e;
    }
    
    .orden {
      background: white;
      padding: 25px;
      margin-bottom: 25px;
      border-radius: 10px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.05);
      border-left: 4px solid #b89b5e;
      transition: transform 0.3s ease;
    }
    
    .orden:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .orden-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    
    .orden-id {
      font-weight: bold;
      color: #333;
      font-size: 18px;
    }
    
    .orden-fecha {
      color: #777;
      font-size: 14px;
    }
    
    .estado {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .estado.aprobada {
      background: #e8f5e9;
      color: #2e7d32;
    }
    
    .estado.pendiente {
      background: #fff8e1;
      color: #ff8f00;
    }
    
    .estado.rechazada {
      background: #ffebee;
      color: #c62828;
    }
    
    .detalles {
      margin-top: 15px;
    }
    
    .detalle-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px dashed #eee;
    }
    
    .detalle-item:last-child {
      border-bottom: none;
    }
    
    .detalle-nombre {
      flex: 2;
    }
    
    .detalle-cantidad {
      flex: 1;
      text-align: center;
    }
    
    .detalle-precio {
      flex: 1;
      text-align: right;
    }
    
    .total {
      text-align: right;
      font-weight: bold;
      margin-top: 15px;
      font-size: 18px;
      color: #b89b5e;
    }
    
    .btn-imprimir {
      background: #b89b5e;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 15px;
      font-weight: bold;
      transition: background 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn-imprimir:hover {
      background: #a88c4e;
    }
    
    .sin-compras {
      text-align: center;
      color: #777;
      font-size: 18px;
      margin-top: 50px;
    }
  </style>
  <script>
  function imprimirTicket(id) {
    const contenido = document.getElementById('ticket-' + id).innerHTML;
    const clienteNombre = "<?= htmlspecialchars($cliente_nombre) ?>";
    const direccionTienda = "<?= $direccion_tienda_escaped ?>";

    const w = window.open('', '', 'width=800,height=700');
    w.document.write('<html><head><title>Ticket de Compra</title>');
    w.document.write('<style>');
    w.document.write(`
      body {
        font-family: "Segoe UI", sans-serif;
        background-color: #fff8e1;
        color: #333;
        text-align: center;
      }
      .ticket {
        width: 80%;
        margin: 0 auto;
        padding: 20px;
        border: 3px solid #d4af37; /* Dorado */
        border-radius: 15px;
        background-color: #fff;
        box-shadow: 0 0 15px rgba(192,192,192,0.5); /* plateado suave */
      }
      h2, h3 {
        color: #d4af37;
      }
      .subtext {
        color: #999;
        font-size: 14px;
      }
      .linea {
        border-top: 1px dashed #ccc;
        margin: 10px 0;
      }
    `);
    w.document.write('</style></head><body>');
    w.document.write('<div class="ticket">');
    w.document.write('<h2>✨ Suárez Joyería ✨</h2>');
    w.document.write('<p class="subtext">Elegancia y Prestigio desde 1980</p>');
    w.document.write('<p><strong>Dirección:</strong> ' + direccionTienda + '</p>');
    w.document.write('<div class="linea"></div>');
    w.document.write('<p><strong>Cliente:</strong> ' + clienteNombre + '</p>');
    w.document.write('<p><strong>Fecha:</strong> ' + new Date().toLocaleString() + '</p>');
    w.document.write('<div class="linea"></div>');
    w.document.write('<h3>Detalles de la Compra</h3>');
    w.document.write(contenido);
    w.document.write('<div class="linea"></div>');
    w.document.write('<p class="subtext">Gracias por tu compra. ¡Vuelve pronto!</p>');
    w.document.write('</div>');
    w.document.write('</body></html>');
    w.document.close();
    w.print();
  }
  </script>
</head>
<body>

<div class="home-icon">
  <a href="perfil.php" title="Volver al perfil">
    <i class="fas fa-home"></i>
  </a>
</div>

<div class="container">
  <h2>Mis Compras</h2>

  <?php if (empty($ordenes)): ?>
    <p class="sin-compras">No tienes compras registradas.</p>
  <?php else: ?>
    <?php foreach ($ordenes as $idOrden => $orden): ?>
      <div class="orden">
        <div class="orden-header">
          <div>
            <span class="orden-id">Orden #<?= $idOrden ?></span>
            <span class="orden-fecha"><?= $orden['fecha'] ?></span>
          </div>
          <span class="estado <?= $orden['estado'] ?>"><?= ucfirst($orden['estado']) ?></span>
        </div>

        <div id="ticket-<?= $idOrden ?>" class="detalles">
          <h4>Detalles:</h4>
          <?php if (!empty($orden['detalles'])): ?>
            <?php foreach ($orden['detalles'] as $d): ?>
              <div class="detalle-item">
                <span class="detalle-nombre"><?= htmlspecialchars($d['descripcion']) ?></span>
                <span class="detalle-cantidad"><?= $d['cantidad'] ?> x</span>
                <span class="detalle-precio">$<?= number_format($d['precio'], 2) ?></span>
              </div>
            <?php endforeach; ?>
            <div class="total">
              Total: $<?= number_format(array_sum(array_map(function($d) { return $d['cantidad'] * $d['precio']; }, $orden['detalles'])), 2) ?>
            </div>
          <?php else: ?>
            <p><em>Sin detalles registrados.</em></p>
          <?php endif; ?>
        </div>

        <?php if ($orden['estado'] === 'aceptada' && !empty($orden['detalles'])): ?>
          <button class="btn-imprimir" onclick="imprimirTicket(<?= $idOrden ?>)">
            <i class="fas fa-print"></i> Imprimir Ticket
          </button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>