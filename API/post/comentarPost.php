<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Obtener los datos enviados por POST
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$texto = trim($_POST['texto'] ?? '');
$idpost = $_POST['idpost'] ?? '';
$idrespuesta = $_POST['idrespuesta'] ?? null;

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar texto
if (empty($texto)) {
    echo json_encode(['success' => false, 'mensaje' => 'El comentario no puede estar vacío.']);
    exit;
}

// Verificar que el post exista
$sql = "SELECT id FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}
$stmt->close();

// Verificar que el comentario padre exista (si es respuesta)
if (!empty($idrespuesta)) {
    $sql = "SELECT id FROM comentariospost WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idrespuesta);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'mensaje' => 'El comentario al que intentas responder no existe.']);
        exit;
    }
    $stmt->close();
} else {
    $idrespuesta = null;
}

// Insertar comentario
$sql = "INSERT INTO comentariospost (usuario, post, texto, fecha, idrespuesta) 
        VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisi", $id, $idpost, $texto, $idrespuesta);
$exito = $stmt->execute();
$idcomentario = $stmt->insert_id;
$stmt->close();

// Actualizar contador de comentarios
if ($exito) {
    $sql = "UPDATE posts SET comentarios = comentarios + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'idcomentario' => $idcomentario,
        'mensaje' => 'Comentario agregado correctamente.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al agregar el comentario.'
    ]);
}

$conn->close();
?>
