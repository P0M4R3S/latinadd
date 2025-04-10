<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idconversacion = $_POST['idconversacion'] ?? '';
$texto = trim($_POST['texto'] ?? '');

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar mensaje
if (empty($texto)) {
    echo json_encode(['success' => false, 'mensaje' => 'El mensaje no puede estar vacío.']);
    exit;
}

// Verificar que el usuario pertenece a la conversación
$sql = "SELECT id FROM conversaciones 
        WHERE id = ? AND (usuario1 = ? OR usuario2 = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $idconversacion, $id, $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No tienes permiso para enviar mensajes en esta conversación.']);
    exit;
}
$stmt->close();

// Insertar mensaje
$sql = "INSERT INTO mensajestienda (usuario, conversacion, texto, fecha, leido) 
        VALUES (?, ?, ?, NOW(), 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id, $idconversacion, $texto);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'mensaje' => 'Mensaje enviado correctamente.',
        'idmensaje' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al enviar el mensaje.'
    ]);
}

$stmt->close();
$conn->close();
?>
