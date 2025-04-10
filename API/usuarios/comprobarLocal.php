<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';

if (empty($id) || empty($token)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'ID y token son obligatorios.'
    ]);
    exit;
}

if (validarSesion($id, $token)) {
    echo json_encode([
        'success' => true,
        'mensaje' => 'Sesión válida.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida o expirada.'
    ]);
}
?>
