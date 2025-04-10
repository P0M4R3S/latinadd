<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idconversacion = $_POST['idconversacion'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar si el usuario es parte de la conversación
$sql = "SELECT usuario1, usuario2 FROM conversaciones WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idconversacion);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Conversación no encontrada.']);
    exit;
}

$row = $result->fetch_assoc();
$esUsuario1 = ($row['usuario1'] == $id);
$esUsuario2 = ($row['usuario2'] == $id);

if (!$esUsuario1 && !$esUsuario2) {
    echo json_encode(['success' => false, 'mensaje' => 'No tienes acceso a esta conversación.']);
    exit;
}

$campo = $esUsuario1 ? 'eliminado1' : 'eliminado2';
$sql = "UPDATE conversaciones SET $campo = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idconversacion);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Conversación eliminada para ti.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar la conversación.']);
}

$stmt->close();
$conn->close();
?>
