<?php
session_start();
include_once("MysqlConnector.php");

// Validación básica del parámetro
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='error'>ID de tienda no válido.</div>";
    exit;
}

$db = new MysqlConnector();
$conn = $db->connect();

$idTienda = (int)$_GET['id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $descripcion  = $_POST['descripcion'];
    $ciudad       = $_POST['ciudad'];
    $direccion    = $_POST['direccion'];
    $codigoPostal = $_POST['codigoPostal'];
    $horario      = $_POST['horario'];
    
    // Manejo de la imagen
    $imagenNombre = $_POST['imagen_actual']; // Mantener la imagen actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $directorioImagenes = 'img/tiendas/';
        
        // Validar tipo de archivo
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $nombreOriginal = $_FILES['imagen']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionesPermitidas)) {
            echo "<div class='error'>Error: Solo se permiten archivos JPG, JPEG, PNG o GIF.</div>";
            exit;
        }
        
        // Validar tamaño
        $tamanoMaximo = 2 * 1024 * 1024; // 2MB
        if ($_FILES['imagen']['size'] > $tamanoMaximo) {
            echo "<div class='error'>Error: El archivo es demasiado grande (máximo 2MB).</div>";
            exit;
        }
        
        // Eliminar imagen anterior si existe
        if (!empty($_POST['imagen_actual']) && file_exists($_POST['imagen_actual'])) {
            unlink($_POST['imagen_actual']);
        }
        
        // Generar nombre único y mover archivo
        $nombreUnico = uniqid('tienda_', true) . '.' . $extension;
        $rutaDestino = $directorioImagenes . $nombreUnico;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagenNombre = $rutaDestino;
        } else {
            echo "<div class='error'>Error al subir la imagen.</div>";
            exit;
        }
    }

    $stmt = $conn->prepare("UPDATE tiendas SET descripcion = ?, ciudad = ?, direccion = ?, codigoPostal = ?, horario = ?, imagen = ? WHERE idTienda = ?");
    $stmt->bind_param("ssssssi", $descripcion, $ciudad, $direccion, $codigoPostal, $horario, $imagenNombre, $idTienda);

    if ($stmt->execute()) {
        header("Location: ver_tiendas.php");
        exit;
    } else {
        echo "<div class='error'>Error al actualizar la tienda: " . $stmt->error . "</div>";
    }
}

$stmt = $conn->prepare("SELECT * FROM tiendas WHERE idTienda = ?");
$stmt->bind_param("i", $idTienda);
$stmt->execute();
$result = $stmt->get_result();
$tienda = $result->fetch_assoc();

if (!$tienda) {
    echo "<div class='error'>Tienda no encontrada.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Tienda</title>
  <link rel="stylesheet" href="css/stylo_ModTienda.css">
</head>
<body>

<div class="home-icon">
    <a href="index.php" title="Inicio">&#8962;</a>
</div>

<div class="container">
  <h2>Editar Tienda ID <?= $idTienda ?></h2>
  
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="imagen_actual" value="<?= $tienda['imagen'] ?>">
    
    <div class="form-group">
      <label for="descripcion">Descripción:</label>
      <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($tienda['descripcion']) ?>" required>
    </div>
    
    <div class="form-group">
      <label for="ciudad">Ciudad:</label>
      <input type="text" id="ciudad" name="ciudad" value="<?= htmlspecialchars($tienda['ciudad']) ?>" required>
    </div>
    
    <div class="form-group">
      <label for="direccion">Dirección:</label>
      <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($tienda['direccion']) ?>" required>
    </div>
    
    <div class="form-group">
      <label for="codigoPostal">Código Postal:</label>
      <input type="text" id="codigoPostal" name="codigoPostal" value="<?= htmlspecialchars($tienda['codigoPostal']) ?>" required>
    </div>
    
    <div class="form-group">
      <label for="horario">Horario:</label>
      <input type="text" id="horario" name="horario" value="<?= htmlspecialchars($tienda['horario']) ?>" required>
    </div>
    
    <div class="form-group file-input">
      <label for="imagen">Imagen de la tienda:</label>
      <?php if ($tienda['imagen'] && file_exists($tienda['imagen'])): ?>
        <div class="current-image">
          <img src="<?= $tienda['imagen'] ?>" alt="Imagen actual">
          <p>Imagen actual</p>
        </div>
      <?php else: ?>
        <p class="no-image">No hay imagen actual</p>
      <?php endif; ?>
      <input type="file" id="imagen" name="imagen" accept="image/*">
    </div>
    
    <div class="form-actions">
      <input type="submit" value="Guardar Cambios" class="btn-submit">
      <a href="ver_tiendas.php" class="btn-cancel">Cancelar</a>
    </div>
  </form>
</div>

<script>
// Mostrar vista previa de la nueva imagen seleccionada
document.getElementById('imagen').addEventListener('change', function(e) {
    const previewContainer = document.querySelector('.file-input');
    let preview = previewContainer.querySelector('.new-image-preview');
    
    if (!preview) {
        preview = document.createElement('div');
        preview.className = 'new-image-preview';
        previewContainer.appendChild(preview);
    }
    
    preview.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(this.files[0]);
        preview.appendChild(img);
        
        const label = document.createElement('p');
        label.textContent = 'Nueva imagen seleccionada';
        preview.appendChild(label);
    }
});
</script>

</body>
</html>
<?php
$conn->close();
?>