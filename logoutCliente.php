<?php
session_start();

// Eliminar todas las variables de sesión específicas del cliente
unset($_SESSION['cliente_id']);
unset($_SESSION['nombre']);
unset($_SESSION['email']);

// Destruir completamente la sesión
session_destroy();

// Redirigir al login de clientes con mensaje de éxito
header("Location: loginCliente.php?logout=1");
exit();
?>