<?php
// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

// Obtener el término de búsqueda
$query = $_POST['query'] ?? '';

if (empty($query)) {
    echo json_encode(['success' => false, 'mensaje' => 'No se recibió ninguna búsqueda.']);
    exit;
}

$searchParam = "%{$query}%";
$resultados = [];

// Funciones de búsqueda
function buscarUsuarios($conn, $searchParam) {
    $sql = "SELECT id, nombre, apellidos, foto FROM usuarios WHERE estado = 'activo' AND (nombre LIKE ? OR apellidos LIKE ?) LIMIT 20";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarios = [];

    while ($row = $result->fetch_assoc()) {
        $usuarios[] = [
            'tipo' => 'usuario',
            'id' => $row['id'],
            'nombre' => trim($row['nombre'] . ' ' . $row['apellidos']),
            'foto' => $row['foto'] ?: 'default.jpg'
        ];
    }

    $stmt->close();
    return $usuarios;
}

function buscarPaginas($conn, $searchParam) {
    $sql = "SELECT id, nombre, imagen FROM paginas WHERE nombre LIKE ? LIMIT 20";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $paginas = [];

    while ($row = $result->fetch_assoc()) {
        $paginas[] = [
            'tipo' => 'pagina',
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'foto' => $row['imagen'] ?: 'default.jpg' // Aquí usamos 'imagen'
        ];
    }

    $stmt->close();
    return $paginas;
}

// Ejecutar las búsquedas
$resultados = array_merge(
    buscarUsuarios($conn, $searchParam),
    buscarPaginas($conn, $searchParam)
);

// Devolver respuesta
echo json_encode([
    'success' => true,
    'resultados' => $resultados
]);

$conn->close();
?>
