<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit;
}

include_once("MysqlConnector.php");
$db = new MysqlConnector();
$conn = $db->connect();

// Verificar si es superadmin
$stmt = $conn->prepare("SELECT tipo_usuario FROM admins WHERE idAdmin = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$current_admin = $result->fetch_assoc();
$is_superadmin = ($current_admin['tipo_usuario'] == 'superadmin');

// Obtener todos los administradores
$query = "SELECT idAdmin, usuario, nombre_completo, correo, telefono, tipo_usuario FROM admins";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Administradores</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/stylo_showadmins.css">
</head>
<body>
    <div class="header">
        <a href="index.php" class="home-icon" title="Volver al inicio">
            <i class="fas fa-home"></i>
        </a>
        <h1>Lista de Administradores</h1>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['idAdmin'] ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($row['correo']) ?></td>
                <td><?= htmlspecialchars($row['telefono']) ?></td>
                <td><?= $row['tipo_usuario'] ?></td>
                <td>
                    <?php if ($is_superadmin || $row['idAdmin'] == $_SESSION['admin_id']): ?>
                        <a href="ModAdmin.php?id=<?= $row['idAdmin'] ?>">Editar</a>
                    <?php else: ?>
                        <span class="no-permission">No permitido</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>