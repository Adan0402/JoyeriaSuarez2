<?php
session_start();

if (isset($_GET['idArticulo'])) {
    $idArticulo = $_GET['idArticulo'];
    
    // Buscamos el artículo en el carrito y lo eliminamos
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['idArticulo'] == $idArticulo) {
            unset($_SESSION['carrito'][$key]);
            echo "Producto eliminado del carrito.";
            break;
        }
    }
}

header("Location: carrito.php"); // Redirigir al carrito actualizado
exit();
?>
