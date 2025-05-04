<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
    exit;
}

$sql = "SELECT COUNT(*) AS total FROM mensajesdirectos 
        WHERE receptor = ? AND leido = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'hay' => intval($data['total']) > 0
]);

$stmt->close();
$conn->close();
?>
