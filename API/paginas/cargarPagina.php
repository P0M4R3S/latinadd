<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpagina = $_POST['idpagina'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que la página existe
$sql = "SELECT id, usuario, nombre, tema, descripcion, imagen, fecha, seguidores 
        FROM paginas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La página no existe.']);
    exit;
}

$pagina = $result->fetch_assoc();
$stmt->close();

// Verificar si el usuario la sigue
$sql = "SELECT id FROM seguidorespagina WHERE usuario = ? AND pagina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->store_result();
$seguida = $stmt->num_rows > 0;
$stmt->close();

// Determinar si es propia
$propia = $pagina['usuario'] == $id;

// Respuesta
echo json_encode([
    'success' => true,
    'pagina' => [
        'id' => $pagina['id'],
        'nombre' => $pagina['nombre'],
        'tema' => $pagina['tema'],
        'descripcion' => $pagina['descripcion'],
        'imagen' => $pagina['imagen'],
        'fecha' => $pagina['fecha'],
        'seguidores' => $pagina['seguidores'],
        'seguida' => $seguida,
        'propia' => $propia
    ]
]);

$conn->close();
?>
