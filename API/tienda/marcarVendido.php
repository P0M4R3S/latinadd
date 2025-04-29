<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idproducto = $_POST['idproducto'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el producto exista y sea del usuario
$sql = "SELECT id FROM productos WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idproducto, $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado o no te pertenece.']);
    exit;
}
$stmt->close();

// Marcar como vendido
$sql = "UPDATE productos SET vendido = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Producto marcado como vendido.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el producto.']);
}

$stmt->close();
$conn->close();
?>
