<?php
include_once("MysqlConnector.php");
$db = new MysqlConnector();
$conn = $db->connect();

// Consulta principal de ventas con información del cliente
$sqlVentas = "
    SELECT 
        v.folio,
        v.fecha,
        DATE_FORMAT(v.fecha, '%Y-%m') AS mes,
        v.total AS total_venta,
        c.nombre AS cliente
    FROM ventas v
    JOIN clientes c ON c.idCliente = v.idCliente
    ORDER BY v.fecha ASC
";
$resultVentas = $conn->query($sqlVentas);

// Consulta de detalles
$sqlDetalles = "
    SELECT 
        dv.folio,
        a.descripcion,
        dv.cantidad,
        dv.precio_unitario,
        (dv.cantidad * dv.precio_unitario) AS total_producto
    FROM detalles_ventas dv
    JOIN articulos a ON a.idArticulo = dv.idArticulo
    JOIN ventas v ON v.folio = dv.folio
    ORDER BY dv.folio
";
$resultDetalles = $conn->query($sqlDetalles);

// Agrupar detalles por folio
$detallesPorFolio = [];
while ($detalle = $resultDetalles->fetch_assoc()) {
    $detallesPorFolio[$detalle['folio']][] = $detalle;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ventas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #ffffff;
            color: #444;
            padding: 20px;
            position: relative;
        }
        h2 {
            text-align: center;
            color: white;
            background-color: #b89b5e;
            padding: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 85%;
            margin: auto;
            border-collapse: collapse;
            box-shadow: 0 0 10px #ccc;
            margin-bottom: 40px;
        }
        th, td {
            border: 1px solid #B8860B;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #b89b5e; /* Cambiado a dorado */
            color: #000;
        }
        tr:nth-child(even) {
            background-color: #FFF8DC;
        }
        .home-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            color: #C0C0C0;
        }
        .home-icon:hover {
            color: #C0C0C0;
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-icon">
        <i class="fas fa-home"></i>
    </a>


    
    <h2>Ventas</h2>

    <table>
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Mes</th>
                <th>Total Venta</th>
                <th>Total Acumulado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $granTotal = 0;
            while ($row = $resultVentas->fetch_assoc()):
                $totalFolio = 0;
                $granTotal += $row['total_venta'];
            ?>
                <tr>
                    <td><?= $row['folio'] ?></td>
                    <td><?= date('d-m-Y', strtotime($row['fecha'])) ?></td>
                    <td><?= htmlspecialchars($row['cliente']) ?></td>
                    <td><?= date('F Y', strtotime($row['mes'] . '-01')) ?></td>
                    <td>$<?= number_format($row['total_venta'], 2) ?></td>
                    <td>$<?= number_format($granTotal, 2) ?></td>
                </tr>

                <?php if (!empty($detallesPorFolio[$row['folio']])): ?>
                    <tr>
                        <td colspan="6">
                            <table style="width:100%; background:white;">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detallesPorFolio[$row['folio']] as $detalle): ?>
                                        <tr>
                                            <td><?= $detalle['descripcion'] ?></td>
                                            <td><?= $detalle['cantidad'] ?></td>
                                            <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                            <td>$<?= number_format($detalle['total_producto'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endwhile; ?>

            <!-- Total general final -->
            <tr>
                <th colspan="5">Total General</th>
                <th>$<?= number_format($granTotal, 2) ?></th>
            </tr>
        </tbody>
    </table>
</body>
</html>