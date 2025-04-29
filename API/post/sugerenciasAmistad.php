<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Obtener datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
    exit;
}

// Obtener sugerencias excluyendo amigos, bloqueos y peticiones
$sql = "
SELECT u.id, u.nombre, u.apellidos, u.foto
FROM usuarios u
WHERE u.id != ?
  AND u.estado = 'activo'
  AND u.id NOT IN (
      SELECT usuario2 FROM amigos WHERE usuario1 = ?
      UNION
      SELECT usuario1 FROM amigos WHERE usuario2 = ?
  )
  AND u.id NOT IN (
      SELECT bloqueado FROM bloqueosusuarios WHERE bloqueador = ?
      UNION
      SELECT bloqueador FROM bloqueosusuarios WHERE bloqueado = ?
  )
  AND u.id NOT IN (
      SELECT solicitado FROM peticionesamistad WHERE solicitante = ?
      UNION
      SELECT solicitante FROM peticionesamistad WHERE solicitado = ?
  )
ORDER BY RAND()
LIMIT 10
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiiii", $id, $id, $id, $id, $id, $id, $id);
$stmt->execute();
$result = $stmt->get_result();

$sugerencias = [];
while ($row = $result->fetch_assoc()) {
    $sugerencias[] = $row;
}

echo json_encode([
    'success' => true,
    'usuarios' => $sugerencias
]);

$stmt->close();
$conn->close();
?>
