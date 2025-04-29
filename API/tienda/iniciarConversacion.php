<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idproducto = $_POST['idproducto'] ?? '';
$idvendedor = $_POST['idvendedor'] ?? '';
$mensaje = trim($_POST['mensaje'] ?? '');

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar que el mensaje no esté vacío
if (empty($mensaje)) {
    echo json_encode(['success' => false, 'mensaje' => 'El mensaje no puede estar vacío.']);
    exit;
}

// No puede iniciar conversación consigo mismo
if ($id == $idvendedor) {
    echo json_encode(['success' => false, 'mensaje' => 'No puedes enviarte mensajes a ti mismo.']);
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

// Buscar si ya existe conversación entre ambos por este producto
$sql = "SELECT id FROM conversaciones 
        WHERE producto = ? AND 
        ((usuario1 = ? AND usuario2 = ?) OR (usuario1 = ? AND usuario2 = ?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $idproducto, $id, $idvendedor, $idvendedor, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $idconversacion = $row['id'];
} else {
    // Crear nueva conversación
    $sql = "INSERT INTO conversaciones (usuario1, usuario2, producto) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id, $idvendedor, $idproducto);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'mensaje' => 'Error al crear la conversación.']);
        exit;
    }
    $idconversacion = $stmt->insert_id;
    $stmt->close();
}

// Insertar mensaje en la conversación
$sql = "INSERT INTO mensajestienda (usuario, conversacion, texto, fecha, leido) 
        VALUES (?, ?, ?, NOW(), 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id, $idconversacion, $mensaje);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar el mensaje.']);
    exit;
}

echo json_encode([
    'success' => true,
    'mensaje' => 'Conversación iniciada correctamente.',
    'idconversacion' => $idconversacion
]);

$stmt->close();
$conn->close();
?>
