<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$pais = $_POST['pais'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

$sql = "UPDATE usuarios SET nombre = ?, apellidos = ?, pais = ?, ciudad = ?, descripcion = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $nombre, $apellidos, $pais, $ciudad, $descripcion, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Perfil actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el perfil.']);
}

$stmt->close();
$conn->close();
?>
