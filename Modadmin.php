<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit;
}

include_once("MysqlConnector.php");
$db = new MysqlConnector();
$conn = $db->connect();

// Verificar si es superadmin
$stmt = $conn->prepare("SELECT tipo_usuario FROM admins WHERE idAdmin = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$current_admin = $result->fetch_assoc();
$is_superadmin = ($current_admin['tipo_usuario'] == 'superadmin');

// Obtener el ID del administrador a editar
$idAdmin = $_GET['id'] ?? null;

// Verificar permisos
if (!$is_superadmin && $idAdmin != $_SESSION['admin_id']) {
    echo "No tienes permiso para editar este perfil.";
    exit;
}

// Obtener datos del administrador a editar
$stmt = $conn->prepare("SELECT usuario, nombre_completo, correo, telefono, tipo_usuario FROM admins WHERE idAdmin = ?");
$stmt->bind_param("i", $idAdmin);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevo_usuario = trim($_POST['usuario']);
    $nuevo_nombre_completo = trim($_POST['nombre_completo']);
    $nuevo_correo = trim($_POST['correo']);
    $nuevo_telefono = trim($_POST['telefono']);
    $nuevo_tipo_usuario = $_POST['tipo_usuario'];
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    // Solo verificar contraseña si no es superadmin o está editando su propio perfil
    if (!$is_superadmin || $idAdmin == $_SESSION['admin_id']) {
        $stmt = $conn->prepare("SELECT contrasena FROM admins WHERE idAdmin = ?");
        $stmt->bind_param("i", $idAdmin);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $datos = $resultado->fetch_assoc();

        if (!password_verify($contrasena_actual, $datos['contrasena'])) {
            echo "<script>alert('Contraseña actual incorrecta');</script>";
            exit;
        }
    }

    // Actualizar datos
    $update_fields = "usuario = ?, nombre_completo = ?, correo = ?, telefono = ?";
    $params = [$nuevo_usuario, $nuevo_nombre_completo, $nuevo_correo, $nuevo_telefono];
    $types = "ssss";

    // Solo permitir cambiar tipo_usuario si es superadmin y no es su propio perfil
    if ($is_superadmin && $idAdmin != $_SESSION['admin_id']) {
        $update_fields .= ", tipo_usuario = ?";
        $params[] = $nuevo_tipo_usuario;
        $types .= "s";
    }

    // Cambiar contraseña solo si se proporcionó una nueva
    if (!empty($nueva_contrasena)) {
        $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $update_fields .= ", contrasena = ?";
        $params[] = $hash;
        $types .= "s";
    }

    $params[] = $idAdmin;
    $types .= "i";

    $query = "UPDATE admins SET $update_fields WHERE idAdmin = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "<script>alert('Datos actualizados correctamente'); window.location.href = 'ShowAdmin.php';</script>";
        exit;
    } else {
        echo "Error al actualizar los datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/stylo_modadmin.css">
</head>
<body>
    <div class="header">
        <a href="ShowAdmin.php" class="home-icon" title="Volver a administradores">
            <i class="fas fa-home"></i>
        </a>
        <h1><?= ($idAdmin == $_SESSION['admin_id']) ? 'Editar mi perfil' : 'Editar Administrador' ?></h1>
    </div>
    
    <form method="POST">
        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($admin['usuario']) ?>" required>

        <label for="nombre_completo">Nombre completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" value="<?= htmlspecialchars($admin['nombre_completo']) ?>" required>

        <label for="correo">Correo electrónico:</label>
        <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($admin['correo']) ?>" required>

        <label for="telefono">Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($admin['telefono']) ?>" required>

        <label for="tipo_usuario">Tipo de usuario:</label>
        <select id="tipo_usuario" name="tipo_usuario" <?= ($is_superadmin && $idAdmin != $_SESSION['admin_id']) ? '' : 'disabled' ?>>
            <option value="admin" <?= $admin['tipo_usuario'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="superadmin" <?= $admin['tipo_usuario'] == 'superadmin' ? 'selected' : '' ?>>Super Administrador</option>
        </select>
        <?php if (!($is_superadmin && $idAdmin != $_SESSION['admin_id'])): ?>
            <p class="info-note">Nota: Solo un superadmin puede cambiar este campo</p>
        <?php endif; ?>

        <?php if (!$is_superadmin || $idAdmin == $_SESSION['admin_id']): ?>
            <label for="contrasena_actual">Contraseña actual:</label>
            <input type="password" id="contrasena_actual" name="contrasena_actual" required>
            
            <label for="nueva_contrasena">Nueva contraseña (dejar en blanco para no cambiar):</label>
            <input type="password" id="nueva_contrasena" name="nueva_contrasena">
            <p class="info-note">La contraseña debe tener al menos 8 caracteres</p>
        <?php else: ?>
            <input type="hidden" name="contrasena_actual" value="bypass_for_superadmin">
            <input type="hidden" name="nueva_contrasena" value="<?= bin2hex(random_bytes(8)) ?>">
        <?php endif; ?>

        <button type="submit">Guardar cambios</button>
    </form>
</body>
</html>