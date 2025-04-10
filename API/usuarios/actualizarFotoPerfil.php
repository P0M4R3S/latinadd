<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Subida de imagen
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al subir la imagen.']);
    exit;
}

$directorio = "imagenes/";
if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
}

$extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
$nombreImagen = uniqid("perfil_", true) . "." . $extension;
$rutaImagen = $directorio . $nombreImagen;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaImagen)) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al guardar la imagen.']);
    exit;
}

// Actualizar ruta en la base de datos
$sql = "UPDATE usuarios SET foto = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $rutaImagen, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Foto actualizada correctamente.', 'foto' => $rutaImagen]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar la foto.']);
}

$stmt->close();
$conn->close();
?>
