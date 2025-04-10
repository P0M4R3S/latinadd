<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? ''; // Usuario a bloquear

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// No puede bloquearse a sí mismo
if ($id == $idusuario) {
    echo json_encode(['success' => false, 'mensaje' => 'No puedes bloquearte a ti mismo.']);
    exit;
}

// Verificar si ya está bloqueado
$sql = "SELECT id FROM bloqueosusuarios WHERE bloqueador = ? AND bloqueado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idusuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Este usuario ya está bloqueado.']);
    exit;
}

// Insertar el bloqueo
$sql = "INSERT INTO bloqueosusuarios (bloqueador, bloqueado) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idusuario);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Usuario bloqueado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al bloquear el usuario.']);
}

$stmt->close();
$conn->close();
?>
