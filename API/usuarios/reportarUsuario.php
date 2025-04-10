<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$texto = $_POST['texto'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Evitar que un usuario se reporte a sí mismo
if ($id == $idusuario) {
    echo json_encode(['success' => false, 'mensaje' => 'No puedes reportarte a ti mismo.']);
    exit;
}

// Insertar el reporte en la base de datos
$sql = "INSERT INTO reportesusuario (reportador, reportado, motivo, texto, fecha) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $id, $idusuario, $motivo, $texto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Reporte enviado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el reporte.']);
}

$stmt->close();
$conn->close();
?>
