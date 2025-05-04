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
$sql = "SELECT id, usuario, nombre, tema, descripcion, imagen, fecha, seguidores 
        FROM paginas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idpagina);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La página no existe.']);
    exit;
}

$pagina = $result->fetch_assoc();
$stmt->close();

$esPropia = $pagina['usuario'] == $id;

// Si la página no es del usuario, ajustar métricas
if (!$esPropia) {
    $sql = "SELECT palabra_id FROM palabraspagina WHERE pagina_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idpagina);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($pal = $res->fetch_assoc()) {
        $palabra_id = $pal['palabra_id'];

        $sqlUp = "UPDATE interesusuario SET puntuacion = puntuacion + 1, ultima_interaccion = NOW()
                  WHERE usuario = ? AND palabra_id = ?";
        $stmtUp = $conn->prepare($sqlUp);
        $stmtUp->bind_param("ii", $id, $palabra_id);
        $stmtUp->execute();

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

// Verificar si el usuario la sigue
$sql = "SELECT id FROM seguidorespagina WHERE usuario = ? AND pagina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idpagina);
$stmt->execute();
$stmt->store_result();
$seguida = $stmt->num_rows > 0;
$stmt->close();

// Respuesta
echo json_encode([
    'success' => true,
    'pagina' => [
        'id' => $pagina['id'],
        'nombre' => $pagina['nombre'],
        'tema' => $pagina['tema'],
        'descripcion' => $pagina['descripcion'],
        'imagen' => $pagina['imagen'],
        'fecha' => $pagina['fecha'],
        'seguidores' => $pagina['seguidores'],
        'seguida' => $seguida,
        'propia' => $esPropia
    ]
]);

$conn->close();
?>
