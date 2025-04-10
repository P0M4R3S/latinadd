<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

// Obtener datos POST
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idreceptor = $_POST['idreceptor'] ?? '';
$texto = $_POST['texto'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Verificar si hay bloqueo entre ambos usuarios
$sql = "SELECT id FROM bloqueosusuarios 
        WHERE (bloqueador = ? AND bloqueado = ?) 
           OR (bloqueador = ? AND bloqueado = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id, $idreceptor, $idreceptor, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No puedes enviar mensajes a este usuario.']);
    exit;
}

// Limitar la longitud del mensaje
$maxLength = 1000;
if (strlen($texto) > $maxLength) {
    echo json_encode(['success' => false, 'mensaje' => "El mensaje es demasiado largo (máx. $maxLength caracteres)."]);
    exit;
}

// Insertar mensaje
$sql = "INSERT INTO mensajesdirectos (emisor, receptor, mensaje) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id, $idreceptor, $texto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Mensaje enviado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el mensaje.']);
}

$stmt->close();
$conn->close();
?>
