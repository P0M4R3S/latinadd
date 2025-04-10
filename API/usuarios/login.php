<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

// Obtener los datos enviados mediante POST
$email = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';

// Validar que todos los campos requeridos estén presentes
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Correo y contraseña son obligatorios.'
    ]);
    exit;
}

// Verificar si el correo existe en la base de datos
$sql_check = "SELECT id, password, primera FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Correo o contraseña incorrectos.'
    ]);
    exit;
}

// Obtener los datos del usuario
$user = $result->fetch_assoc();
$user_id = $user['id'];
$hashed_password = $user['password'];
$primera = $user['primera'];

// Verificar la contraseña
if (!password_verify($password, $hashed_password)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Correo o contraseña incorrectos.'
    ]);
    exit;
}

// Generar un nuevo token
$new_token = nuevoToken();

// Actualizar el token en la base de datos
$sql_update = "UPDATE usuarios SET token = ?, ultima = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("si", $new_token, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'mensaje' => 'Inicio de sesión exitoso.',
        'id' => $user_id,
        'token' => $new_token,
        'primera' => $primera
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al iniciar sesión. Intente nuevamente más tarde.'
    ]);
}

// Cerrar conexiones
$stmt->close();
$conn->close();
?>
