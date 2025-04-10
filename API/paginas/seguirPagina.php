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

// Verificar que la página existe y no sea propia
$sql = "SELECT usuario FROM paginas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La página no existe.']);
    exit;
}

$row = $result->fetch_assoc();
if ($row['usuario'] == $id) {
    echo json_encode(['success' => false, 'mensaje' => 'No puedes seguir tu propia página.']);
    exit;
}
$stmt->close();

// Verificar si ya sigue
$sql = "SELECT id FROM seguidorespagina WHERE usuario = ? AND pagina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Ya sigues esta página.']);
    exit;
}
$stmt->close();

// Insertar seguidor
$sql = "INSERT INTO seguidorespagina (usuario, pagina) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->close();

// Aumentar contador
$sql = "UPDATE paginas SET seguidores = seguidores + 1 WHERE id = ?";
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

    // Intentar actualizar métrica
    $sql_upd = "UPDATE metricasusuario SET valor = valor + 1 WHERE usuario_id = ? AND palabra_id = ?";
    $stmt_upd = $conn->prepare($sql_upd);
    $stmt_upd->bind_param("ii", $id, $palabra_id);
    $stmt_upd->execute();

    // Si no existía, insertamos
    if ($stmt_upd->affected_rows === 0) {
        $sql_ins = "INSERT INTO metricasusuario (usuario_id, palabra_id, valor) VALUES (?, ?, 1)";
        $stmt_ins = $conn->prepare($sql_ins);
        $stmt_ins->bind_param("ii", $id, $palabra_id);
        $stmt_ins->execute();
        $stmt_ins->close();
    }

    $stmt_upd->close();
}

$stmt->close();

echo json_encode(['success' => true, 'mensaje' => 'Ahora sigues esta página.']);
$conn->close();
?>
