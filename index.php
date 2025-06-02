<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: loginadmin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Panel de Administración - Suárez Joyería</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: "Arial", sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            position: relative;
            min-height: 100vh;
        }
        header {
            background-color: #b89b5e;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .menu-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 60px; /* Espacio para el botón de salir */
        }
        .menu-item {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .menu-item:hover {
            transform: scale(1.05);
        }
        .menu-item a {
            text-decoration: none;
            color: #b89b5e;
            font-size: 18px;
            font-weight: bold;
            display: block;
            margin: 20px 0;
        }
        .menu-item a:hover {
            color: #a88c4e;
        }
        footer {
            background-color: #b89b5e;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .logout-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        .logout-btn:hover {
            background-color: #c0392b;
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <header>Panel de Administración</header>

    <div class="menu-container">
        <div class="menu-item">
            <h3>Gestión de Órdenes</h3>
            <a href="ordenes_admin.php">Ver Órdenes Pendientes</a>
        </div>

        <div class="menu-item">
            <h3>Gestión de Productos</h3>
            <a href="Showproductos.php">Ver Productos</a>
            <a href="FrmAddArticulos.php">Agregar Producto</a>
        </div>

        <div class="menu-item">
            <h3>Gestión de Clientes</h3>
            <a href="ver_clientes.php">Ver Clientes</a>
        </div>

        <div class="menu-item">
            <h3>Gestión de Ventas</h3>
            <a href="ventas.php">Ventas</a>
        </div>

        <div class="menu-item">
            <h3>Gestión de Inventario</h3>
            <a href="ver_inventario.php">Ver Inventario</a>
            <a href="ShowExistencias.php">Actualizar Inventario</a>
        </div>

        <div class="menu-item">
            <h3>Gestión de Sucursales</h3>
            <a href="FrmAddTienda.php">Agregar Sucursales</a>
            <a href="ver_tiendas.php">Actualizar Sucursales</a>
        </div>

        <div class="menu-item">
            <h3>Reportes</h3>
            <a href="reporte_ventas.php">Reporte de Ventas</a>
        </div>

        <div class="menu-item">
            <h3>Reportes</h3>
            <a href="Des_articulos.php">Articulos Desahabilitados</a>
        </div>

        <div class="menu-item">
            <h3>Reportes</h3>
            <a href="ShowAdmin.php">Administracion</a>
        </div>

        <div class="menu-item">
            <h3>Nueva LLinea</h3>
            <a href="FrmAddLinea.php">nueva Linea</a>
        </div>


    </div>

    <button class="logout-btn" onclick="window.location.href='logout.php'" title="Cerrar sesión">
        <i class="fas fa-sign-out-alt"></i>
    </button>

    <footer>
        &copy; <?= date("Y") ?> Juárez Joyería
    </footer>
</body>
</html>
