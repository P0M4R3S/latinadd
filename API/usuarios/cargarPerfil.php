<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idusuario = $_POST['idusuario'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Obtener datos del perfil
$sql = "SELECT nombre, apellidos, nacimiento, pais, ciudad, descripcion, foto FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo json_encode(['success' => false, 'mensaje' => 'Usuario no encontrado.']);
    exit;
}

// Contar el número de amigos
$sql = "SELECT COUNT(*) as num_amigos FROM amigos WHERE usuario1 = ? OR usuario2 = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idusuario, $idusuario);
$stmt->execute();
$result = $stmt->get_result();
$num_amigos = ($result->fetch_assoc()['num_amigos']) ?? 0;
$stmt->close();

// Determinar el vínculo
$vinculo = 3; // Por defecto: no son amigos ni hay solicitudes
$idSolicitud = 0;

if ($id == $idusuario) {
    $vinculo = 1; // Es su propio perfil
} else {
    // Son amigos
    $sql = "SELECT id FROM amigos WHERE 
            (usuario1 = ? AND usuario2 = ?) OR 
            (usuario1 = ? AND usuario2 = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $id, $idusuario, $idusuario, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vinculo = 2; // Son amigos
    } else {
        // El usuario actual ha enviado solicitud
        $sql = "SELECT id FROM peticionesamistad WHERE solicitante = ? AND solicitado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $idusuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $vinculo = 5; // Ha enviado solicitud
            $idSolicitud = $result->fetch_assoc()['id'];
        } else {
            // El usuario actual ha recibido solicitud
            $sql = "SELECT id FROM peticionesamistad WHERE solicitante = ? AND solicitado = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $idusuario, $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $vinculo = 4; // Ha recibido solicitud
                $idSolicitud = $result->fetch_assoc()['id'];
            }
        }
    }
    $stmt->close();
}

// Armar respuesta
$usuario['num_amigos'] = $num_amigos;
$usuario['vinculo'] = $vinculo;
$usuario['idSolicitud'] = $idSolicitud;

echo json_encode(['success' => true, 'perfil' => $usuario]);
?>
