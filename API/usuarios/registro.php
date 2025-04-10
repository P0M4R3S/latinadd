<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener los datos enviados mediante POST
$nombre = $_POST['nombre'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$email = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';

// Validar que todos los campos requeridos estén presentes
if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Todos los campos son obligatorios.'
    ]);
    exit;
}

// Comprobar si el correo ya está registrado
$sql_check = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'El correo ya está registrado.'
    ]);
    exit;
}
$stmt->close();

// Preparar inserción
$token = nuevoToken();
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$sql_insert = "INSERT INTO usuarios (nombre, apellidos, email, password, token) 
               VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("sssss", $nombre, $apellidos, $email, $hashed_password, $token);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;

    echo json_encode([
        'success' => true,
        'mensaje' => 'Usuario registrado exitosamente.',
        'id' => $user_id,
        'token' => $token,
        'primera' => true
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al registrar el usuario.',
        'error' => $stmt->error // Para depurar
    ]);
    exit;
}

$conn->close();
?>
