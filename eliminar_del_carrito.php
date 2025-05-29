<?php
session_start();

// Verificamos si se pasó el idArticulo
if (isset($_GET['idArticulo'])) {
    $idArticulo = $_GET['idArticulo'];

    // Buscamos el artículo en el carrito y lo eliminamos
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['idArticulo'] == $idArticulo) {
            unset($_SESSION['carrito'][$key]);
            break;
        }
    }
}

// Redirigir al carrito sin enviar salida previa
header("Location: carrito.php");
exit();
?>
