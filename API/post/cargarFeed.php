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

// Cargar posts
$sql = "SELECT * FROM posts ORDER BY fecha DESC LIMIT ? OFFSET ?";
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

// Obtener info adicional según tipo del post (usuario o página)
foreach ($posts as &$post) {
    if ($post['tipo'] == 2) {
        // Post de página
        $stmt = $conn->prepare("SELECT nombre, imagen FROM paginas WHERE id = ?");
        $stmt->bind_param("i", $post['usuario']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $datos = $res->fetch_assoc();
            $post['usuario_nombre'] = $datos['nombre'];
            $post['usuario_apellidos'] = '';
            $post['usuario_foto'] = $datos['imagen'];
        }
        $stmt->close();
    } else {
        // Post de usuario
        $stmt = $conn->prepare("SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $post['usuario']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $datos = $res->fetch_assoc();
            $post['usuario_nombre'] = $datos['nombre'];
            $post['usuario_apellidos'] = $datos['apellidos'];
            $post['usuario_foto'] = $datos['foto'];
        }
        $stmt->close();
    }
}

// Cargar imágenes asociadas
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

// Obtener datos de posts compartidos
$compartidos = [];
if (!empty($post_ids_compartidos)) {
    $placeholders = implode(',', array_fill(0, count($post_ids_compartidos), '?'));
    $types = str_repeat('i', count($post_ids_compartidos));
    $sql = "SELECT * FROM posts WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$post_ids_compartidos);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $compartidos[$row['id']] = $row;
    }
    $stmt->close();

    // Agregar nombre/foto a compartidos
    foreach ($compartidos as &$comp) {
        if ($comp['tipo'] == 2) {
            $stmt = $conn->prepare("SELECT nombre, imagen FROM paginas WHERE id = ?");
        } else {
            $stmt = $conn->prepare("SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?");
        }
        $stmt->bind_param("i", $comp['usuario']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $datos = $res->fetch_assoc();
            $comp['nombre'] = $datos['nombre'];
            $comp['apellidos'] = $datos['apellidos'] ?? '';
            $comp['foto'] = $datos['foto'] ?? $datos['imagen'] ?? '';
        }
        $stmt->close();
    }

    // Cargar imágenes para compartidos
    $sql = "SELECT post_id, ruta FROM imagenes WHERE post_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$post_ids_compartidos);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($img = $res->fetch_assoc()) {
        $compartidos[$img['post_id']]['imagenes'][] = $img['ruta'];
    }
    $stmt->close();
}

// Cargar likes del usuario
$likesUsuario = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $types = str_repeat('i', count($post_ids));
    $sql = "SELECT post FROM likespost WHERE usuario = ? AND post IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($post_ids) + 1), $id, ...$post_ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($like = $res->fetch_assoc()) {
        $likesUsuario[] = $like['post'];
    }
    $stmt->close();
}

// Agregar datos finales
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
