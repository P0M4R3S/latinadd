<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id       = $_POST['id'] ?? '';
$token    = $_POST['token'] ?? '';
$tipo     = intval($_POST['tipo'] ?? 0);
$receptor = isset($_POST['receptor']) ? intval($_POST['receptor']) : 0;
$post     = isset($_POST['post']) ? intval($_POST['post']) : null;

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida']);
    exit;
}

$tiposValidos = [1, 2, 3, 4, 5];
if (!in_array($tipo, $tiposValidos)) {
    echo json_encode(['success' => false, 'mensaje' => 'Tipo de notificación no válido']);
    exit;
}

// === MANEJO AUTOMÁTICO DE receptor en caso tipo 2 (comentario en post) ===
if ($tipo === 2) {
    if (!$post) {
        echo json_encode(['success' => false, 'mensaje' => 'Falta ID del post para tipo 2']);
        exit;
    }

    // Buscar autor del post
    $sqlAutor = "SELECT usuario FROM posts WHERE id = ?";
    $stmtAutor = $conn->prepare($sqlAutor);
    $stmtAutor->bind_param("i", $post);
    $stmtAutor->execute();
    $resAutor = $stmtAutor->get_result();
    if ($resAutor->num_rows === 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Post no encontrado']);
        exit;
    }

    $fila = $resAutor->fetch_assoc();
    $receptor = intval($fila['usuario']);

    // Evitar auto-notificación
    if ($receptor === intval($id)) {
        echo json_encode(['success' => false, 'mensaje' => 'No se notifica al autor']);
        exit;
    }

    $stmtAutor->close();
}

// Validar receptor final
if ($receptor <= 0 || $receptor == $id) {
    echo json_encode(['success' => false, 'mensaje' => 'Receptor inválido']);
    exit;
}

// Insertar notificación
$sql = "INSERT INTO notificaciones (usuario, tipo, mensaje, post, otroUsuario)
        VALUES (?, ?, NULL, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error en SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iiii", $receptor, $tipo, $post, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Notificación creada']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al insertar la notificación']);
}

$stmt->close();
$conn->close();
?>
