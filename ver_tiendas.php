<?php
session_start();
include_once("MysqlConnector.php");

$db = new MysqlConnector();
$conn = $db->connect();

$result = $conn->query("SELECT * FROM tiendas ORDER BY idTienda DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tiendas Registradas</title>
    <link rel="stylesheet" href="css/stylo_tiendas.css">
</head>
<body>

<div class="home-icon">
    <a href="index.php" title="Inicio">&#8962;</a>
</div>

<h1>Tiendas Registradas</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Descripción</th>
            <th>Ciudad</th>
            <th>Dirección</th>
            <th>Código Postal</th>
            <th>Horario</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($tienda = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $tienda['idTienda'] ?></td>
            <td class="imagen-tienda">
                <?php if ($tienda['imagen'] && file_exists($tienda['imagen'])): ?>
                    <img src="<?= $tienda['imagen'] ?>" alt="<?= htmlspecialchars($tienda['descripcion']) ?>">
                <?php else: ?>
                    <div class="sin-imagen">Sin imagen</div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($tienda['descripcion']) ?></td>
            <td><?= htmlspecialchars($tienda['ciudad']) ?></td>
            <td><?= htmlspecialchars($tienda['direccion']) ?></td>
            <td><?= htmlspecialchars($tienda['codigoPostal']) ?></td>
            <td><?= htmlspecialchars($tienda['horario']) ?></td>
            <td class="acciones">
                <a href="editar_tienda.php?id=<?= $tienda['idTienda'] ?>" class="btn editar">Editar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
<?php $conn->close(); ?>