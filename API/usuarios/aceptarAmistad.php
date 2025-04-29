<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id       = $_POST['id'] ?? '';
$token    = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? ''; // Este es el otro usuario, no la solicitud

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Verificar que existe una solicitud donde el usuario actual sea el solicitado
$sql = "SELECT id FROM peticionesamistad 
        WHERE solicitante = ? AND solicitado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idusuario, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No se encontró la solicitud de amistad.']);
    exit;
}

$idpeticion = $result->fetch_assoc()['id'];

// Insertar amistad (ordenando IDs para evitar duplicados)
$u1 = min($id, $idusuario);
$u2 = max($id, $idusuario);

$sql_insert = "INSERT INTO amigos (usuario1, usuario2) VALUES (?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ii", $u1, $u2);
if (!$stmt_insert->execute()) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al insertar amistad.']);
    exit;
}
$stmt_insert->close();

// Eliminar solicitud de amistad
$sql_delete = "DELETE FROM peticionesamistad WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $idpeticion);
$stmt_delete->execute();
$stmt_delete->close();

echo json_encode([
    'success' => true,
    'mensaje' => 'Amistad aceptada.'
]);

$stmt->close();
$conn->close();
?>
