<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idproducto = $_POST['idproducto'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? '';
$categoria = trim($_POST['categoria'] ?? '');
$estado = $_POST['estado'] ?? 0;
$latitud = $_POST['latitud'] ?? null;
$longitud = $_POST['longitud'] ?? null;

// Validaciones básicas
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

if (empty($nombre) || empty($descripcion) || !is_numeric($precio)) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios.']);
    exit;
}

// Verificar que el producto exista y sea del usuario
$sql = "SELECT id FROM productos WHERE id = ? AND usuario = ? AND vendido = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idproducto, $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado o ya está vendido.']);
    exit;
}
$stmt->close();

// Actualizar el producto
$sql = "UPDATE productos 
        SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, estado = ?, latitud = ?, longitud = ? 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdssdsi", $nombre, $descripcion, $precio, $categoria, $estado, $latitud, $longitud, $idproducto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Producto actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el producto.']);
}

$stmt->close();
$conn->close();
?>
