<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_GET['idOrden'])) {
    echo "ID de orden no proporcionado.";
    exit();
}

$idOrden = $_GET['idOrden'];
$db = new MysqlConnector();
$conn = $db->connect();

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener la orden
$sqlOrden = "SELECT * FROM ordenes WHERE idOrden = ?";
$stmtOrden = $conn->prepare($sqlOrden);
if ($stmtOrden === false) {
    die("Error al preparar la consulta de orden: " . $conn->error);
}

$stmtOrden->bind_param("i", $idOrden);
$stmtOrden->execute();
$resultOrden = $stmtOrden->get_result();

if ($resultOrden->num_rows === 0) {
    echo "Orden no encontrada.";
    exit();
}

$orden = $resultOrden->fetch_assoc();
$idCliente = $orden['idCliente'];
$idTienda = $orden['idTienda'];
$fecha = date("Y-m-d");
$total = $orden['total'];

// Insertar en ventas (sin campo cantidad)
$sqlVenta = "INSERT INTO ventas (idCliente, fecha, idTienda, total) VALUES (?, ?, ?, ?)";
$stmtVenta = $conn->prepare($sqlVenta);
if ($stmtVenta === false) {
    die("Error al preparar la consulta de ventas: " . $conn->error);
}

$stmtVenta->bind_param("isid", $idCliente, $fecha, $idTienda, $total);
$stmtVenta->execute();
$folio = $conn->insert_id;

// Obtener detalles de la orden
$sqlDetalles = "SELECT * FROM orden_detalle WHERE idOrden = ?";
$stmtDetalles = $conn->prepare($sqlDetalles);
if ($stmtDetalles === false) {
    die("Error al preparar la consulta de detalles: " . $conn->error);
}

$stmtDetalles->bind_param("i", $idOrden);
$stmtDetalles->execute();
$resultDetalles = $stmtDetalles->get_result();

// Insertar en detalles_ventas
while ($detalle = $resultDetalles->fetch_assoc()) {
    $idArticulo = $detalle['idArticulo'];
    $cantidad = $detalle['cantidad'];
    $precio = $detalle['precio_unitario'];

    $sqlDV = "INSERT INTO detalles_ventas (folio, idArticulo, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmtDV = $conn->prepare($sqlDV);
    if ($stmtDV === false) {
        echo "Error al preparar la consulta de detalles de ventas: " . $conn->error;
        exit();
    }

    $stmtDV->bind_param("iiid", $folio, $idArticulo, $cantidad, $precio);
    if (!$stmtDV->execute()) {
        echo "Error al insertar detalles de ventas: " . $stmtDV->error;
        exit();
    }
}

// Marcar la orden como aceptada
$sqlUpdate = "UPDATE ordenes SET estado = 'aceptada' WHERE idOrden = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
if ($stmtUpdate === false) {
    die("Error al preparar la consulta de actualización de orden: " . $conn->error);
}

$stmtUpdate->bind_param("i", $idOrden);
$stmtUpdate->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ticket de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fafafa;
            padding: 40px;
            color: #333;
        }
        .ticket {
            max-width: 600px;
            margin: auto;
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .ticket h2 {
            color: #5a2a83;
        }
        .ticket table {
            width: 100%;
            border-collapse: collapse;
        }
        .ticket table th, .ticket table td {
            padding: 10px;
            text-align: left;
        }
        .ticket .total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
        }
        .print-btn {
            margin-top: 20px;
            text-align: center;
        }
        .print-btn button {
            background-color: #5a2a83;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .print-btn button:hover {
            background-color: #4a1f6a;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <h2>Ticket de Compra</h2>
        <p><strong>Folio:</strong> <?php echo $folio; ?></p>
        <p><strong>Fecha:</strong> <?php echo $fecha; ?></p>
        <p><strong>Cliente:</strong> <?php echo $idCliente; ?></p>

        <table>
            <tr>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
            <?php
            $sql = "SELECT a.descripcion, dv.cantidad, dv.precio_unitario 
                    FROM detalles_ventas dv
                    JOIN articulos a ON dv.idArticulo = a.idArticulo
                    WHERE dv.folio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $folio);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $sub = $row['cantidad'] * $row['precio_unitario'];
                echo "<tr>
                        <td>{$row['descripcion']}</td>
                        <td>{$row['cantidad']}</td>
                        <td>\${$row['precio_unitario']}</td>
                        <td>\$" . number_format($sub, 2) . "</td>
                    </tr>";
            }
            ?>
        </table>

        <p class="total">Total: $<?php echo number_format($total, 2); ?></p>

        <div class="print-btn">
            <button onclick="window.print()">Imprimir Ticket</button>
        </div>
    </div>
</body>
</html>
