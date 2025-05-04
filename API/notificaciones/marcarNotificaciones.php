<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

// Recoger datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Marcar todas las notificaciones como leídas
$sql = "UPDATE notificaciones SET leido = 1 WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Notificaciones marcadas como leídas.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar notificaciones.']);
}

$stmt->close();
$conn->close();
?>
