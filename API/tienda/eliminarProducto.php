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

// Verificar que el producto existe y es del usuario
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

// Obtener imágenes asociadas
$sql = "SELECT ruta FROM imagenes WHERE post_id = ? AND tipo = 'tienda'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ruta = '../' . $row['ruta'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}
$stmt->close();

// Eliminar las imágenes de la base de datos
$sql = "DELETE FROM imagenes WHERE post_id = ? AND tipo = 'tienda'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$stmt->close();

// Eliminar el producto
$sql = "DELETE FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado correctamente.']);
$conn->close();
?>
