<?php
session_start();
include_once("MysqlConnector.php");

$db   = new MysqlConnector();
$conn = $db->connect();

$sql = "SELECT idOrden, fecha, total, idCliente FROM ordenes WHERE estado = 'pendiente'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<p>No hay órdenes pendientes.</p>";
} else {
    echo "<h1>Órdenes Pendientes</h1>";
    echo "<table>
            <tr>
                <th>Fecha</th>
                <th>Total</th>
                <th>Cliente</th>
                <th>Acción</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['fecha']}</td>
                <td>\${$row['total']}</td>
                <td><a href='perfil.php?idCliente={$row['idCliente']}'>Ver Cliente</a></td>
                <td>
                    <a href='aprobar_orden.php?idOrden={$row['idOrden']}'>Aceptar</a> | 
                    <a href='rechazar_orden.php?idOrden={$row['idOrden']}'>Rechazar</a>
                </td>
              </tr>";
    }
    echo "</table>";
}

$conn->close();
?>