<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: loginAdmin.php");
    exit;
}

$db   = new MysqlConnector();
$conn = $db->connect();

// Obtener todos los artículos de la base de datos
$stmt = $conn->prepare("SELECT * FROM articulos");
$stmt->execute();
$result = $stmt->get_result();

// Cambiar estado del artículo si se recibe la solicitud por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_id'])) {
    $toggle_id = $_POST['toggle_id'];
    
    // Primero obtenemos el estado actual
    $checkStmt = $conn->prepare("SELECT activo FROM articulos WHERE idArticulo = ?");
    $checkStmt->bind_param("i", $toggle_id);
    $checkStmt->execute();
    $checkStmt->bind_result($activo);
    $checkStmt->fetch();
    $checkStmt->close();
    
    // Cambiamos al estado opuesto
    $newStatus = $activo ? 0 : 1;
    
    $updateStmt = $conn->prepare("UPDATE articulos SET activo = ? WHERE idArticulo = ?");
    $updateStmt->bind_param("ii", $newStatus, $toggle_id);
    if ($updateStmt->execute()) {
        $statusMsg = $newStatus ? 'habilitado' : 'deshabilitado';
        echo "<script>alert('Artículo $statusMsg correctamente.'); window.location.href = 'ver_inventario.php';</script>";
    } else {
        echo "<script>alert('Error al cambiar el estado del artículo.');</script>";
    }
    $updateStmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Inventario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #333;
            padding: 20px;
            position: relative;
        }
        h1 {
            text-align: center;
            color: #b89b5e;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
            color: #b89b5e;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr.inactive {
            background-color: #ffebee;
            opacity: 0.7;
        }
        .edit-btn, .toggle-btn {
            background-color: #b89b5e;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }
        .edit-btn:hover, .toggle-btn:hover {
            background-color: #a88c4e;
        }
        .toggle-btn.inactive {
            background-color: #4CAF50; /* Verde para habilitar */
        }
        .toggle-btn.active {
            background-color: #f44336; /* Rojo para deshabilitar */
        }
        img {
            max-width: 80px;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: transparent;
            border: none;
            color: #fff;
            cursor: pointer;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .status-active {
            background-color: #4CAF50;
        }
        .status-inactive {
            background-color: #f44336;
        }
        .home-icon {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            color: #C0C0C0;
        }
        .home-icon:hover {
            color: #DAA520;
        }
    </style>
</head>
<body>

<a href="index.php" class="home-icon" title="Ir al inicio">
    <i class="fas fa-home"></i>
</a>

<h1>Inventario de Artículos</h1>

<table>
    <thead>
        <tr>
            <th>Descripción</th>
            <th>Características</th>
            <th>Precio</th>
            <th>Estado</th>
            <th>Imagen</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($articulo = $result->fetch_assoc()): ?>
        <tr class="<?= $articulo['activo'] ? '' : 'inactive' ?>">
            <td><?= htmlspecialchars($articulo['descripcion']) ?></td>
            <td><?= htmlspecialchars($articulo['caracteristicas']) ?></td>
            <td><?= number_format($articulo['precio'], 2, ',', '.') ?> $</td>
            <td>
                <span class="status-badge <?= $articulo['activo'] ? 'status-active' : 'status-inactive' ?>">
                    <?= $articulo['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
            </td>
            <td>
                <?php if ($articulo['imagen']): ?>
                    <img src="uploads/<?= htmlspecialchars($articulo['imagen']) ?>" alt="Imagen de artículo">
                <?php else: ?>
                    <span>No disponible</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="ShowArticulos.php?idArticulo=<?= $articulo['idArticulo'] ?>" class="edit-btn">Editar</a>

                <!-- Botón para cambiar estado -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="toggle_id" value="<?= $articulo['idArticulo'] ?>">
                    <button type="submit" class="toggle-btn <?= $articulo['activo'] ? 'active' : 'inactive' ?>" 
                            onclick="return confirm('¿Estás seguro de <?= $articulo['activo'] ? 'deshabilitar' : 'habilitar' ?> este artículo?')">
                        <?= $articulo['activo'] ? 'Deshabilitar' : 'Habilitar' ?>
                    </button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>