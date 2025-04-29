<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$filtro = $_POST['filtro'] ?? 'todos';
$indice = intval($_POST['indice'] ?? 1);
$limite = 20;

if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
    exit;
}

$offset = ($indice - 1) * $limite;

switch ($filtro) {
    case 'amigos':
        $sql = "SELECT u.id, u.nombre, u.foto, 'amigo' AS estado
                FROM amigos a
                JOIN usuarios u ON (u.id = a.usuario1 OR u.id = a.usuario2)
                WHERE (a.usuario1 = ? OR a.usuario2 = ?)
                  AND u.id != ?
                  AND u.id NOT IN (
                      SELECT bloqueado FROM bloqueosusuarios WHERE bloqueador = ?
                      UNION
                      SELECT bloqueador FROM bloqueosusuarios WHERE bloqueado = ?
                  )
                LIMIT ?, ?";
        $tipos = "iiiiiii";
        $params = [$id, $id, $id, $id, $id, $offset, $limite];
        break;

    case 'sugerencias':
        $sql = "SELECT u.id, u.nombre, u.foto,
                       CASE
                           WHEN p1.id IS NOT NULL THEN 'solicitado'
                           WHEN p2.id IS NOT NULL THEN 'recibido'
                           ELSE 'ninguno'
                       END AS estado
                FROM usuarios u
                LEFT JOIN peticionesamistad p1 ON p1.solicitante = ? AND p1.solicitado = u.id
                LEFT JOIN peticionesamistad p2 ON p2.solicitante = u.id AND p2.solicitado = ?
                WHERE u.id != ?
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
                LIMIT ?, ?";
        $tipos = "iiiiiiiii";
        $params = [$id, $id, $id, $id, $id, $id, $id, $offset, $limite];
        break;

    case 'todos':
    default:
        $sql = "SELECT u.id, u.nombre, u.foto,
                       CASE
                           WHEN a.usuario1 IS NOT NULL THEN 'amigo'
                           WHEN p1.id IS NOT NULL THEN 'solicitado'
                           WHEN p2.id IS NOT NULL THEN 'recibido'
                           ELSE 'ninguno'
                       END AS estado
                FROM usuarios u
                LEFT JOIN amigos a ON (a.usuario1 = u.id AND a.usuario2 = ?) OR (a.usuario2 = u.id AND a.usuario1 = ?)
                LEFT JOIN peticionesamistad p1 ON p1.solicitante = ? AND p1.solicitado = u.id
                LEFT JOIN peticionesamistad p2 ON p2.solicitante = u.id AND p2.solicitado = ?
                WHERE u.id != ?
                  AND u.id NOT IN (
                      SELECT bloqueado FROM bloqueosusuarios WHERE bloqueador = ?
                      UNION
                      SELECT bloqueador FROM bloqueosusuarios WHERE bloqueado = ?
                  )
                LIMIT ?, ?";
        $tipos = "iiiiiiiii";
        $params = [$id, $id, $id, $id, $id, $id, $id, $offset, $limite];
        break;
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'imagen' => $row['foto'] ?: 'img/default.png',
        'estado' => $row['estado']
    ];
}

echo json_encode(['success' => true, 'usuarios' => $usuarios]);

$stmt->close();
$conn->close();
?>
