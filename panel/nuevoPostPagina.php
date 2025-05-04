<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

$idPagina = $_POST['idpagina'] ?? null;
$texto = trim($_POST['texto'] ?? '');
$imagenes = $_FILES['imagenes'] ?? [];

// Validar datos
if (!$idPagina || empty($texto)) {
    die("Faltan datos obligatorios.");
}

// Validar que la página pertenezca al admin
$sql = "SELECT id FROM paginas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idPagina, $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("La página no existe o no tienes permiso.");
}
$stmt->close();

// Insertar post (usuario = 0, tipo = 2)
$usuario = 0;
$tipo = 2;
$idcompartido = 0;
$sql = "INSERT INTO posts (usuario, texto, fecha, tipo, idcompartido) VALUES (?, ?, NOW(), ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $idPagina, $texto, $tipo, $idcompartido);
if (!$stmt->execute()) {
    die("Error al crear el post.");
}
$post_id = $stmt->insert_id;
$stmt->close();

// Subida de imágenes
if (!empty($imagenes['name'][0])) {
    if (!is_dir("../imagenes/posts")) {
        mkdir("../imagenes/posts", 0777, true);
    }

    foreach ($imagenes['tmp_name'] as $i => $tmpName) {
        if ($imagenes['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($imagenes['name'][$i], PATHINFO_EXTENSION);
            $finalName = uniqid('post_', true) . '.' . $ext;
            $rutaRel = "imagenes/posts/" . $finalName;
            $rutaFull = "../" . $rutaRel;

            if (move_uploaded_file($tmpName, $rutaFull)) {
                $sql = "INSERT INTO imagenes (usuario, tipo, ruta, post_id) VALUES (?, 'post', ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isi", $usuario, $rutaRel, $post_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

header("Location: gestorPagina.php?id=" . $idPagina);
exit;
