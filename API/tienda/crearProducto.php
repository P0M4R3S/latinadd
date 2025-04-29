<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entradas
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$categoria = trim($_POST['categoria'] ?? '');
$latitud = floatval($_POST['latitud'] ?? 0);
$longitud = floatval($_POST['longitud'] ?? 0);
$estado = intval($_POST['estado'] ?? 0);

// Imágenes en base64
$imagenesBase64 = $_POST['imagenes'] ?? []; // array con img1, img2, img3

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar campos básicos
if (empty($nombre) || empty($descripcion) || $precio <= 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios o precio inválido.']);
    exit;
}

// Insertar producto
$sql = "INSERT INTO productos (usuario, nombre, descripcion, precio, fecha, vendido, latitud, longitud, estado, categoria) 
        VALUES (?, ?, ?, ?, NOW(), 0, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issdddis", $id, $nombre, $descripcion, $precio, $latitud, $longitud, $estado, $categoria);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el producto.']);
    exit;
}

$idproducto = $stmt->insert_id;
$stmt->close();

// Crear carpeta si no existe
$directorio = '../imagenes_tienda/';
if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
}

// Guardar imágenes
foreach ($imagenesBase64 as $img64) {
    $imgData = base64_decode($img64, true);
    if ($imgData === false) continue;

    $nombreArchivo = uniqid('prod_', true) . '.jpg';
    $rutaRelativa = 'imagenes_tienda/' . $nombreArchivo;
    $rutaCompleta = '../' . $rutaRelativa;

    if (file_put_contents($rutaCompleta, $imgData)) {
        $sqlImg = "INSERT INTO imagenes (usuario, post_id, tipo, ruta) VALUES (?, ?, 'tienda', ?)";
        $stmtImg = $conn->prepare($sqlImg);
        $stmtImg->bind_param("iis", $id, $idproducto, $rutaRelativa);
        $stmtImg->execute();
        $stmtImg->close();
    }
}

echo json_encode(['success' => true, 'idproducto' => $idproducto, 'mensaje' => 'Producto creado correctamente.']);
$conn->close();
?>
