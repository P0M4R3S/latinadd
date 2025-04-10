<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Entrada
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idcompartido = $_POST['idcompartido'] ?? '';
$texto = trim($_POST['texto'] ?? '');

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Validar existencia del post original
$sql = "SELECT id FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idcompartido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post original no existe.']);
    exit;
}
$stmt->close();

// Insertar nuevo post compartido (tipo 3)
$sql = "INSERT INTO posts (usuario, texto, fecha, tipo, idcompartido) VALUES (?, ?, NOW(), 3, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $id, $texto, $idcompartido);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'idpost' => $stmt->insert_id,
        'mensaje' => 'Post compartido correctamente.'
    ]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al compartir el post.']);
}

$stmt->close();
$conn->close();
?>
