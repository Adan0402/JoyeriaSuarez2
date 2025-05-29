<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: loginAdmin.php");
    exit;
}

$db   = new MysqlConnector();
$conn = $db->connect();

// Verificar si se ha enviado un ID de artículo
if (isset($_GET['idArticulo'])) {
    $idArticulo = $_GET['idArticulo'];

    // Obtener los detalles del artículo
    $stmt = $conn->prepare("SELECT * FROM articulos WHERE idArticulo = ?");
    $stmt->bind_param("i", $idArticulo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "Artículo no encontrado.";
        exit;
    }

    $articulo = $result->fetch_assoc();

    // Si el formulario es enviado, actualizar los datos del artículo
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $descripcion = $_POST['descripcion'];
        $caracteristicas = $_POST['caracteristicas'];
        $precio = $_POST['precio'];
        $imagen = $_FILES['imagen']['name'];

        // Si se sube una nueva imagen
        if ($imagen) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($imagen);
            move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file);
        } else {
            // Mantener la imagen actual si no se sube una nueva
            $imagen = $articulo['imagen'];  
        }

        // Actualizar la base de datos
        $updateStmt = $conn->prepare("UPDATE articulos SET descripcion = ?, caracteristicas = ?, precio = ?, imagen = ? WHERE idArticulo = ?");
        $updateStmt->bind_param("ssdsi", $descripcion, $caracteristicas, $precio, $imagen, $idArticulo);
        $updateStmt->execute();

        echo "Artículo actualizado correctamente.";
    }
} else {
    echo "No se ha proporcionado un ID de artículo.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        label { margin-top: 10px; display: block; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 8px; margin-top: 5px; }
        input[type="submit"] { background: #b89b5e; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        input[type="submit"]:hover { background: #a88c4e; }
        .image-preview { margin-top: 20px; }
        .image-preview img { max-width: 200px; }
    </style>
</head>
<body>
    <h1>Editar Artículo</h1>
    <form action="ShowArticulos.php?idArticulo=<?= $articulo['idArticulo'] ?>" method="POST" enctype="multipart/form-data">
        <label for="descripcion">Descripción:</label>
        <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($articulo['descripcion']) ?>" required>

        <label for="caracteristicas">Características:</label>
        <textarea id="caracteristicas" name="caracteristicas" rows="4" required><?= htmlspecialchars($articulo['caracteristicas']) ?></textarea>

        <label for="precio">Precio:</label>
        <input type="number" id="precio" name="precio" value="<?= $articulo['precio'] ?>" step="0.01" required>

        <label for="imagen">Imagen:</label>
        <input type="file" id="imagen" name="imagen">
        
        <!-- Mostrar imagen actual -->
        <?php if ($articulo['imagen']): ?>
            <div class="image-preview">
                <p><strong>Imagen actual:</strong></p>
                <img src="uploads/<?= htmlspecialchars($articulo['imagen']) ?>" alt="Imagen actual">
            </div>
        <?php endif; ?>

        <input type="submit" value="Actualizar Artículo">
    </form>
</body>
</html>