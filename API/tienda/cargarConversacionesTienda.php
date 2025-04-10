<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Buscar conversaciones del usuario que no están eliminadas para él
$sql = "SELECT id, usuario1, usuario2, producto, eliminado1, eliminado2 
        FROM conversaciones 
        WHERE (usuario1 = ? AND eliminado1 = 0) OR (usuario2 = ? AND eliminado2 = 0)
        ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $id);
$stmt->execute();
$result = $stmt->get_result();

$conversaciones = [];

while ($row = $result->fetch_assoc()) {
    $idconversacion = $row['id'];
    $idproducto = $row['producto'];
    $idotro = ($row['usuario1'] == $id) ? $row['usuario2'] : $row['usuario1'];

    // Obtener info del otro usuario
    $sqlU = "SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?";
    $stmtU = $conn->prepare($sqlU);
    $stmtU->bind_param("i", $idotro);
    $stmtU->execute();
    $resultU = $stmtU->get_result();
    $usuario = $resultU->fetch_assoc();
    $stmtU->close();

    // Obtener info del producto
    $sqlP = "SELECT nombre, precio, categoria FROM productos WHERE id = ?";
    $stmtP = $conn->prepare($sqlP);
    $stmtP->bind_param("i", $idproducto);
    $stmtP->execute();
    $resultP = $stmtP->get_result();
    $producto = $resultP->fetch_assoc();
    $stmtP->close();

    // Obtener último mensaje
    $sqlM = "SELECT texto, fecha, usuario FROM mensajestienda 
             WHERE conversacion = ? 
             ORDER BY fecha DESC LIMIT 1";
    $stmtM = $conn->prepare($sqlM);
    $stmtM->bind_param("i", $idconversacion);
    $stmtM->execute();
    $resultM = $stmtM->get_result();
    $ultimo = $resultM->fetch_assoc();
    $stmtM->close();

    // Contar mensajes no leídos
    $sqlL = "SELECT COUNT(*) as sin_leer FROM mensajestienda 
             WHERE conversacion = ? AND usuario != ? AND leido = 0";
    $stmtL = $conn->prepare($sqlL);
    $stmtL->bind_param("ii", $idconversacion, $id);
    $stmtL->execute();
    $resultL = $stmtL->get_result();
    $sinLeer = $resultL->fetch_assoc()['sin_leer'] ?? 0;
    $stmtL->close();

    $conversaciones[] = [
        'idconversacion' => $idconversacion,
        'usuario' => $usuario,
        'producto' => $producto,
        'ultimo_mensaje' => $ultimo,
        'sin_leer' => $sinLeer
    ];
}

echo json_encode(['success' => true, 'conversaciones' => $conversaciones]);
$conn->close();
?>
