<?php
session_start();
include_once("MysqlConnector.php");

$idArticulo = (int)$_POST['idArticulo'];
$cantidad   = (int)$_POST['cantidad'];

// Conexión a la base de datos
$db = new MysqlConnector();
$conn = $db->connect();

// Obtener la información del artículo
$sql = "SELECT descripcion, precio FROM articulos WHERE idArticulo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {    
$idTienda = (int)$_POST['idTienda']; // <<< Asegúrate que lo estás enviando en el formulario

$item = [
    'descripcion' => $row['descripcion'],
    'precio' => (float)$row['precio'],
    'cantidad' => $cantidad,
    'idTienda' => $idTienda  // <<< Guardamos la tienda del artículo
];



    // Inicializar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Si ya existe, actualizamos cantidad
    if (isset($_SESSION['carrito'][$idArticulo])) {
        $_SESSION['carrito'][$idArticulo]['cantidad'] += $cantidad;
    } else {
        $_SESSION['carrito'][$idArticulo] = $item;
    }
}

$stmt->close();
$conn->close();

// Redirigir al carrito
header("Location: carrito.php");
exit;
