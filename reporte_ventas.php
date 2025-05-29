<?php
include_once("MysqlConnector.php");
$db = new MysqlConnector();
$conn = $db->connect();

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-01-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$exportar = $_GET['exportar'] ?? '';

// Exportar a Excel
if ($exportar === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=reporte_mensual_ventas_detalles.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
}

// Consulta principal de ventas
$sqlVentas = "
    SELECT 
        v.folio,
        v.fecha,
        DATE_FORMAT(v.fecha, '%Y-%m') AS mes,
        v.total AS total_venta
    FROM ventas v
    WHERE v.fecha BETWEEN ? AND ?
    ORDER BY v.fecha ASC
";
$stmtVentas = $conn->prepare($sqlVentas);
if (!$stmtVentas) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmtVentas->bind_param("ss", $fechaInicio, $fechaFin);
$stmtVentas->execute();
$resultVentas = $stmtVentas->get_result();

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
    WHERE v.fecha BETWEEN ? AND ?
    ORDER BY dv.folio
";
$stmtDetalles = $conn->prepare($sqlDetalles);
$stmtDetalles->bind_param("ss", $fechaInicio, $fechaFin);
$stmtDetalles->execute();
$resultDetalles = $stmtDetalles->get_result();

// Agrupar detalles por folio
$detallesPorFolio = [];
while ($detalle = $resultDetalles->fetch_assoc()) {
    $detallesPorFolio[$detalle['folio']][] = $detalle;
}
?>

<?php if ($exportar !== 'excel'): ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual de Ventas y Detalles</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #ffffff;
            color: #444;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #B8860B;
            border-bottom: 2px solid #B8860B;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
            text-align: center;
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
            background-color: #FFD700;
            color: #000;
        }
        tr:nth-child(even) {
            background-color: #FFF8DC;
        }
        button {
            background-color: #B8860B;
            color: #fff;
            border: none;
            padding: 8px 15px;
            margin: 0 5px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #DAA520;
        }
        input[type="date"] {
            padding: 5px;
        }
        @media print {
            form, button { display: none; }
            table { width: 100%; }
        }
    </style>
</head>
<body>
    <h2>Reporte de Ventas y Detalles por Mes</h2>
    <form method="GET">
        <label>Fecha inicio: <input type="date" name="fecha_inicio" value="<?= $fechaInicio ?>"></label>
        <label>Fecha fin: <input type="date" name="fecha_fin" value="<?= $fechaFin ?>"></label>
        <button type="submit">Consultar</button>
        <a href="?fecha_inicio=<?= $fechaInicio ?>&fecha_fin=<?= $fechaFin ?>&exportar=excel"><button type="button">Exportar a Excel</button></a>
        <button type="button" onclick="window.print()">Imprimir PDF</button>
    </form>
<?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
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
                    <td><?= date('F Y', strtotime($row['mes'] . '-01')) ?></td>
                    <td>$<?= number_format($row['total_venta'], 2) ?></td>
                    <td>$<?= number_format($granTotal, 2) ?></td>
                </tr>

                <?php if (!empty($detallesPorFolio[$row['folio']])): ?>
                    <tr>
                        <td colspan="5">
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
                <th colspan="4">Total General</th>
                <th>$<?= number_format($granTotal, 2) ?></th>
            </tr>
        </tbody>
    </table>

<?php if ($exportar !== 'excel'): ?>
</body>
</html>
<?php endif; ?>




