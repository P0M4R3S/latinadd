<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Contar cuántos mensajes no leídos tiene el usuario
$sql = "SELECT COUNT(*) AS sin_leer FROM mensajesdirectos WHERE receptor = ? AND leido = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'sin_leer' => $row['sin_leer']
]);

$stmt->close();
$conn->close();
?>
