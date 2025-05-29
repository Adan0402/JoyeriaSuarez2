<?php
session_start();
include_once("MysqlConnector.php");

if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginCliente.php");
    exit;
}

if (empty($_SESSION['carrito'])) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Carrito Vacío</title>
        <style>
            body {
                font-family: 'Times New Roman', serif;
                background-color: #F5F5F5;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                flex-direction: column;
            }

            .mensaje {
                background-color: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }

            .mensaje p {
                font-size: 1.2rem;
                color: #555;
            }

            .mensaje a {
                display: inline-block;
                margin-top: 15px;
                text-decoration: none;
                background-color: #b89b5e;
                color: white;
                padding: 10px 20px;
                border-radius: 6px;
                transition: background-color 0.3s;
            }

            .mensaje a:hover {
                background-color: #a88c4e;
            }
        </style>
    </head>
    <body>
        <div class='mensaje'>
            <p>Tu carrito está vacío.</p>
            <a href='productos.php'>Ver productos</a>
        </div>
    </body>
    </html>";
    exit;
}

$idCliente = $_SESSION['cliente_id'];
$ids = array_map('intval', array_keys($_SESSION['carrito']));
$idList = implode(',', $ids);

$db = new MysqlConnector();
$conn = $db->connect();

$conn->begin_transaction();

try {
    $sql = "
        SELECT idArticulo, descripcion, precio 
        FROM articulos 
        WHERE idArticulo IN ($idList)
    ";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error al obtener artículos: " . $conn->error);
    }

    $total = 0;
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $idArt = $row['idArticulo'];
        $cantidad = $_SESSION['carrito'][$idArt]['cantidad'];
        $sub = $row['precio'] * $cantidad;
        $total += $sub;

        $items[] = [
            'idArticulo'  => $idArt,
            'descripcion' => $row['descripcion'],
            'precio'      => $row['precio'],
            'cantidad'    => $cantidad,
            'subtotal'    => $sub
        ];
    }

    $fecha = date('Y-m-d H:i:s');

    $stmtOrden = $conn->prepare("INSERT INTO ordenes (idCliente, fecha, total) VALUES (?, ?, ?)");
    $stmtOrden->bind_param("isd", $idCliente, $fecha, $total);
    $stmtOrden->execute();
    $idOrden = $stmtOrden->insert_id;
    $stmtOrden->close();

    foreach ($items as $item) {
        $stmtDetalle = $conn->prepare("
            INSERT INTO orden_detalle (idOrden, idArticulo, cantidad, precio_unitario) 
            VALUES (?, ?, ?, ?)
        ");
        $stmtDetalle->bind_param("iiid", $idOrden, $item['idArticulo'], $item['cantidad'], $item['precio']);
        $stmtDetalle->execute();
        $stmtDetalle->close();
    }

    $conn->commit();
    unset($_SESSION['carrito']);

    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Orden Registrada</title>
        <style>
            body {
                margin: 0;
                font-family: 'Times New Roman', serif;
                background-color: #EFEFEF;
            }

            .encabezado {
                background-color: #b89b5e; /* dorado */
                color: white;
                padding: 15px 0;
                text-align: center;
                font-size: 1.8rem;
                font-weight: bold;
            }

            .contenido {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 80vh;
            }

            .mensaje {
                background-color: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0,0,0,0.15);
                text-align: center;
                max-width: 500px;
            }

            .mensaje p {
                font-size: 1.2rem;
                margin-bottom: 20px;
                color: #333;
            }

            .mensaje a {
                display: inline-block;
                text-decoration: none;
                background-color: #b89b5e;
                color: white;
                padding: 10px 25px;
                border-radius: 6px;
                transition: background-color 0.3s;
            }

            .mensaje a:hover {
                background-color: #a88c4e;
            }
        </style>
    </head>
    <body>
        <div class='encabezado'>Joyería Suarez</div>
        <div class='contenido'>
            <div class='mensaje'>
                <p>Tu orden ha sido registrada correctamente y está pendiente de aprobación.</p>
                <a href='perfil.php'>Ver tu perfil</a>
            </div>
        </div>
    </body>
    </html>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<p>Error al guardar la orden: " . $e->getMessage() . "</p>";
    exit;
} finally {
    $conn->close();
}
?>
