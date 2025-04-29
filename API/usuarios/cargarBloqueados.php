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

$sql = "SELECT u.id, u.nombre, u.apellidos, u.foto
        FROM bloqueosusuarios b
        JOIN usuarios u ON b.bloqueado = u.id
        WHERE b.bloqueador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$bloqueados = [];
while ($row = $result->fetch_assoc()) {
    $bloqueados[] = $row;
}

echo json_encode(['success' => true, 'bloqueados' => $bloqueados]);

$stmt->close();
$conn->close();
?>
