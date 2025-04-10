<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Verificar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

$sql = "SELECT p.id AS idSolicitud, u.id, u.nombre, u.apellidos, u.foto
        FROM peticionesamistad p
        JOIN usuarios u ON p.solicitante = u.id
        WHERE p.solicitado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}

echo json_encode(['success' => true, 'solicitudes' => $solicitudes]);

$stmt->close();
$conn->close();
?>
