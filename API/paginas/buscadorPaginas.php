<?php
require_once '../conectar.php';
require_once '../funciones.php';
header('Content-Type: application/json');
$conn->set_charset("utf8");

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$filtro = $_POST['filtro'] ?? 'todas'; // CAMBIO: se usa 'filtro' en lugar de 'modo'
$indice = isset($_POST['indice']) ? intval($_POST['indice']) : 1;

$limite = 20;
$offset = ($indice - 1) * $limite;

// Validación básica (no bloquea el acceso, pero permite saber si sigue)
$sesionValida = validarSesion($id, $token);

// Según filtro, armamos SQL
switch ($filtro) {
    case 'seguidos':
        $sql = "SELECT p.id, p.nombre, p.imagen, 1 AS seguida
                FROM paginas p
                INNER JOIN seguidorespagina sp ON p.id = sp.pagina
                WHERE sp.usuario = ?
                ORDER BY p.nombre ASC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id, $limite, $offset);
        break;

    case 'sugerencias':
        $sql = "SELECT p.id, p.nombre, p.imagen, 0 AS seguida
                FROM paginas p
                WHERE p.id NOT IN (
                    SELECT pagina FROM seguidorespagina WHERE usuario = ?
                )
                ORDER BY RAND()
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id, $limite, $offset);
        break;

    case 'todas':
    default:
        $sql = "SELECT p.id, p.nombre, p.imagen,
                       EXISTS(SELECT 1 FROM seguidorespagina WHERE usuario = ? AND pagina = p.id) AS seguida
                FROM paginas p
                ORDER BY p.nombre ASC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id, $limite, $offset);
        break;
}

if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al preparar la consulta.']);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();

$paginas = [];
while ($row = $res->fetch_assoc()) {
    $paginas[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'imagen' => $row['imagen'] ?: 'img/default.png',
        'seguida' => boolval($row['seguida'])
    ];
}

echo json_encode([
    'success' => true,
    'paginas' => $paginas
]);

$conn->close();
?>
