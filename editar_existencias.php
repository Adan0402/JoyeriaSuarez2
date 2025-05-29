<?php
session_start();
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idArticulo = $_POST['idArticulo'];
    $idTienda = $_POST['idTienda'];
    $cantidad = $_POST['cantidad'];
    $precio = $_POST['precio'];

    $db   = new MysqlConnector();
    $conn = $db->connect();

    $sql = "UPDATE existencias SET cantidad = ? WHERE idArticulo = ? AND idTienda = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $cantidad, $idArticulo, $idTienda);
    $stmt->execute();

    // Actualizar el precio en la tabla productos
    $sqlPrecio = "UPDATE articulos SET precio = ? WHERE idArticulo = ?";
    $stmtPrecio = $conn->prepare($sqlPrecio);
    $stmtPrecio->bind_param("di", $precio, $idArticulo);
    $stmtPrecio->execute();

    $db->close();

    header("Location: existencias.php");
    exit;
}

// Obtener los datos del producto
if (isset($_GET['id']) && isset($_GET['tienda'])) {
    $idArticulo = $_GET['id'];
    $idTienda = $_GET['tienda'];

    $db   = new MysqlConnector();
    $conn = $db->connect();

    $sql = "SELECT a.descripcion, a.precio, e.cantidad 
            FROM articulos a 
            JOIN existencias e ON a.idArticulo = e.idArticulo
            WHERE a.idArticulo = ? AND e.idTienda = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idArticulo, $idTienda);
    $stmt->execute();
    $result = $stmt->get_result();

    $product = $result->fetch_assoc();

    $db->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Existencia</title>
</head>
<body>

<h1>Editar Existencia</h1>

<form method="POST" action="editar_existencia.php">
    <input type="hidden" name="idArticulo" value="<?= $idArticulo ?>">
    <input type="hidden" name="idTienda" value="<?= $idTienda ?>">
    
    <label for="cantidad">Cantidad:</label>
    <input type="number" name="cantidad" value="<?= $product['cantidad'] ?>" required><br><br>

    <label for="precio">Precio:</label>
    <input type="text" name="precio" value="<?= $product['precio'] ?>" required><br><br>

    <input type="submit" value="Actualizar Existencia">
</form>

<a href="existencias.php">Volver</a>

</body>
</html>
