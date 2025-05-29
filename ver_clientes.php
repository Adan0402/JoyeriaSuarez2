<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: loginAdmin.php");
    exit;
}

$db = new MysqlConnector();
$conn = $db->connect();

// Obtener todos los clientes
$stmt = $conn->prepare("SELECT * FROM clientes");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes Registrados</title>
    <link rel="stylesheet" href="css/stylo_showclientes.css">
</head>
<body>

<div class="header">
    <a href="index.php" class="home-button">🏠 Volver al menú</a>
    <h1>Clientes Registrados</h1>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Código Postal</th>
                <th>Ciudad</th>
                <th>Colonia</th>
                <th>Estado</th>
                <th>País</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cliente = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                <td><?= htmlspecialchars($cliente['apellidos']) ?></td>
                <td><?= htmlspecialchars($cliente['correoElectronico']) ?></td>
                <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                <td><?= htmlspecialchars($cliente['direccionPostal']) ?></td>
                <td><?= htmlspecialchars($cliente['ciudad']) ?></td>
                <td><?= htmlspecialchars($cliente['colonia']) ?></td>
                <td><?= htmlspecialchars($cliente['estado']) ?></td>
                <td><?= htmlspecialchars($cliente['pais']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>