<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Obtener datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$texto = trim($_POST['texto'] ?? '');
$tipo = $_POST['tipo'] ?? 1; // 1 = usuario, 2 = página, 3 = compartido
$idcompartido = $_POST['idcompartido'] ?? null;
$imagenes = $_POST['imagenes'] ?? []; // Array base64 codificadas

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar tipo
$tipo = intval($tipo);
if (!in_array($tipo, [1, 2, 3])) {
    echo json_encode(['success' => false, 'mensaje' => 'Tipo de post no válido.']);
    exit;
}

if ($tipo !== 3 && empty($texto) && empty($imagenes)) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no puede estar vacío.']);
    exit;
}

if ($tipo === 3 && empty($idcompartido)) {
    echo json_encode(['success' => false, 'mensaje' => 'Debe especificarse el post compartido.']);
    exit;
}

// Insertar post
$sql = "INSERT INTO posts (usuario, texto, fecha, tipo, idcompartido) VALUES (?, ?, NOW(), ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $id, $texto, $tipo, $idcompartido);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al crear el post.']);
    exit;
}
$post_id = $stmt->insert_id;
$stmt->close();

// Guardar imágenes si se enviaron
if (is_array($imagenes) && count($imagenes) > 0) {
    $rutaBase = '../../imagenes/';
    if (!is_dir($rutaBase)) mkdir($rutaBase, 0777, true);

    foreach ($imagenes as $imgBase64) {
        $imgData = base64_decode($imgBase64, true);
        if ($imgData === false) continue;

        $nombre = uniqid('img_', true) . '.jpg';
        $ruta = $rutaBase . $nombre;
        $relativa = 'imagenes/' . $nombre;

        if (file_put_contents($ruta, $imgData, LOCK_EX)) {
            $sql = "INSERT INTO imagenes (usuario, post_id, fecha, tipo, ruta) VALUES (?, ?, NOW(), 'post', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $id, $post_id, $relativa);
            $stmt->execute();
            $stmt->close();
        }
    }
}

echo json_encode([
    'success' => true,
    'idpost' => $post_id,
    'mensaje' => 'Post creado correctamente.'
]);

$conn->close();
?>
