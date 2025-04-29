<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$pais = $_POST['pais'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$descripcion = $_POST['descripcion'] ?? ''; // <-- añadido aquí

// Verificar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Procesar imagen (si viene)
$rutaImagen = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $directorio = "../../imagenes/usuarios/";
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $nombreArchivo = "img_" . uniqid() . "." . $ext;
    $rutaCompleta = $directorio . $nombreArchivo;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
        echo json_encode(['success' => false, 'mensaje' => 'Error al subir la imagen.']);
        exit;
    }

    $rutaImagen = "imagenes/usuarios/" . $nombreArchivo;
}

// Construir SQL
$sql = "UPDATE usuarios SET pais = ?, nacimiento = ?, ciudad = ?, descripcion = ?, primera = FALSE";
if ($rutaImagen) {
    $sql .= ", foto = ?";
}
$sql .= " WHERE id = ?";

$stmt = $conn->prepare($sql);
if ($rutaImagen) {
    $stmt->bind_param("sssssi", $pais, $fecha, $ciudad, $descripcion, $rutaImagen, $id);
} else {
    $stmt->bind_param("ssssi", $pais, $fecha, $ciudad, $descripcion, $id);
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al guardar los datos.']);
    exit;
}
$stmt->close();

// Insertar palabras clave con puntuación 0
$sql = "INSERT IGNORE INTO interesusuario (usuario, palabra_id, puntuacion) SELECT ?, id, 0 FROM palabrasclave";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'mensaje' => 'Datos actualizados correctamente.',
    'ruta_foto' => $rutaImagen
]);

$conn->close();
?>
