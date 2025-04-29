<?php
require_once '../conectar.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$token = $_POST['token'] ?? '';
$idamigo = $_POST['idamigo'] ?? '';

// Verificar autenticación
if (!validarSesion($id, $token)) {
    echo json_encode(['success' => false, 'mensaje' => 'Autenticación fallida.']);
    exit;
}

// Verificar si la amistad existe
$sql_check = "SELECT id FROM amigos WHERE 
              (usuario1 = ? AND usuario2 = ?) OR 
              (usuario1 = ? AND usuario2 = ?)";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iiii", $id, $idamigo, $idamigo, $id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'No existe una relación de amistad con este usuario.']);
    exit;
}

// Eliminar la amistad
$sql_delete = "DELETE FROM amigos WHERE 
               (usuario1 = ? AND usuario2 = ?) OR 
               (usuario1 = ? AND usuario2 = ?)";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("iiii", $id, $idamigo, $idamigo, $id);
$stmt_delete->execute();

echo json_encode(['success' => true, 'mensaje' => 'Amistad eliminada correctamente.']);

// Cerrar conexiones
$stmt_check->close();
$stmt_delete->close();
$conn->close();
?>
