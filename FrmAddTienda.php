<?php
session_start();
include_once("MysqlConnector.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $descripcion = $_POST['descripcion'];
    $ciudad = $_POST['ciudad'];
    $direccion = $_POST['direccion'];
    $codigoPostal = $_POST['codigoPostal'];
    $horario = $_POST['horario'];
    
    $imagenNombre = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $directorioImagenes = 'img/tiendas/';
        
        // Crear directorio si no existe
        if (!file_exists($directorioImagenes)) {
            mkdir($directorioImagenes, 0777, true);
        }
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $nombreOriginal = $_FILES['imagen']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionesPermitidas)) {
            die("<p class='msg-error'>Error: Solo se permiten archivos JPG, JPEG, PNG o GIF.</p>");
        }
        
        $tamanoMaximo = 2 * 1024 * 1024; // 2MB
        if ($_FILES['imagen']['size'] > $tamanoMaximo) {
            die("<p class='msg-error'>Error: El archivo es demasiado grande (máximo 2MB).</p>");
        }
        
        // Generar nombre único y mover archivo
        $nombreUnico = uniqid('tienda_', true) . '.' . $extension;
        $rutaDestino = $directorioImagenes . $nombreUnico;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagenNombre = $rutaDestino;
        } else {
            die("<p class='msg-error'>Error al subir la imagen.</p>");
        }
    }

    $db = new MysqlConnector();
    $conn = $db->connect();

    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO tiendas 
                              (descripcion, ciudad, direccion, codigoPostal, horario, imagen) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $descripcion, $ciudad, $direccion, $codigoPostal, $horario, $imagenNombre);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "<p class='msg-error'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $db->close();
    } else {
        echo "<p class='msg-error'>Error de conexión a la base de datos.</p>";
    }
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Tienda</title>
    <link rel="stylesheet" href="css/stylo_FrmAddTienda.css">
</head>
<body>

<div class="home-button">
    <a href="index.php">Inicio</a>
</div>

<h1>Agregar Nueva Tienda</h1>

<form method="POST" action="FrmAddTienda.php" enctype="multipart/form-data">
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="descripcion" required>
    </div>
    
    <div class="form-group">
        <label for="ciudad">Ciudad:</label>
        <input type="text" name="ciudad" id="ciudad" required>
    </div>
    
    <div class="form-group">
        <label for="direccion">Dirección:</label>
        <input type="text" name="direccion" id="direccion" required>
    </div>
    
    <div class="form-group">
        <label for="codigoPostal">Código Postal:</label>
        <input type="text" name="codigoPostal" id="codigoPostal" required>
    </div>
    
    <div class="form-group">
        <label for="horario">Horario:</label>
        <input type="text" name="horario" id="horario" required>
    </div>
    
    <div class="form-group file-input">
        <label for="imagen">Imagen de la tienda:</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">
        <div id="preview-container"></div>
    </div>
    
    <input type="submit" value="Agregar Tienda">
</form>

<script>
document.getElementById('imagen').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview-container');
    previewContainer.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'file-preview';
            previewContainer.appendChild(preview);
        }
        
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

</body>
</html>
<?php
}
?>