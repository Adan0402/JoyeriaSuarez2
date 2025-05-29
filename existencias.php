<?php
session_start();
include_once("MysqlConnector.php");

$db   = new MysqlConnector();
$conn = $db->connect();

// Obtener los productos y sus existencias
$sql = "
    SELECT a.idArticulo, a.descripcion, a.precio, a.imagen, e.cantidad, t.descripcion AS tienda 
    FROM articulos a
    JOIN existencias e ON a.idArticulo = e.idArticulo
    JOIN tiendas t ON e.idTienda = t.idTienda
    ORDER BY a.idArticulo, t.descripcion
";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error al obtener las existencias: " . mysqli_error($conn);
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Existencias - Administración</title>
    <style>
        /* Agrega el estilo del carrito */
        body {
            font-family: 'Helvetica Neue', sans-serif;
            background-color: #ffffff;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #000000;
            color: #d4af37; /* Dorado */
            padding: 20px;
            text-align: center;
        }

        main {
            padding: 30px;
            text-align: center;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #b89b5e;
            color: white;
        }

        .btn {
            padding: 10px 20px;
            background-color: #d4af37;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #a88c4e;
        }

        .btn-danger {
            background-color: #ff0000;
        }

        .btn-danger:hover {
            background-color: #cc0000;
        }
    </style>
</head>
<body>

<header>
    <h1>Joyería Suárez - Administración</h1>
</header>

<main>
    <h2>Gestión de Existencias</h2>

    <table>
        <tr>
            <th>Imagen</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Existencias</th>
            <th>Tienda</th>
            <th>Acciones</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><img src="uploads/<?= htmlspecialchars($row['imagen']) ?>" alt="Imagen de producto" width="80" height="80"></td>
                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                <td>$<?= number_format($row['precio'], 2) ?></td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= htmlspecialchars($row['tienda']) ?></td>
                <td>
                    <!-- Botón para editar -->
                    <a href="editar_existencia.php?id=<?= $row['idArticulo'] ?>&tienda=<?= $row['idTienda'] ?>" class="btn">Editar</a>

                    <!-- Botón para eliminar -->
                    <a href="eliminar_existencia.php?id=<?= $row['idArticulo'] ?>&tienda=<?= $row['idTienda'] ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta existencia?');">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>

    </table>

    <a href="productos.php" class="btn">Ver Productos</a>
</main>

</body>
</html>

<?php
mysqli_free_result($result);
$db->close();
?>
