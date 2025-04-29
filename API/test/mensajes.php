<?php
require_once '../conectar.php';
$conn->set_charset("utf8");

// IDs de los usuarios
$usuario1 = 10;
$usuario2 = 8;

// Comprobar si ya existe la conversación entre ambos
$sqlCheck = "SELECT id FROM conversaciones 
             WHERE (usuario1 = ? AND usuario2 = ?) OR (usuario1 = ? AND usuario2 = ?)";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("iiii", $usuario1, $usuario2, $usuario2, $usuario1);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $idConversacion = $row['id'];
} else {
    // Insertar nueva conversación
    $sqlInsert = "INSERT INTO conversaciones (usuario1, usuario2, eliminado1, eliminado2, producto) 
                  VALUES (?, ?, 0, 0, 0)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("ii", $usuario1, $usuario2);
    $stmt->execute();
    $idConversacion = $stmt->insert_id;
}
$stmt->close();

// Insertar 50 mensajes alternando emisor y receptor
$sqlMensaje = "INSERT INTO mensajesdirectos (emisor, receptor, mensaje, fecha, leido) 
               VALUES (?, ?, ?, NOW(), 0)";
$stmt = $conn->prepare($sqlMensaje);

for ($i = 1; $i <= 50; $i++) {
    $emisor = $i % 2 === 0 ? $usuario1 : $usuario2;
    $receptor = $emisor === $usuario1 ? $usuario2 : $usuario1;
    $mensaje = "Mensaje número $i de usuario $emisor";
    $stmt->bind_param("iis", $emisor, $receptor, $mensaje);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo "Conversación generada con éxito entre los usuarios $usuario1 y $usuario2.";
?>
