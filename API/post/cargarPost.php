<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Datos recibidos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Obtener datos del post
$sql = "SELECT p.id, p.tipo, p.usuario, p.texto, p.fecha, p.likes, p.comentarios, p.idcompartido,
               u.nombre, u.apellidos, u.foto
        FROM posts p
        JOIN usuarios u ON p.usuario = u.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// Obtener imágenes asociadas
$sql = "SELECT ruta FROM imagenes WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

$imagenes = [];
while ($row = $result->fetch_assoc()) {
    $imagenes[] = $row['ruta'];
}
$post['imagenes'] = $imagenes;
$stmt->close();

// Obtener comentarios del post
$sql = "SELECT c.id, c.usuario AS idusuario, u.nombre, u.apellidos, u.foto, c.texto, c.fecha, c.idrespuesta
        FROM comentariospost c
        JOIN usuarios u ON c.usuario = u.id
        WHERE c.post = ?
        ORDER BY c.fecha ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

$comentarios = [];
while ($row = $result->fetch_assoc()) {
    $comentarios[] = $row;
}
$stmt->close();

echo json_encode([
    'success' => true,
    'post' => $post,
    'comentarios' => $comentarios
]);

$conn->close();
?>
