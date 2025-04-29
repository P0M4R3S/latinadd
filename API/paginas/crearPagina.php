<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Obtener datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$tema = trim($_POST['tema'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar campos
if (empty($nombre)) {
    echo json_encode(['success' => false, 'mensaje' => 'El nombre de la página no puede estar vacío.']);
    exit;
}

// Manejar imagen
$rutaRelativa = 'imagenes/default.jpg'; // Por defecto
if (!empty($_FILES['img']['tmp_name'])) {
    $directorio = '../imagenes/';
    if (!is_dir($directorio)) mkdir($directorio, 0777, true);

    $extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
    $nombreUnico = uniqid('pagina_', true) . '.' . $extension;
    $ruta = $directorio . $nombreUnico;

    if (!move_uploaded_file($_FILES['img']['tmp_name'], $ruta)) {
        echo json_encode(['success' => false, 'mensaje' => 'Error al guardar la imagen.']);
        exit;
    }

    $rutaRelativa = 'imagenes/' . $nombreUnico;
}

// Insertar página
$sql = "INSERT INTO paginas (usuario, nombre, tema, imagen, descripcion, fecha, seguidores)
        VALUES (?, ?, ?, ?, ?, NOW(), 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $id, $nombre, $tema, $rutaRelativa, $descripcion);

if ($stmt->execute()) {
    $idpagina = $stmt->insert_id;
    echo json_encode([
        'success' => true,
        'idpagina' => $idpagina,
        'mensaje' => 'Página creada correctamente.'
    ]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al crear la página.']);
}

$stmt->close();
$conn->close();
?>
