<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Obtener los datos del usuario
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idcomentario = $_POST['idcomentario'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el comentario exista
$sql = "SELECT id FROM comentariospost WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idcomentario);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El comentario no existe.']);
    exit;
}
$stmt->close();

// Comprobar si ya se ha dado like
$sql = "SELECT id FROM likescomentarios WHERE usuario = ? AND comentario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idcomentario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Ya tiene like, eliminarlo
    $stmt->close();
    $sql = "DELETE FROM likescomentarios WHERE usuario = ? AND comentario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idcomentario);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'liked' => false, 'mensaje' => 'Like eliminado.']);
} else {
    // No tiene like, insertarlo
    $stmt->close();
    $sql = "INSERT INTO likescomentarios (usuario, comentario, fecha) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idcomentario);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'liked' => true, 'mensaje' => 'Like añadido.']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al dar like.']);
    }
    $stmt->close();
}

$conn->close();
