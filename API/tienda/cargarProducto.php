<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idproducto = $_POST['idproducto'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el producto existe
$sql = "SELECT p.*, u.nombre, u.apellidos, u.foto 
        FROM productos p 
        JOIN usuarios u ON p.usuario = u.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado.']);
    exit;
}

$producto = $result->fetch_assoc();
$stmt->close();

// Verificar si es del propio usuario
$propio = ($producto['usuario'] == $id);

// Obtener imágenes asociadas al producto
$sql_imgs = "SELECT ruta FROM imagenes WHERE post_id = ? AND tipo = 'tienda'";
$stmt = $conn->prepare($sql_imgs);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$result_imgs = $stmt->get_result();

$imagenes = [];
while ($row = $result_imgs->fetch_assoc()) {
    $imagenes[] = $row['ruta'];
}
$stmt->close();

// Respuesta
echo json_encode([
    'success' => true,
    'producto' => [
        'id' => $producto['id'],
        'nombre' => $producto['nombre'],
        'descripcion' => $producto['descripcion'],
        'precio' => $producto['precio'],
        'latitud' => $producto['latitud'],
        'longitud' => $producto['longitud'],
        'estado' => $producto['estado'],
        'categoria' => $producto['categoria'],
        'fecha' => $producto['fecha'],
        'vendido' => $producto['vendido'],
        'usuario' => [
            'id' => $producto['usuario'],
            'nombre' => $producto['nombre'],
            'apellidos' => $producto['apellidos'],
            'foto' => $producto['foto']
        ],
        'propio' => $propio,
        'imagenes' => $imagenes
    ]
]);

$conn->close();
?>
