<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idconversacion = $_POST['idconversacion'] ?? '';
$motivo = trim($_POST['motivo'] ?? '');
$texto = trim($_POST['texto'] ?? '');

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar que el usuario pertenezca a la conversación
$sql = "SELECT id FROM conversaciones WHERE id = ? AND (usuario1 = ? OR usuario2 = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $idconversacion, $id, $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No tienes acceso a esta conversación.']);
    exit;
}
$stmt->close();

// Insertar el reporte
$sql = "INSERT INTO reportesconversacion (idreportador, idconversacion, motivo, texto) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $id, $idconversacion, $motivo, $texto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Reporte enviado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el reporte.']);
}

$stmt->close();
$conn->close();
?>
