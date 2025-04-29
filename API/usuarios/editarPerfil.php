<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

// Verificar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Procesar imagen (si viene)
$rutaFoto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $directorio = "../../imagenes/usuarios/";
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $nombreArchivo = "perfil_" . $id . "_" . time() . "." . $ext;
    $rutaCompleta = $directorio . $nombreArchivo;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
        echo json_encode(['success' => false, 'mensaje' => 'Error al subir la imagen.']);
        exit;
    }

    $rutaFoto = "imagenes/usuarios/" . $nombreArchivo;
}

// Actualizar datos
if ($rutaFoto) {
    $sql = "UPDATE usuarios SET nombre = ?, apellidos = ?, descripcion = ?, foto = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $apellidos, $descripcion, $rutaFoto, $id);
} else {
    $sql = "UPDATE usuarios SET nombre = ?, apellidos = ?, descripcion = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nombre, $apellidos, $descripcion, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Perfil actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el perfil.']);
}

$stmt->close();
$conn->close();
?>
