<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';

if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Obtener el post principal
$sql = "SELECT * FROM posts WHERE id = ?";
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

$tipoPost = $post['tipo'];
$autorPost = $post['usuario'];

// Ajustar métricas si es post de página y el usuario que lo ve no es el autor
if ($tipoPost == 2 && $id != $autorPost) {
    $sql = "SELECT palabra_id FROM palabraspagina WHERE pagina_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $autorPost);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($pal = $res->fetch_assoc()) {
        $palabra_id = $pal['palabra_id'];

        // Intentar actualizar
        $sqlUp = "UPDATE interesusuario SET puntuacion = puntuacion + 1, ultima_interaccion = NOW()
                  WHERE usuario = ? AND palabra_id = ?";
        $stmtUp = $conn->prepare($sqlUp);
        $stmtUp->bind_param("ii", $id, $palabra_id);
        $stmtUp->execute();

        if ($stmtUp->affected_rows === 0) {
            $sqlIns = "INSERT INTO interesusuario (usuario, palabra_id, puntuacion) VALUES (?, ?, 1)";
            $stmtIns = $conn->prepare($sqlIns);
            $stmtIns->bind_param("ii", $id, $palabra_id);
            $stmtIns->execute();
            $stmtIns->close();
        }

        $stmtUp->close();
    }

    $stmt->close();
}

// Obtener información del autor del post (usuario o página)
if ($tipoPost == 2) {
    $sql = "SELECT nombre, imagen AS foto FROM paginas WHERE id = ?";
} else {
    $sql = "SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $autorPost);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $autor = $result->fetch_assoc();
    $post['nombre'] = $autor['nombre'];
    $post['apellidos'] = $autor['apellidos'] ?? '';
    $post['foto'] = $autor['foto'];
} else {
    $post['nombre'] = 'Desconocido';
    $post['apellidos'] = '';
    $post['foto'] = 'img/default.jpg';
}
$stmt->close();

// Verificar si el usuario actual dio like
$sql = "SELECT 1 FROM likespost WHERE usuario = ? AND post = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpost);
$stmt->execute();
$result = $stmt->get_result();
$post['liked'] = $result->num_rows > 0;
$stmt->close();

// Cargar imágenes del post
$sql = "SELECT ruta FROM imagenes WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();
$imagenes = [];
while ($img = $result->fetch_assoc()) {
    $imagenes[] = $img['ruta'];
}
$post['imagenes'] = $imagenes;
$stmt->close();

// Si es un post compartido, obtener datos del post original
if ($post['tipo'] == 3 && !empty($post['idcompartido'])) {
    $sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post['idcompartido']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $compartido = $result->fetch_assoc();
        $stmt->close();

        if ($compartido['tipo'] == 2) {
            $sql = "SELECT nombre, imagen AS foto FROM paginas WHERE id = ?";
        } else {
            $sql = "SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $compartido['usuario']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $autorC = $result->fetch_assoc();
            $compartido['nombre'] = $autorC['nombre'];
            $compartido['apellidos'] = $autorC['apellidos'] ?? '';
            $compartido['foto'] = $autorC['foto'];
        } else {
            $compartido['nombre'] = 'Desconocido';
            $compartido['apellidos'] = '';
            $compartido['foto'] = 'img/default.jpg';
        }
        $stmt->close();

        // Imágenes del post compartido
        $sql = "SELECT ruta FROM imagenes WHERE post_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $compartido['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $imagenesComp = [];
        while ($img = $result->fetch_assoc()) {
            $imagenesComp[] = $img['ruta'];
        }
        $compartido['imagenes'] = $imagenesComp;
        $stmt->close();

        $post['compartido'] = $compartido;
    } else {
        $post['compartido'] = null;
    }
}

// Obtener comentarios
$sql = "SELECT c.id, c.usuario AS idusuario, u.nombre, u.apellidos, u.foto, c.texto, c.fecha, c.idrespuesta,
               IF(lc.id IS NOT NULL, 1, 0) AS liked
        FROM comentariospost c
        JOIN usuarios u ON c.usuario = u.id
        LEFT JOIN likescomentarios lc ON lc.usuario = ? AND lc.comentario = c.id
        WHERE c.post = ?
        ORDER BY c.fecha ASC";
$stmt = $conn->prepare($sql);
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
