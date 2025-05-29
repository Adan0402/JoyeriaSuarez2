<?php
session_start();
include_once("MysqlConnector.php");

if (isset($_GET['id']) && isset($_GET['tienda'])) {
    $idArticulo = $_GET['id'];
    $idTienda = $_GET['tienda'];

    $db   = new MysqlConnector();
    $conn = $db->connect();

    $sql = "DELETE FROM existencias WHERE idArticulo = ? AND idTienda = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idArticulo, $idTienda);
    $stmt->execute();

    $db->close();

    header("Location: existencias.php");
    exit;
}
?>
