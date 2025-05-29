<?php
// FrmAddArticulos.php
include_once("MysqlConnector.php");
$db   = new MysqlConnector();
$conn = $db->connect();

// Procesar formulario al enviar artículo
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1) Crear nueva categoría si se proporciona
    if (!empty(trim($_POST['descripcion_nueva_linea']))) {
        $descLinea = trim($_POST['descripcion_nueva_linea']);
        $stmtLinea = $conn->prepare("INSERT INTO linea_articulos (descripcion) VALUES (?)");
        $stmtLinea->bind_param("s", $descLinea);
        $stmtLinea->execute();
        $idLinea = $stmtLinea->insert_id;
        $stmtLinea->close();
    } else {
        // 2) Usar línea existente
        $idLinea = isset($_POST['idLinea_existente']) ? (int)$_POST['idLinea_existente'] : 0;
    }

    // 3) Datos del artículo
    $descripcion     = trim($_POST['descripcion']);
    $caracteristicas = trim($_POST['caracteristicas']);
    $precio          = (float) $_POST['precio'];

    // 4) Validar imagen
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir la imagen.");
    }
    $info = getimagesize($_FILES['imagen']['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
        die("Solo JPG o PNG.");
    }
    if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
        die("La imagen supera 2 MB.");
    }
    $ext    = $info[2] === IMAGETYPE_JPEG ? '.jpg' : '.png';
    $nombre = uniqid('art_') . $ext;
    $dest   = __DIR__ . "/uploads/{$nombre}";
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
        die("No se pudo guardar la imagen.");
    }

    // 5) Insertar artículo
    $stmt = $conn->prepare(
        "INSERT INTO articulos (idLinea, descripcion, caracteristicas, precio, imagen) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issds", $idLinea, $descripcion, $caracteristicas, $precio, $nombre);
    if (!$stmt->execute()) {
        die("Error al guardar artículo: " . htmlspecialchars($stmt->error));
    }
    $idArticulo = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // 6) Redirigir a FrmAsignarExistencias con el ID del artículo
    header("Location: FrmAsignarExistencias.php?idArticulo={$idArticulo}");
    exit();
}

// GET: mostrar formulario
$lineas = $conn->query("SELECT * FROM linea_articulos ORDER BY descripcion");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Artículo</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f8f8; padding:20px; max-width:600px; margin:auto; }
    h1 { color: #b89b5e; text-align: center; }
    form { background: white; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; flex-direction:column; gap:15px; }
    label { font-weight:bold; }
    input, select, button { padding:8px; font-size:1rem; border:1px solid #ccc; border-radius:5px; }
    input[type=file] { padding:4px; }
    .grupo-linea { display:flex; gap:10px; }
    .grupo-linea > div { flex:1; }
    button { background:#b89b5e; color:white; border:none; cursor:pointer; }
    button:hover { background:#a88c4e; }
  </style>
</head>
<body>
  <h1>Agregar Artículo</h1>
  <form method="POST" action="FrmAddArticulos.php" enctype="multipart/form-data">
    <div class="grupo-linea">
      <div>
        <label for="idLinea_existente">Categoría existente:</label>
        <select name="idLinea_existente" id="idLinea_existente">
          <option value="">— Selecciona una —</option>
          <?php while($l = $lineas->fetch_assoc()): ?>
            <option value="<?= $l['idLinea'] ?>"><?= htmlspecialchars($l['descripcion']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label for="descripcion_nueva_linea">O nueva categoría:</label>
        <input type="text" name="descripcion_nueva_linea" id="descripcion_nueva_linea" placeholder="Ej. Relojes">
      </div>
    </div>

    <label for="descripcion">Descripción del artículo:</label>
    <input type="text" name="descripcion" id="descripcion" required>

    <label for="caracteristicas">Características:</label>
    <input type="text" name="caracteristicas" id="caracteristicas" required>

    <label for="precio">Precio:</label>
    <input type="number" step="0.01" name="precio" id="precio" required>

    <label for="imagen">Imagen (JPG/PNG):</label>
    <input type="file" name="imagen" id="imagen" accept="image/png, image/jpeg" required>

    <button type="submit">Agregar Artículo</button>
  </form>
</body>
</html>
