<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$motivo = $_POST['motivo'] ?? 'Sin especificar';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Obtener datos del usuario
$sql = "SELECT password, email FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Contraseña incorrecta.']);
    exit;
}

$correo = $user['email'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // Guardar motivo en la tabla cuentaseliminadas
    $sql_insert = "INSERT INTO cuentaseliminadas (idusuario, correo, motivo) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iss", $id, $correo, $motivo);
    $stmt->execute();
    $stmt->close();

    // Eliminar usuario
    $sql_delete = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Confirmar cambios
    $conn->commit();

    echo json_encode(['success' => true, 'mensaje' => 'Cuenta eliminada correctamente.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar la cuenta.']);
}

$conn->close();
?>
