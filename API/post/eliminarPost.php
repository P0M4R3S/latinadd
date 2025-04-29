<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';

// Verificar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el post existe y pertenece al usuario
$sql = "SELECT id FROM posts WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idpost, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe o no te pertenece.']);
    exit;
}
$stmt->close();

// Eliminar imágenes asociadas (archivos + registros)
$sql = "SELECT ruta FROM imagenes WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

while ($img = $result->fetch_assoc()) {
    $ruta = '../' . $img['ruta'];
    if (file_exists($ruta)) unlink($ruta);
}
$stmt->close();

// Eliminar registros de imágenes
$sql = "DELETE FROM imagenes WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$stmt->close();

// Eliminar comentarios
$sql = "DELETE FROM comentariospost WHERE post = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$stmt->close();

// Eliminar likes
$sql = "DELETE FROM likespost WHERE post = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$stmt->close();

// Eliminar el post
$sql = "DELETE FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Post eliminado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el post.']);
}
$stmt->close();

$conn->close();
?>
