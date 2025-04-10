<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idconversacion = $_POST['idconversacion'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el usuario forma parte de la conversación
$sql = "SELECT usuario1, usuario2, producto FROM conversaciones WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idconversacion);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Conversación no encontrada.']);
    exit;
}

$conversacion = $result->fetch_assoc();
$stmt->close();

// Validar que el usuario sea uno de los participantes
if ($conversacion['usuario1'] != $id && $conversacion['usuario2'] != $id) {
    echo json_encode(['success' => false, 'mensaje' => 'No tienes acceso a esta conversación.']);
    exit;
}

$idotro = ($conversacion['usuario1'] == $id) ? $conversacion['usuario2'] : $conversacion['usuario1'];
$idproducto = $conversacion['producto'];

// Obtener info del otro usuario
$sql = "SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idotro);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Obtener info del producto
$sql = "SELECT nombre, precio, categoria FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

// Obtener los mensajes
$sql = "SELECT usuario, texto, fecha, leido FROM mensajestienda 
        WHERE conversacion = ? 
        ORDER BY fecha ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idconversacion);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}
$stmt->close();

// Marcar mensajes como leídos
$sql = "UPDATE mensajestienda SET leido = 1 WHERE conversacion = ? AND usuario != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idconversacion, $id);
$stmt->execute();
$stmt->close();

// Enviar respuesta
echo json_encode([
    'success' => true,
    'conversacion' => [
        'id' => $idconversacion,
        'producto' => $producto,
        'usuario' => $usuario,
        'mensajes' => $mensajes
    ]
]);

$conn->close();
?>
