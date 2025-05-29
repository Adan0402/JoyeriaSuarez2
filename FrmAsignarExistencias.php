<?php
include_once("MysqlConnector.php");

if (!isset($_GET['idArticulo']) || !is_numeric($_GET['idArticulo'])) {
    die("Artículo no especificado.");
}

$idArticulo = (int)$_GET['idArticulo'];

$db = new MysqlConnector();
$conn = $db->connect();

// Obtener nombre del artículo
$stmt = $conn->prepare("SELECT descripcion FROM articulos WHERE idArticulo = ?");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$stmt->bind_result($descripcionArticulo);
if (!$stmt->fetch()) {
    die("Artículo no encontrado.");
}
$stmt->close();

// Obtener todas las tiendas
$tiendas = $conn->query("SELECT idTienda, descripcion FROM tiendas");

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($_POST['existencias'] as $idTienda => $cantidad) {
        $idTienda = (int)$idTienda;
        $cantidad = (int)$cantidad;

        if ($cantidad >= 0) {
            // Verificar si ya existe una fila
            $check = $conn->prepare("SELECT idExistencia FROM existencias WHERE idArticulo = ? AND idTienda = ?");
            $check->bind_param("ii", $idArticulo, $idTienda);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                // Actualizar
                $update = $conn->prepare("UPDATE existencias SET cantidad = ? WHERE idArticulo = ? AND idTienda = ?");
                $update->bind_param("iii", $cantidad, $idArticulo, $idTienda);
                $update->execute();
                $update->close();
            } else {
                // Insertar
                $insert = $conn->prepare("INSERT INTO existencias (idArticulo, idTienda, cantidad) VALUES (?, ?, ?)");
                $insert->bind_param("iii", $idArticulo, $idTienda, $cantidad);
                $insert->execute();
                $insert->close();
            }
            $check->close();
        }
    }

    echo "<p style='color:green;'>Existencias actualizadas correctamente.</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Existencias</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: auto; }
        h1 { color: #b89b5e; }
        form { display: flex; flex-direction: column; gap: 15px; }
        label { font-weight: bold; }
        input[type=number] { width: 100%; padding: 8px; }
        button {
            background-color: #b89b5e;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        button:hover { background-color: #a88c4e; }
        .tienda { border: 1px solid #ccc; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Asignar existencias para: <?= htmlspecialchars($descripcionArticulo) ?></h1>
    <form method="POST">
        <?php while ($t = $tiendas->fetch_assoc()): ?>
            <div class="tienda">
                <label><?= htmlspecialchars($t['descripcion']) ?>:</label>
                <input type="number" name="existencias[<?= $t['idTienda'] ?>]" min="0" value="0">
            </div>
        <?php endwhile; ?>
        <button type="submit">Guardar existencias</button>
    </form>
</body>
</html>
