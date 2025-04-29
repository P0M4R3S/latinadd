<?php
require_once '../funciones.php';
require_once '../conectar.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

// Datos
$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idpagina = $_POST['idpagina'] ?? '';

// Validar sesión
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida.']);
    exit;
}

// Verificar que la página pertenece al usuario
$sql = "SELECT imagen FROM paginas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idpagina, $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No tienes permiso para borrar esta página o no existe.']);
    exit;
}
$pagina = $result->fetch_assoc();
$stmt->close();

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Eliminar imagen de la página si no es la default
    if (!empty($pagina['imagen']) && $pagina['imagen'] !== 'imagenes/default.jpg') {
        $ruta = "../" . $pagina['imagen'];
        if (file_exists($ruta)) unlink($ruta);
    }

    // 2. Obtener IDs de posts de la página
    $sql = "SELECT id FROM posts WHERE usuario = ? AND tipo = 3";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $res = $stmt->get_result();
    $post_ids = [];
    while ($row = $res->fetch_assoc()) {
        $post_ids[] = $row['id'];
    }
    $stmt->close();

    if (!empty($post_ids)) {
        // Convertir IDs a placeholders para IN ()
        $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
        $types = str_repeat('i', count($post_ids));
        
        // 3. Eliminar comentarios de esos posts
        $sql = "DELETE FROM comentariospost WHERE post IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$post_ids);
        $stmt->execute();
        $stmt->close();

        // 4. Eliminar likes de esos posts
        $sql = "DELETE FROM likespost WHERE post IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$post_ids);
        $stmt->execute();
        $stmt->close();

        // 5. Eliminar los posts
        $sql = "DELETE FROM posts WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$post_ids);
        $stmt->execute();
        $stmt->close();
    }

    // 6. Eliminar imágenes subidas por la página (tipo 'post')
    $sql = "SELECT ruta FROM imagenes WHERE usuario = ? AND tipo = 'post'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($img = $res->fetch_assoc()) {
        $ruta = "../" . $img['ruta'];
        if (file_exists($ruta)) unlink($ruta);
    }
    $stmt->close();

    $sql = "DELETE FROM imagenes WHERE usuario = ? AND tipo = 'post'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $stmt->close();

    // 7. Eliminar palabras clave asociadas
    $sql = "DELETE FROM palabraspagina WHERE pagina_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $stmt->close();

    // 8. Eliminar likes a la página
    $sql = "DELETE FROM likespagina WHERE pagina = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $stmt->close();

    // 9. Eliminar la página en sí
    $sql = "DELETE FROM paginas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $stmt->close();

    // Confirmar transacción
    $conn->commit();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Página eliminada correctamente junto con todo su contenido.'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al eliminar la página: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
