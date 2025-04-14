<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Obtener datos del usuario y post
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Obtener datos del post principal
$sql = "SELECT p.id, p.tipo, p.usuario, p.texto, p.fecha, p.likes, p.comentarios, p.idcompartido,
               u.nombre, u.apellidos, u.foto
        FROM posts p
        JOIN usuarios u ON p.usuario = u.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error SQL (post): ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// Verificar si el usuario ha dado like al post
$sql = "SELECT 1 FROM likespost WHERE usuario = ? AND post = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error SQL (likes post): ' . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $id, $idpost);
$stmt->execute();
$result = $stmt->get_result();
$post['liked'] = $result->num_rows > 0;
$stmt->close();

// Obtener imágenes del post
$sql = "SELECT ruta FROM imagenes WHERE post_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error SQL (imagenes): ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

$imagenes = [];
while ($row = $result->fetch_assoc()) {
    $imagenes[] = $row['ruta'];
}
$post['imagenes'] = $imagenes;
$stmt->close();

// Si el post es compartido, obtener datos del original
if ($post['tipo'] == 3 && !empty($post['idcompartido'])) {
    $sql = "SELECT p.id, p.usuario, p.texto, p.fecha, p.likes, p.comentarios,
                   u.nombre, u.apellidos, u.foto
            FROM posts p
            JOIN usuarios u ON p.usuario = u.id
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $post['idcompartido']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $compartido = $result->fetch_assoc();

            // Obtener imágenes del compartido
            $sql = "SELECT ruta FROM imagenes WHERE post_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $compartido['id']);
            $stmt->execute();
            $resImg = $stmt->get_result();

            $imagenesComp = [];
            while ($img = $resImg->fetch_assoc()) {
                $imagenesComp[] = $img['ruta'];
            }
            $compartido['imagenes'] = $imagenesComp;
            $post['compartido'] = $compartido;
        } else {
            $post['compartido'] = null;
        }
        $stmt->close();
    }
}

// Obtener comentarios con campo liked
$sql = "SELECT c.id, c.usuario AS idusuario, u.nombre, u.apellidos, u.foto, c.texto, c.fecha, c.idrespuesta,
               IF(lc.id IS NOT NULL, 1, 0) AS liked
        FROM comentariospost c
        JOIN usuarios u ON c.usuario = u.id
        LEFT JOIN likescomentarios lc ON lc.usuario = ? AND lc.comentario = c.id
        WHERE c.post = ?
        ORDER BY c.fecha ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error SQL (comentarios): ' . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $id, $idpost);
$stmt->execute();
$result = $stmt->get_result();

$comentarios = [];
while ($row = $result->fetch_assoc()) {
    $row['liked'] = boolval($row['liked']);
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
