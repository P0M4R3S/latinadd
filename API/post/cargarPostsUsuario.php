<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Datos de entrada
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? '';
$indice = $_POST['indice'] ?? 1;

$limite = 5;
$offset = ($indice - 1) * $limite;

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(["success" => false, "mensaje" => "Sesión no válida."]);
    exit;
}

// Si no es su propio perfil, verificar amistad
if ($id != $idusuario) {
    $sql = "SELECT id FROM amistades WHERE 
            (usuario1 = ? AND usuario2 = ?) OR 
            (usuario1 = ? AND usuario2 = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $id, $idusuario, $idusuario, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "mensaje" => "No tienes permiso para ver estos posts."]);
        exit;
    }
    $stmt->close();
}

// Obtener posts creados por el usuario (tipo 1) y compartidos (tipo 3)
$sql = "SELECT p.id, p.usuario, p.texto, p.fecha, p.likes, p.comentarios, p.tipo, p.idcompartido,
               u.nombre, u.apellidos, u.foto
        FROM posts p
        JOIN usuarios u ON p.usuario = u.id
        WHERE p.usuario = ?
        ORDER BY p.fecha DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $idusuario, $limite, $offset);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
$post_ids = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
    $post_ids[] = $row['id'];
}
$stmt->close();

// Obtener imágenes asociadas a los posts
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

// Agregar imágenes a cada post
foreach ($posts as &$post) {
    $post['imagenes'] = $imagenesPorPost[$post['id']] ?? [];
}

echo json_encode([
    "success" => true,
    "posts" => $posts
]);

$conn->close();
?>
