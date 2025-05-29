<?php
session_start();
include_once("MysqlConnector.php");


if (!isset($_SESSION['admin_id'])) {
    header("Location: loginAdmin.php");
    exit;
}

$db = new MysqlConnector();
$conn = $db->connect();


$query = "SELECT * FROM articulos WHERE activo = 0 ORDER BY descripcion";
$result = $conn->query($query);


if (isset($_GET['generar_pdf'])) {
    
    $html = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Artículos Deshabilitados</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            h1 { text-align: center; color: #b89b5e; }
            p { text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
            th { background-color: #b89b5e; color: white; }
        </style>
    </head>
    <body>
        <h1>Reporte de Artículos Deshabilitados</h1>
        <p>Fecha: ' . date('d/m/Y') . '</p>
        <table>
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Características</th>
                <th>Precio</th>
            </tr>';

    while ($articulo = $result->fetch_assoc()) {
        $html .= '<tr>
            <td>' . $articulo['idArticulo'] . '</td>
            <td>' . htmlspecialchars($articulo['descripcion']) . '</td>
            <td>' . htmlspecialchars($articulo['caracteristicas']) . '</td>
            <td>$' . number_format($articulo['precio'], 2) . '</td>
        </tr>';
    }

    $html .= '</table>
        <script>
            window.onload = function() {
                window.print();
                setTimeout(function() {
                    window.location.href = "Des_articulos.php";
                }, 1500);
            }
        </script>
    </body>
    </html>';

    echo $html;
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Artículos Deshabilitados - Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/stylo_des.css">
</head>
<body>

<header>
    <div class="home-icon">
        <a href="index.php" title="Volver al inicio">
            <i class="fas fa-home"></i>
        </a>
    </div>
    <h1>Artículos Deshabilitados</h1>
</header>

<div class="container">
    <div class="actions-bar">
        <a href="Des_articulos.php?generar_pdf=1" class="pdf-button">
            <i class="fas fa-file-pdf"></i> Generar PDF
        </a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid">
            <?php while($articulo = $result->fetch_assoc()): ?>
                <div class="card disabled">
                    <img src="uploads/<?= htmlspecialchars($articulo['imagen']) ?>" 
                         alt="<?= htmlspecialchars($articulo['descripcion']) ?>" 
                         class="product-image">
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($articulo['descripcion']) ?></h3>
                        <p class="card-text"><?= htmlspecialchars($articulo['caracteristicas']) ?></p>
                        <p class="card-price">$<?= number_format($articulo['precio'], 2) ?></p>
                        
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="info-message">No hay artículos deshabilitados en este momento.</p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> Suárez Joyería</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
