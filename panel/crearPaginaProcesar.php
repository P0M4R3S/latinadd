<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

// Recoger y validar datos
$nombre       = trim($_POST['nombre'] ?? '');
$tema         = trim($_POST['tema'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$palabras     = $_POST['palabras'] ?? [];
$usuario      = 1; // ID fijo del administrador por ahora
$imagenNombre = null;

if (empty($nombre) || empty($tema) || empty($descripcion)) {
    die("Todos los campos son obligatorios.");
}

// Procesar imagen si fue enviada
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $nombreFinal = uniqid('pagina_', true) . '.' . $ext;

    $rutaServidor = realpath(__DIR__ . '/../imagenes/paginas');
    if (!is_dir($rutaServidor)) {
        mkdir($rutaServidor, 0777, true);
    }

    $rutaCompleta = $rutaServidor . '/' . $nombreFinal;
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
        $imagenNombre = "imagenes/paginas/" . $nombreFinal; // Ruta relativa
    } else {
        die("Error al mover la imagen al destino00.");
    }
}

// Insertar página
$sql = "INSERT INTO paginas (usuario, nombre, tema, descripcion, imagen, fecha) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $usuario, $nombre, $tema, $descripcion, $imagenNombre);
$stmt->execute();
$idPagina = $stmt->insert_id;
$stmt->close();

// Insertar palabras clave asociadas
if (!empty($palabras)) {
    $sql = "INSERT INTO palabraspagina (pagina_id, palabra_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($palabras as $palabra_id) {
        $stmt->bind_param("ii", $idPagina, $palabra_id);
        $stmt->execute();
    }
    $stmt->close();
}

// Redirigir al panel
header("Location: panel.php?creada=1");
exit;
