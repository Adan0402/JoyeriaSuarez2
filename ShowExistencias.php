<?php
include_once("MysqlConnector.php");
$db = new MysqlConnector();
$conn = $db->connect();

// Si se envió un formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idExistencia'], $_POST['cantidad'])) {
    $idExistencia = (int)$_POST['idExistencia'];
    $cantidad = (int)$_POST['cantidad'];

    $update = $conn->prepare("UPDATE existencias SET cantidad = ? WHERE idExistencia = ?");
    $update->bind_param("ii", $cantidad, $idExistencia);
    if ($update->execute()) {
        echo "<p style='color: green;'>Existencia actualizada correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al actualizar existencia: " . $conn->error . "</p>";
    }
}

// Obtener existencias
$sql = "SELECT e.idExistencia, e.cantidad, t.descripcion AS tienda, a.descripcion AS articulo, a.imagen AS imagen_articulo
        FROM existencias e
        JOIN tiendas t ON e.idTienda = t.idTienda
        JOIN articulos a ON e.idArticulo = a.idArticulo
        ORDER BY t.descripcion, a.descripcion";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Existencias por Tienda</title>
  <style>
    body { font-family: Arial; padding: 20px; background-color: #fff; }
    h1 { text-align: center; color: #b8860b; font-size: 2em; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    th { background-color: #fff8dc; color: #b8860b; }
    td img { max-width: 100px; height: auto; margin: 5px 0; }
    form { margin: 0; }
    input[type="number"] { width: 60px; }
    input[type="submit"] { padding: 5px 10px; background-color: #b8860b; color: white; border: none; cursor: pointer; }
    input[type="submit"]:hover { background-color: #c2a800; }
    .btn-regresar { 
        display: inline-block;
        background-color: #b8860b;
        color: white;
        padding: 10px;
        text-decoration: none;
        font-size: 16px;
        border-radius: 5px;
        margin-top: 20px;
        text-align: center;
        width: 200px;
        margin: 0 auto;
    }
    .btn-regresar:hover {
        background-color: #c2a800;
    }
  </style>
</head>
<body>

<h1>Existencias por Tienda</h1>

<!-- Icono para regresar a index.php -->
<a href="index.php" class="btn-regresar">
    <i class="fas fa-home"></i> Regresar al Inicio
</a>

<table>
    <tr>
        <th>Tienda</th>
        <th>Artículo</th>
        <th>Imagen</th>
        <th>Cantidad</th>
        <th>Acción</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['tienda']) ?></td>
        <td><?= htmlspecialchars($row['articulo']) ?></td>
        <!-- Mostrar la imagen del artículo desde la carpeta uploads -->
        <td>
            <?php if (!empty($row['imagen_articulo'])): ?>
                <!-- Ruta relativa a la carpeta uploads -->
                <img src="uploads/<?= htmlspecialchars($row['imagen_articulo']) ?>" alt="<?= htmlspecialchars($row['articulo']) ?>">
            <?php else: ?>
                <span>No disponible</span>
            <?php endif; ?>
        </td>
        <td>
            <form method="POST">
                <input type="hidden" name="idExistencia" value="<?= $row['idExistencia'] ?>">
                <input type="number" name="cantidad" value="<?= $row['cantidad'] ?>" min="0">
        </td>
        <td>
            <input type="submit" value="Actualizar">
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
