<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpagina = $_POST['idpagina'] ?? '';
$motivo = trim($_POST['motivo'] ?? '');
$texto = trim($_POST['texto'] ?? '');

// Verificar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que la página existe
$sql = "SELECT id FROM paginas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La página no existe.']);
    exit;
}
$stmt->close();

// Verificar si ya fue reportada por el mismo usuario
$sql = "SELECT id FROM reportespagina WHERE reportador = ? AND pagina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Ya has reportado esta página.']);
    exit;
}
$stmt->close();

// Insertar reporte
$sql = "INSERT INTO reportespagina (reportador, pagina, motivo, texto, fecha, estado) 
        VALUES (?, ?, ?, ?, NOW(), 'pendiente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $id, $idpagina, $motivo, $texto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Reporte enviado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el reporte.']);
}

$stmt->close();
$conn->close();
?>
