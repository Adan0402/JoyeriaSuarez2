<?php
session_start();
include_once("MysqlConnector.php");

$db   = new MysqlConnector();
$conn = $db->connect();

// Obtener órdenes pendientes
$sql = "SELECT o.idOrden, o.fecha, o.total, o.estado, c.nombre 
        FROM ordenes o 
        JOIN clientes c ON o.idCliente = c.idCliente 
        WHERE o.estado = 'pendiente'";
$result = $conn->query($sql);

// Procesar acciones si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idOrden'], $_POST['accion'])) {
    $idOrden = $_POST['idOrden'];
    $accion = $_POST['accion'];


    if ($accion === 'aceptar') {
        header("Location: procesar_orden.php?idOrden=" . $idOrden);
        exit();
    } elseif ($accion === 'rechazar') {
        $stmt = $conn->prepare("UPDATE ordenes SET estado = 'rechazada' WHERE idOrden = ?");
        $stmt->bind_param("i", $idOrden);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Órdenes Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdfdfd;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #b89b5e;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        h1 {
            margin-top: 30px;
            text-align: center;
            color: #333;
        }
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #b89b5e;
            color: white;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        form {
            display: inline-block;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-aceptar {
            background-color: #4CAF50;
            color: white;
        }
        .btn-rechazar {
            background-color: #D32F2F;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h2>Panel de Órdenes Pendientes</h2>
    </header>

    <h1>Órdenes por Aprobar</h1>

    <?php if ($result->num_rows === 0): ?>
        <p style="text-align: center;">No hay órdenes pendientes.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['fecha']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td>$<?php echo number_format($row['total'], 2); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="idOrden" value="<?php echo $row['idOrden']; ?>">
                            <input type="hidden" name="accion" value="aceptar">
                            <button type="submit" class="btn btn-aceptar">Aceptar</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="idOrden" value="<?php echo $row['idOrden']; ?>">
                            <input type="hidden" name="accion" value="rechazar">
                            <button type="submit" class="btn btn-rechazar">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
