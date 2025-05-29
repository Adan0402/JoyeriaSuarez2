<?php
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idArticulo = $_POST['idArticulo'];
    $idTienda = $_POST['idTienda'];
    $cantidad = $_POST['cantidad'];

    $db = new MysqlConnector();
    $conn = $db->connect();

    $sql = "INSERT INTO existencias (idArticulo, idTienda, cantidad) 
            VALUES ('$idArticulo', '$idTienda', '$cantidad')";

    if ($conn->query($sql) === TRUE) {
        echo "Existencias agregadas exitosamente.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $db->close();
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Existencias</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h1>Agregar Existencias</h1>
    <form method="POST" action="FrmAddExistencia.php">
        Artículo ID: <input type="number" name="idArticulo" required><br>
        Tienda ID: <input type="number" name="idTienda" required><br>
        Cantidad: <input type="number" name="cantidad" required><br>
        <input type="submit" value="Agregar Existencias">
    </form>
</body>
</html>
<?php
}
?>
