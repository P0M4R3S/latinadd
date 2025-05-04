<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$limite = 10;

if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
    exit;
}

$conn->set_charset("utf8");

// Selección con agrupación
$sql = "
SELECT 
    tipo, 
    post, 
    COUNT(*) AS cantidad,
    MAX(fecha) AS fecha,
    GROUP_CONCAT(otroUsuario) AS usuarios
FROM notificaciones
WHERE usuario = ?
GROUP BY tipo, post
ORDER BY fecha DESC
LIMIT ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $limite);
$stmt->execute();
$result = $stmt->get_result();

$notificaciones = [];

while ($row = $result->fetch_assoc()) {
    $usuarios = explode(',', $row['usuarios']);
    $primerUsuario = intval($usuarios[0] ?? 0);

    // Obtener nombre del primer usuario
    $nombre = null;
    if ($primerUsuario > 0) {
        $sqlU = "SELECT nombre, apellidos FROM usuarios WHERE id = ?";
        $stmtU = $conn->prepare($sqlU);
        $stmtU->bind_param("i", $primerUsuario);
        $stmtU->execute();
        $resU = $stmtU->get_result();
        if ($rowU = $resU->fetch_assoc()) {
            $nombre = $rowU['nombre'] . ' ' . $rowU['apellidos'];
        }
        $stmtU->close();
    }

    $notificaciones[] = [
        'tipo' => intval($row['tipo']),
        'cantidad' => intval($row['cantidad']),
        'fecha' => $row['fecha'],
        'post' => $row['post'],
        'nombreUsuario' => $nombre,
        'otroUsuario' => $primerUsuario // ahora se incluye correctamente
    ];
}

echo json_encode(['success' => true, 'notificaciones' => $notificaciones]);

$stmt->close();
$conn->close();
?>
