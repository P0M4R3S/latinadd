<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = intval($_POST['idpost'] ?? 0);

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Obtener autor del post
$sql = "SELECT usuario FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al preparar consulta: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}
$fila = $result->fetch_assoc();
$autorPost = intval($fila['usuario']);
$stmt->close();

// Verificar si ya dio like
$sql = "SELECT id FROM likespost WHERE usuario = ? AND post = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpost);
$stmt->execute();
$result = $stmt->get_result();
$yaLike = $result->num_rows > 0;
$stmt->close();

if ($yaLike) {
    // Eliminar like
    $sql = "DELETE FROM likespost WHERE usuario = ? AND post = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idpost);
    $stmt->execute();
    $stmt->close();

    $sql = "UPDATE posts SET likes = likes - 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'liked' => false, 'mensaje' => 'Like eliminado.']);

} else {
    // Agregar like
    $sql = "INSERT INTO likespost (usuario, post) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idpost);
    $stmt->execute();
    $stmt->close();

    $sql = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    // Notificar si el post es de otro usuario
    if ($autorPost !== intval($id)) {
        $sqlNotif = "INSERT INTO notificaciones (usuario, tipo, mensaje, post, otroUsuario)
                     VALUES (?, 5, NULL, ?, ?)";
        $stmtNotif = $conn->prepare($sqlNotif);
        $stmtNotif->bind_param("iii", $autorPost, $idpost, $id);
        $stmtNotif->execute();
        $stmtNotif->close();
    }

    echo json_encode(['success' => true, 'liked' => true, 'mensaje' => 'Like agregado.']);
}

$conn->close();
?>
