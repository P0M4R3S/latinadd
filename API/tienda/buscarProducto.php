<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$latitud = $_POST['latitud'] ?? null;
$longitud = $_POST['longitud'] ?? null;
$texto = trim($_POST['texto'] ?? '');
$indice = intval($_POST['indice'] ?? 0);
$limite = 10;
$offset = $indice * $limite;

// Validaciones básicas
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

if (!is_numeric($latitud) || !is_numeric($longitud)) {
    echo json_encode(['success' => false, 'mensaje' => 'Ubicación inválida.']);
    exit;
}

if (empty($texto)) {
    echo json_encode(['success' => false, 'mensaje' => 'Texto de búsqueda vacío.']);
    exit;
}

// Buscar productos por texto y calcular distancia
$sql = "
    SELECT p.*, 
           u.nombre, u.apellidos, u.foto,
           (6371 * ACOS(
               COS(RADIANS(?)) * COS(RADIANS(p.latitud)) *
               COS(RADIANS(p.longitud) - RADIANS(?)) +
               SIN(RADIANS(?)) * SIN(RADIANS(p.latitud))
           )) AS distancia
    FROM productos p
    JOIN usuarios u ON p.usuario = u.id
    WHERE p.vendido = 0 AND (
          p.nombre LIKE CONCAT('%', ?, '%') OR 
          p.descripcion LIKE CONCAT('%', ?, '%')
    )
    HAVING distancia IS NOT NULL
    ORDER BY distancia ASC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddsssii", $latitud, $longitud, $latitud, $texto, $texto, $limite, $offset);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];

while ($row = $result->fetch_assoc()) {
    $idproducto = $row['id'];

    // Obtener imágenes del producto
    $sql_img = "SELECT ruta FROM imagenes WHERE tipo = 'tienda' AND post_id = ?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $idproducto);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    $imagenes = [];

    while ($img = $result_img->fetch_assoc()) {
        $imagenes[] = $img['ruta'];
    }
    $stmt_img->close();

    $productos[] = [
        'id' => $idproducto,
        'nombre' => $row['nombre'],
        'descripcion' => $row['descripcion'],
        'precio' => $row['precio'],
        'categoria' => $row['categoria'],
        'fecha' => $row['fecha'],
        'latitud' => $row['latitud'],
        'longitud' => $row['longitud'],
        'estado' => $row['estado'],
        'distancia_km' => round($row['distancia'], 2),
        'usuario' => [
            'id' => $row['usuario'],
            'nombre' => $row['nombre'],
            'apellidos' => $row['apellidos'],
            'foto' => $row['foto']
        ],
        'imagenes' => $imagenes
    ];
}

echo json_encode([
    'success' => true,
    'productos' => $productos
]);

$conn->close();
?>
