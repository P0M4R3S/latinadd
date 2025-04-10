<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? ''; // Usuario a desbloquear

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// No puede desbloquearse a sí mismo
if ($id == $idusuario) {
    echo json_encode(['success' => false, 'mensaje' => 'No puedes desbloquearte a ti mismo.']);
    exit;
}

// Verificar si el bloqueo existe
$sql_check = "SELECT id FROM bloqueosusuarios WHERE bloqueador = ? AND bloqueado = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $id, $idusuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Este usuario no está bloqueado.']);
    exit;
}

// Eliminar el bloqueo
$sql_delete = "DELETE FROM bloqueosusuarios WHERE bloqueador = ? AND bloqueado = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("ii", $id, $idusuario);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Usuario desbloqueado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al desbloquear el usuario.']);
}

$stmt->close();
$conn->close();
?>
