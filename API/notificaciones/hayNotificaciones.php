<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida']);
    exit;
}

$sql = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario = ? AND leido = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'hay' => $row['total'] > 0
]);

$stmt->close();
$conn->close();
?>
