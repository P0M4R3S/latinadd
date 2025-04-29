<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Autenticación inválida.'
    ]);
    exit;
}

// Obtener datos del perfil propio
$sql = "SELECT nombre, apellidos, nacimiento, pais, ciudad, descripcion, foto FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'usuario' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Usuario no encontrado.'
    ]);
}

$stmt->close();
$conn->close();
?>
