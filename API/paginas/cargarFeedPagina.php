<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpagina = $_POST['idpagina'] ?? '';
$indice = isset($_POST['indice']) ? intval($_POST['indice']) : 1;

$limite = 10;
$offset = ($indice - 1) * $limite;

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que la página existe y obtener su nombre e imagen
$sql = "SELECT nombre, imagen FROM paginas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La página no existe.']);
    exit;
}
$pagina = $result->fetch_assoc();
$stmt->close();

// Obtener posts de tipo 2 (página)
$sql = "SELECT id, usuario, texto, fecha, likes, comentarios, tipo
        FROM posts
        WHERE usuario = ? AND tipo = 2
        ORDER BY fecha DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $idpagina, $limite, $offset);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
$post_ids = [];

while ($row = $result->fetch_assoc()) {
    $row['nombre'] = $pagina['nombre'];
    $row['foto'] = $pagina['imagen'] ?? 'img/default.png';
    $posts[] = $row;
    $post_ids[] = $row['id'];
}
$stmt->close();

// Obtener imágenes asociadas
$imagenesPorPost = [];

if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $types = str_repeat('i', count($post_ids));
    $sql = "SELECT post_id, ruta FROM imagenes WHERE post_id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$post_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($img = $result->fetch_assoc()) {
        $imagenesPorPost[$img['post_id']][] = $img['ruta'];
    }
    $stmt->close();
}

// Obtener likes del usuario para estos posts
$likesUsuario = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $types = str_repeat('i', count($post_ids));
    $sql = "SELECT post FROM likespost WHERE usuario = ? AND post IN ($placeholders)";

    $stmt = $conn->prepare("SELECT post FROM likespost WHERE usuario = ? AND post IN ($placeholders)");
    $stmt->bind_param("i" . $types, $id, ...$post_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($like = $result->fetch_assoc()) {
        $likesUsuario[$like['post']] = true;
    }
    $stmt->close();
}

// Añadir imágenes y campo liked a cada post
foreach ($posts as &$post) {
    $post['imagenes'] = $imagenesPorPost[$post['id']] ?? [];
    $post['liked'] = $likesUsuario[$post['id']] ?? false;
}

// Devolver datos
echo json_encode([
    'success' => true,
    'posts' => $posts
]);

$conn->close();
?>
