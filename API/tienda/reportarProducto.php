<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idproducto = $_POST['idproducto'] ?? '';
$motivo = trim($_POST['motivo'] ?? '');
$texto = trim($_POST['texto'] ?? '');

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el producto existe
$sql = "SELECT id FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El producto no existe.']);
    exit;
}
$stmt->close();

// Verificar si el usuario ya reportó este producto
$sql = "SELECT id FROM reportesproducto WHERE reportador = ? AND producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idproducto);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Ya has reportado este producto.']);
    exit;
}
$stmt->close();

// Insertar reporte
$sql = "INSERT INTO reportesproducto (reportador, producto, motivo, texto, fecha, estado) 
        VALUES (?, ?, ?, ?, NOW(), 'pendiente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $id, $idproducto, $motivo, $texto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Reporte enviado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el reporte.']);
}

$stmt->close();
$conn->close();
?>
