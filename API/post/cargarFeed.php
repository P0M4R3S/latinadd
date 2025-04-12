<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Datos de entrada
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$indice = $_POST['indice'] ?? 1;

$limite = 5;
$offset = ($indice - 1) * $limite;

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Cargar posts de todo tipo por fecha descendente
$sql = "SELECT p.*, 
            u.nombre AS usuario_nombre, u.apellidos AS usuario_apellidos, u.foto AS usuario_foto
        FROM posts p
        LEFT JOIN usuarios u ON p.usuario = u.id
        ORDER BY p.fecha DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limite, $offset);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
$post_ids = [];
$post_ids_compartidos = [];

while ($row = $result->fetch_assoc()) {
    if ($row['tipo'] == 3 && $row['idcompartido'] > 0) {
        $post_ids_compartidos[] = $row['idcompartido'];
    }
    $posts[] = $row;
    $post_ids[] = $row['id'];
}
$stmt->close();

// Cargar imágenes asociadas a posts
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

// Cargar datos de los posts compartidos
$compartidos = [];

if (!empty($post_ids_compartidos)) {
    $placeholders = implode(',', array_fill(0, count($post_ids_compartidos), '?'));
    $types = str_repeat('i', count($post_ids_compartidos));

    $sql = "SELECT p.*, u.nombre, u.apellidos, u.foto
            FROM posts p
            LEFT JOIN usuarios u ON p.usuario = u.id
            WHERE p.id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$post_ids_compartidos);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($comp = $result->fetch_assoc()) {
        $comp['imagenes'] = [];
        $compartidos[$comp['id']] = $comp;
    }
    $stmt->close();

    // Agregar imágenes a los compartidos
    $sql = "SELECT post_id, ruta FROM imagenes WHERE post_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$post_ids_compartidos);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($img = $result->fetch_assoc()) {
        $compartidos[$img['post_id']]['imagenes'][] = $img['ruta'];
    }
    $stmt->close();
}

// Agregar imágenes y datos compartidos a los posts
foreach ($posts as &$post) {
    $post['imagenes'] = $imagenesPorPost[$post['id']] ?? [];
    if ($post['tipo'] == 3 && isset($compartidos[$post['idcompartido']])) {
        $post['compartido'] = $compartidos[$post['idcompartido']];
    } else {
        $post['compartido'] = null;
    }
}

// Cargar posts que el usuario ha dado like
$likesUsuario = [];

if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $types = str_repeat('i', count($post_ids));
    $params = array_merge([$types], $post_ids);
    $sql = "SELECT post FROM likespost WHERE usuario = ? AND post IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    // Construcción dinámica de bind_param
    $stmt->bind_param(str_repeat('i', count($post_ids) + 1), $id, ...$post_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($like = $result->fetch_assoc()) {
        $likesUsuario[] = $like['post'];
    }
    $stmt->close();
}

// Agregar imágenes, datos compartidos y liked
foreach ($posts as &$post) {
    $post['imagenes'] = $imagenesPorPost[$post['id']] ?? [];
    $post['liked'] = in_array($post['id'], $likesUsuario);
    if ($post['tipo'] == 3 && isset($compartidos[$post['idcompartido']])) {
        $post['compartido'] = $compartidos[$post['idcompartido']];
    } else {
        $post['compartido'] = null;
    }
}


echo json_encode([
    'success' => true,
    'posts' => $posts
]);

$conn->close();
