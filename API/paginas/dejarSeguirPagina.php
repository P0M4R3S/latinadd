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
$sql = "SELECT id FROM paginas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La página no existe.']);
    exit;
}
$stmt->close();

// Verificar que el usuario realmente siga la página
$sql = "SELECT id FROM seguidorespagina WHERE usuario = ? AND pagina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No estás siguiendo esta página.']);
    exit;
}
$stmt->close();

// Eliminar el seguidor
$sql = "DELETE FROM seguidorespagina WHERE usuario = ? AND pagina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->close();

// Reducir contador de seguidores
$sql = "UPDATE paginas SET seguidores = GREATEST(seguidores - 1, 0) WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$stmt->close();

// Obtener palabras clave de la página
$sql = "SELECT palabra_id FROM palabraspagina WHERE pagina_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $palabra_id = $row['palabra_id'];

    // Reducir valor en métricas, sin bajar de 0
    $sql_update = "
        UPDATE metricasusuario 
        SET valor = CASE 
                      WHEN valor > 0 THEN valor - 1 
                      ELSE 0 
                    END 
        WHERE usuario_id = ? AND palabra_id = ?";
    $stmt_upd = $conn->prepare($sql_update);
    $stmt_upd->bind_param("ii", $id, $palabra_id);
    $stmt_upd->execute();
    $stmt_upd->close();
}

echo json_encode(['success' => true, 'mensaje' => 'Has dejado de seguir esta página.']);
$conn->close();
?>
