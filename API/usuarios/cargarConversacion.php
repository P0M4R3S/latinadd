<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$id_usuario = $_POST['idusuario'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Obtener datos de los participantes
$sql_participantes = "SELECT id, nombre, apellidos, foto FROM usuarios WHERE id IN (?, ?)";
$stmt_participantes = $conn->prepare($sql_participantes);
$stmt_participantes->bind_param("ii", $id, $id_usuario);
$stmt_participantes->execute();
$result_participantes = $stmt_participantes->get_result();

$participantes = [];
while ($row = $result_participantes->fetch_assoc()) {
    $participantes[$row['id']] = [
        'id_usuario' => $row['id'],
        'nombre' => $row['nombre'],
        'apellidos' => $row['apellidos'],
        'imagen' => $row['foto']
    ];
}
$stmt_participantes->close();

// Obtener mensajes de la conversación
$sql_mensajes = "SELECT id, emisor, receptor, mensaje, fecha, leido 
                 FROM mensajesdirectos 
                 WHERE (emisor = ? AND receptor = ?) OR (emisor = ? AND receptor = ?) 
                 ORDER BY fecha ASC";
$stmt_mensajes = $conn->prepare($sql_mensajes);
$stmt_mensajes->bind_param("iiii", $id, $id_usuario, $id_usuario, $id);
$stmt_mensajes->execute();
$result_mensajes = $stmt_mensajes->get_result();

$mensajes = [];
while ($row = $result_mensajes->fetch_assoc()) {
    $mensajes[] = $row;
}
$stmt_mensajes->close();

// Marcar los mensajes recibidos como leídos
$sql_update = "UPDATE mensajesdirectos SET leido = 1 WHERE receptor = ? AND emisor = ? AND leido = 0";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ii", $id, $id_usuario);
$stmt_update->execute();
$stmt_update->close();

$conn->close();

// Enviar respuesta
echo json_encode([
    'success' => true,
    'participantes' => $participantes,
    'mensajes' => $mensajes
]);
?>
