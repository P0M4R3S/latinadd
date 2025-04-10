<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Verificar que la sesión es válida
if (!validarSesion($id, $token)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
    exit;
}

// Obtener los datos del usuario
$sql = "SELECT nombre, apellidos, pais, foto FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'nombre' => $row['nombre'],
        'apellidos' => $row['apellidos'],
        'pais' => $row['pais'],
        'foto' => $row['foto']
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
