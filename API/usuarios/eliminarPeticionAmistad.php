<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

// Obtener los datos enviados por POST
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$id_receptor = $_POST['idusuario'] ?? '';

// Verificar sesión válida
if (!validarSesion($id, $token)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
    exit;
}

// Verificar si la solicitud de amistad existe
$sql = "SELECT id FROM peticionesamistad WHERE 
        (solicitante = ? AND solicitado = ?) OR 
        (solicitante = ? AND solicitado = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id, $id_receptor, $id_receptor, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'No existe una solicitud de amistad entre estos usuarios.'
    ]);
    exit;
}

// Eliminar la solicitud de amistad
$sql = "DELETE FROM peticionesamistad WHERE 
        (solicitante = ? AND solicitado = ?) OR 
        (solicitante = ? AND solicitado = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id, $id_receptor, $id_receptor, $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'mensaje' => 'Solicitud de amistad eliminada correctamente.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al eliminar la solicitud.'
    ]);
}

$stmt->close();
$conn->close();
?>
