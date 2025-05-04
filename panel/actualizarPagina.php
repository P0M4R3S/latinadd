<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

// Recoger datos
$idpagina = intval($_POST['id'] ?? 0);  // ← Cambiado a 'id' según tu formulario
$nombre = trim($_POST['nombre'] ?? '');
$tema = trim($_POST['tema'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// Validaciones
if ($idpagina <= 0 || empty($nombre) || empty($tema) || empty($descripcion)) {
    die("Faltan campos obligatorios.");
}

// Verificar que la página pertenezca al admin
$sql = "SELECT id FROM paginas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idpagina, $_SESSION['admin_id']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("No tienes permiso para editar esta página.");
}
$stmt->close();

// Manejar imagen si fue enviada
$imagenNombre = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombreFinal = uniqid('pagina_', true) . '.' . $ext;
    $rutaDestino = "../imagenes/paginas/" . $nombreFinal;

    if (!is_dir("../imagenes/paginas")) {
        mkdir("../imagenes/paginas", 0777, true);
    }

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
        $imagenNombre = "imagenes/paginas/" . $nombreFinal;
    } else {
        die("Error al subir la nueva imagen.");
    }
}

// Actualizar la página
if ($imagenNombre) {
    $sql = "UPDATE paginas SET nombre = ?, tema = ?, descripcion = ?, imagen = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $tema, $descripcion, $imagenNombre, $idpagina);
} else {
    $sql = "UPDATE paginas SET nombre = ?, tema = ?, descripcion = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nombre, $tema, $descripcion, $idpagina);
}
$stmt->execute();
$stmt->close();

// Redirigir con confirmación
header("Location: gestorPagina.php?id=$idpagina&actualizado=1");
exit;
