<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Obtener lista de usuarios con los que hay conversación y última fecha
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
$res = $stmt->get_result();

$conversaciones = [];

while ($row = $res->fetch_assoc()) {
    $otro = $row['otro_usuario'];

    // Obtener datos del usuario
    $sqlUser = "SELECT nombre, apellidos, foto FROM usuarios WHERE id = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $otro);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    $user = $resUser->fetch_assoc();
    $stmtUser->close();

    // Último mensaje
    $sqlUltimo = "
        SELECT mensaje, fecha FROM mensajesdirectos 
        WHERE (emisor = ? AND receptor = ?) OR (emisor = ? AND receptor = ?)
        ORDER BY fecha DESC LIMIT 1
    ";
    $stmtUltimo = $conn->prepare($sqlUltimo);
    $stmtUltimo->bind_param("iiii", $id, $otro, $otro, $id);
    $stmtUltimo->execute();
    $resUltimo = $stmtUltimo->get_result();
    $ultimo = $resUltimo->fetch_assoc();
    $stmtUltimo->close();

    // Mensajes no leídos del otro hacia mí
    $sqlNoLeidos = "
        SELECT COUNT(*) AS sinleer FROM mensajesdirectos 
        WHERE emisor = ? AND receptor = ? AND leido = 0
    ";
    $stmtLeido = $conn->prepare($sqlNoLeidos);
    $stmtLeido->bind_param("ii", $otro, $id);
    $stmtLeido->execute();
    $resLeido = $stmtLeido->get_result();
    $sinleer = $resLeido->fetch_assoc()['sinleer'] > 0;
    $stmtLeido->close();

    $conversaciones[] = [
        'id' => $otro,
        'nombre' => $user['nombre'] . ' ' . $user['apellidos'],
        'foto' => $user['foto'] ?: 'img/default.png',
        'ultimo' => $ultimo['mensaje'] ?? '',
        'fecha' => $ultimo['fecha'] ?? '',
        'sinleer' => $sinleer
    ];
}

echo json_encode([
    'success' => true,
    'conversaciones' => $conversaciones
]);

$conn->close();
?>
