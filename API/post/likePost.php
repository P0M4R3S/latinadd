<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el post existe
$sql = "SELECT id FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}
$stmt->close();

// Verificar si ya ha dado like
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

    // Restar 1 al contador
    $sql = "UPDATE posts SET likes = likes - 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'liked' => false, 'mensaje' => 'Like eliminado.']);
} else {
    // Insertar like
    $sql = "INSERT INTO likespost (usuario, post) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idpost);
    $stmt->execute();
    $stmt->close();

    // Sumar 1 al contador
    $sql = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    // Actualizar métricas del usuario con las palabras clave del post
    $sql = "SELECT palabra_id FROM palabraspost WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $palabra_id = $row['palabra_id'];

        // Intentar actualizar
        $sql_up = "UPDATE metricasusuario SET valor = valor + 1 WHERE usuario_id = ? AND palabra_id = ?";
        $stmt_up = $conn->prepare($sql_up);
        $stmt_up->bind_param("ii", $id, $palabra_id);
        $stmt_up->execute();

        // Si no actualizó nada, es que no existía → insertar
        if ($stmt_up->affected_rows === 0) {
            $sql_insert = "INSERT INTO metricasusuario (usuario_id, palabra_id, valor) VALUES (?, ?, 1)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $id, $palabra_id);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_up->close();
    }

    $stmt->close();

    echo json_encode(['success' => true, 'liked' => true, 'mensaje' => 'Like agregado.']);
}

$conn->close();
?>
