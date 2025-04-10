<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Consulta para obtener las conversaciones recientes (último mensaje por usuario)
$sql = "
    SELECT 
        IF(emisor = ?, receptor, emisor) AS otro_usuario,
        MAX(fecha) AS ultima_fecha
    FROM mensajesdirectos
    WHERE emisor = ? OR receptor = ?
    GROUP BY otro_usuario
    ORDER BY ultima_fecha DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id, $id, $id);
$stmt->execute();
$result = $stmt->get_result();

$conversaciones = [];

while ($row = $result->fetch_assoc()) {
    $otro_usuario = $row['otro_usuario'];

    // Obtener datos del otro usuario
    $sql_user = "SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $otro_usuario);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_data = $result_user->fetch_assoc();
    $stmt_user->close();

    // Obtener último mensaje
    $sql_msj = "SELECT mensaje, fecha, emisor, leido 
                FROM mensajesdirectos 
                WHERE (emisor = ? AND receptor = ?) OR (emisor = ? AND receptor = ?) 
                ORDER BY fecha DESC LIMIT 1";
    $stmt_msj = $conn->prepare($sql_msj);
    $stmt_msj->bind_param("iiii", $id, $otro_usuario, $otro_usuario, $id);
    $stmt_msj->execute();
    $result_msj = $stmt_msj->get_result();
    $mensaje = $result_msj->fetch_assoc();
    $stmt_msj->close();

    // Verificar si hay mensajes no leídos de esta persona
    $sql_leido = "SELECT COUNT(*) AS sin_leer FROM mensajesdirectos 
                  WHERE emisor = ? AND receptor = ? AND leido = 0";
    $stmt_leido = $conn->prepare($sql_leido);
    $stmt_leido->bind_param("ii", $otro_usuario, $id);
    $stmt_leido->execute();
    $result_leido = $stmt_leido->get_result();
    $row_leido = $result_leido->fetch_assoc();
    $sin_leer = $row_leido['sin_leer'];
    $stmt_leido->close();

    $conversaciones[] = [
        'id_usuario' => $otro_usuario,
        'nombre' => $user_data['nombre'],
        'apellidos' => $user_data['apellidos'],
        'foto' => $user_data['foto'],
        'mensaje' => $mensaje['mensaje'],
        'fecha' => $mensaje['fecha'],
        'emisor' => $mensaje['emisor'],
        'leido' => $mensaje['leido'],
        'sin_leer' => $sin_leer
    ];
}

$conn->close();

echo json_encode([
    'success' => true,
    'conversaciones' => $conversaciones
]);
?>
