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

// Verificar amistad si no es el propio perfil
if ($id != $idusuario) {
    $sqlAmigos = "SELECT id FROM amigos WHERE 
                  (usuario1 = ? AND usuario2 = ?) OR 
                  (usuario1 = ? AND usuario2 = ?)";
    $stmt = $conn->prepare($sqlAmigos);
    $stmt->bind_param("iiii", $id, $idusuario, $idusuario, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "mensaje" => "No tienes permiso para ver estos posts."]);
        exit;
    }
    $stmt->close();
}

// Obtener posts del usuario
$sql = "SELECT p.*, u.nombre, u.apellidos, u.foto
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
$posts_con_compartido = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
    $post_ids[] = $row['id'];
    if ($row['tipo'] == 3 && $row['idcompartido'] > 0) {
        $posts_con_compartido[] = $row['idcompartido'];
    }
}
$stmt->close();

// Obtener likes del usuario actual
$likedPosts = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $types = str_repeat('i', count($post_ids));
    $sqlLikes = "SELECT post FROM likespost WHERE post IN ($placeholders) AND usuario = ?";
    $stmt = $conn->prepare($sqlLikes);
    $params = array_merge($post_ids, [$id]);
    $types .= 'i';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($like = $res->fetch_assoc()) {
        $likedPosts[] = $like['post'];
    }
    $stmt->close();
}

// Obtener imágenes de los posts
$imagenesPorPost = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $types = str_repeat('i', count($post_ids));
    $sqlImg = "SELECT post_id, ruta FROM imagenes WHERE post_id IN ($placeholders)";
    $stmt = $conn->prepare($sqlImg);
    $stmt->bind_param($types, ...$post_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($img = $res->fetch_assoc()) {
        $imagenesPorPost[$img['post_id']][] = $img['ruta'];
    }
    $stmt->close();
}

// Obtener datos de posts compartidos (pueden ser de usuario o página)
$compartidos = [];
if (!empty($posts_con_compartido)) {
    $placeholders = implode(',', array_fill(0, count($posts_con_compartido), '?'));
    $types = str_repeat('i', count($posts_con_compartido));

    // Posts y nombre/foto de origen (usuario o página)
    $sql = "SELECT p.*, 
            IFNULL(u.nombre, pa.nombre) AS nombre,
            u.apellidos,
            IFNULL(u.foto, pa.imagen) AS foto,
            IF(u.id IS NOT NULL, 1, 2) AS tipo
        FROM posts p
        LEFT JOIN usuarios u ON p.usuario = u.id
        LEFT JOIN paginas pa ON p.usuario = pa.id
        WHERE p.id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$posts_con_compartido);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($comp = $res->fetch_assoc()) {
        $compartidos[$comp['id']] = $comp;
    }
    $stmt->close();

    // Imágenes de posts compartidos
    $sqlImgComp = "SELECT post_id, ruta FROM imagenes WHERE post_id IN ($placeholders)";
    $stmt = $conn->prepare($sqlImgComp);
    $stmt->bind_param($types, ...$posts_con_compartido);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($img = $res->fetch_assoc()) {
        $compartidos[$img['post_id']]['imagenes'][] = $img['ruta'];
    }
    $stmt->close();
}

// Armar el array final
foreach ($posts as &$post) {
    $post['imagenes'] = $imagenesPorPost[$post['id']] ?? [];
    $post['liked'] = in_array($post['id'], $likedPosts);
    if ($post['tipo'] == 3 && isset($compartidos[$post['idcompartido']])) {
        $post['compartido'] = $compartidos[$post['idcompartido']];
    }
}

echo json_encode([
    "success" => true,
    "posts" => $posts
]);

$conn->close();
