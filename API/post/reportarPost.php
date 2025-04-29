<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';
$motivo = trim($_POST['motivo'] ?? '');
$texto = trim($_POST['texto'] ?? '');

// Verificar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el post exista
$sql = "SELECT id FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}
$stmt->close();

// Evitar reportes duplicados opcionalmente
$sql = "SELECT id FROM reportespost WHERE reportador = ? AND post = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpost);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Ya has reportado este post.']);
    exit;
}
$stmt->close();

// Insertar reporte
$sql = "INSERT INTO reportespost (reportador, post, motivo, texto, fecha, estado)
        VALUES (?, ?, ?, ?, NOW(), 'pendiente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $id, $idpost, $motivo, $texto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Reporte enviado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el reporte.']);
}

$stmt->close();
$conn->close();
?>
