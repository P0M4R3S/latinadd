<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpost = $_POST['idpost'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que el post existe y obtener datos
$sql = "SELECT usuario, tipo FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpost);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El post no existe.']);
    exit;
}

$post = $result->fetch_assoc();
$idAutor = $post['usuario'];
$tipoPost = $post['tipo']; // 1: usuario, 2: página, 3: compartido
$stmt->close();

// Verificar si ya ha dado like
$sql = "SELECT id FROM likespost WHERE usuario = ? AND post = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpost);
$stmt->execute();
$result = $stmt->get_result();
$yaLike = $result->num_rows > 0;
$stmt->close();

if ($yaLike) {
    // Eliminar like
    $sql = "DELETE FROM likespost WHERE usuario = ? AND post = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idpost);
    $stmt->execute();
    $stmt->close();

    $sql = "UPDATE posts SET likes = likes - 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'liked' => false, 'mensaje' => 'Like eliminado.']);
} else {
    // Insertar like
    $sql = "INSERT INTO likespost (usuario, post) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $idpost);
    $stmt->execute();
    $stmt->close();

    $sql = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpost);
    $stmt->execute();
    $stmt->close();

    // 🔎 MÉTRICAS si el post es de una página
    if ($tipoPost == 2) {
        // Buscar palabras clave de la página (idAutor)
        $sqlPalabras = "SELECT palabra_id FROM palabraspagina WHERE pagina_id = ?";
        $stmt = $conn->prepare($sqlPalabras);
        $stmt->bind_param("i", $idAutor);
        $stmt->execute();
        $resPalabras = $stmt->get_result();

        while ($pal = $resPalabras->fetch_assoc()) {
            $palabra_id = $pal['palabra_id'];

            // Intentar actualizar
            $sqlUp = "UPDATE interesusuario 
                      SET puntuacion = puntuacion + 1, ultima_interaccion = NOW() 
                      WHERE usuario = ? AND palabra_id = ?";
            $stmtUp = $conn->prepare($sqlUp);
            $stmtUp->bind_param("ii", $id, $palabra_id);
            $stmtUp->execute();

            // Si no existía, insertar
            if ($stmtUp->affected_rows === 0) {
                $sqlIns = "INSERT INTO interesusuario (usuario, palabra_id, puntuacion) VALUES (?, ?, 1)";
                $stmtIns = $conn->prepare($sqlIns);
                $stmtIns->bind_param("ii", $id, $palabra_id);
                $stmtIns->execute();
                $stmtIns->close();
            }

            $stmtUp->close();
        }

        $stmt->close();
    }

    echo json_encode(['success' => true, 'liked' => true, 'mensaje' => 'Like agregado.']);
}

$conn->close();
?>
