<?php
include_once("MysqlConnector.php");
session_start();

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

$db = new MysqlConnector();
$conn = $db->connect();

// Consulta para obtener las ventas con detalles
$sql = "
    SELECT 
        c.nombre AS cliente,
        v.fecha,
        a.descripcion AS articulo,
        od.precio_unitario,
        od.cantidad,
        (od.precio_unitario * od.cantidad) AS total
    FROM ventas v
    JOIN clientes c ON v.idCliente = c.idCliente
    JOIN orden_detalle od ON v.folio = od.idOrden
    JOIN articulos a ON od.idArticulo = a.idArticulo
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas Detalladas</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            background-color: #ffffff;
            color: #333333;
            padding: 20px;
        }

        h1 {
            color: #d4af37;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #000;
            color: #d4af37;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>

<h1>Ventas Detalladas</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Artículo</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($venta = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($venta['cliente']) ?></td>
                    <td><?= htmlspecialchars($venta['fecha']) ?></td>
                    <td><?= htmlspecialchars($venta['articulo']) ?></td>
                    <td>$<?= number_format($venta['precio_unitario'], 2) ?></td>
                    <td><?= $venta['cantidad'] ?></td>
                    <td>$<?= number_format($venta['total'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align:center;">No hay ventas registradas.</p>
<?php endif; ?>

<footer>
    © 2025 Joyería Suárez - Todos los derechos reservados
</footer>

</body>
</html>
