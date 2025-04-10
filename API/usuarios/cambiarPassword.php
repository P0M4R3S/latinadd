<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$npassword = $_POST['npassword'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Verificar que se envió una nueva contraseña
if (empty($npassword)) {
    echo json_encode(['success' => false, 'mensaje' => 'La nueva contraseña no puede estar vacía.']);
    exit;
}

// Obtener contraseña actual
$sql = "SELECT password FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Contraseña actual incorrecta.']);
    exit;
}

// Verificar que la nueva contraseña no sea igual a la actual
if (password_verify($npassword, $user['password'])) {
    echo json_encode(['success' => false, 'mensaje' => 'La nueva contraseña no puede ser igual a la anterior.']);
    exit;
}

// Actualizar la contraseña
$new_hashed_password = password_hash($npassword, PASSWORD_DEFAULT);

$sql = "UPDATE usuarios SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_hashed_password, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar la contraseña.']);
}

$stmt->close();
$conn->close();
?>
