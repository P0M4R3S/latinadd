<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpeticion = $_POST['idpeticion'] ?? '';
$respuesta = $_POST['respuesta'] ?? ''; // 1 = aceptar, otro = rechazar

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Verificar que la solicitud exista y que el usuario sea el receptor
$sql = "SELECT id, solicitante, solicitado FROM peticionesamistad WHERE id = ? AND solicitado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idpeticion, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No se encontró la petición de amistad.']);
    exit;
}

$row = $result->fetch_assoc();
$usuario1 = $row['solicitante'];
$usuario2 = $row['solicitado'];

// Si la respuesta es 1 (aceptar), se inserta la amistad
if ($respuesta == 1) {
    $sql_insert = "INSERT INTO amigos (usuario1, usuario2) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $usuario1, $usuario2);
    $stmt_insert->execute();
    $stmt_insert->close();
}

// Eliminar la solicitud
$sql_delete = "DELETE FROM peticionesamistad WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $idpeticion);
$stmt_delete->execute();
$stmt_delete->close();

echo json_encode([
    'success' => true,
    'mensaje' => $respuesta == 1 ? 'Amistad aceptada.' : 'Solicitud rechazada.'
]);

$stmt->close();
$conn->close();
?>
