<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

// Obtener los datos enviados mediante POST
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$pais = $_POST['pais'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';

// Verificar que la sesión es válida
if (!validarSesion($id, $token)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
    exit;
}

// Manejo de la imagen (si se ha subido)
$rutaImagen = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $directorioSubida = "../../imagenes/";
    if (!is_dir($directorioSubida)) {
        mkdir($directorioSubida, 0777, true);
    }

    $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombreImagen = uniqid("img_", true) . "." . $extension;
    $rutaCompleta = $directorioSubida . $nombreImagen;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al subir la imagen.'
        ]);
        exit;
    }

    // Ruta que se guardará en la base de datos (relativa)
    $rutaImagen = 'imagenes/' . $nombreImagen;
}

// Armar y ejecutar la query de actualización
$sql_update = "UPDATE usuarios SET pais = ?, nacimiento = ?, ciudad = ?, primera = FALSE";
if ($rutaImagen) {
    $sql_update .= ", foto = ?";
}
$sql_update .= " WHERE id = ?";

$stmt = $conn->prepare($sql_update);

if ($rutaImagen) {
    $stmt->bind_param("ssssi", $pais, $fecha, $ciudad, $rutaImagen, $id);
} else {
    $stmt->bind_param("sssi", $pais, $fecha, $ciudad, $id);
}

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al actualizar los datos.'
    ]);
    exit;
}
$stmt->close();

// Inicializar todas las palabras clave en interesusuario con puntuación 0
$sql = "INSERT IGNORE INTO interesusuario (usuario, palabra_id, puntuacion) 
        SELECT ?, id, 0 FROM palabrasclave";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Respuesta final
echo json_encode([
    'success' => true,
    'mensaje' => 'Datos actualizados correctamente.',
    'ruta_foto' => $rutaImagen
]);

$conn->close();
?>
