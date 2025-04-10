<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Consulta para obtener amigos desde la tabla `amigos`
$sql = "SELECT u.id, u.nombre, u.apellidos, u.foto
        FROM amigos a
        JOIN usuarios u ON (u.id = a.usuario1 OR u.id = a.usuario2)
        WHERE (a.usuario1 = ? OR a.usuario2 = ?) AND u.id != ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error en la consulta SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iii", $idusuario, $idusuario, $idusuario);
$stmt->execute();
$result = $stmt->get_result();

$amigos = [];
while ($row = $result->fetch_assoc()) {
    $amigos[] = $row;
}

echo json_encode(['success' => true, 'amigos' => $amigos]);

$stmt->close();
$conn->close();
?>
