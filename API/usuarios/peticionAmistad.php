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

// No permitir solicitudes a uno mismo
if ($id == $id_receptor) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'No puedes enviarte una solicitud a ti mismo.'
    ]);
    exit;
}

// Verificar que el usuario receptor exista
$sql = "SELECT id FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_receptor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'El usuario no existe.'
    ]);
    exit;
}

// Verificar si ya existe una solicitud pendiente entre ambos
$sql = "SELECT id FROM peticionesamistad WHERE 
        (solicitante = ? AND solicitado = ?) OR 
        (solicitante = ? AND solicitado = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id, $id_receptor, $id_receptor, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Ya existe una solicitud de amistad entre estos usuarios.'
    ]);
    exit;
}

// Insertar solicitud
$sql = "INSERT INTO peticionesamistad (solicitante, solicitado) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $id_receptor);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'mensaje' => 'Solicitud de amistad enviada.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al enviar la solicitud.'
    ]);
}

$stmt->close();
$conn->close();
?>
