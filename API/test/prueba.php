<?php
require_once '../conectar.php';

$conn->set_charset("utf8");

// Crear el post
$usuarioId = 9;
$textoPost = "Este es un post de prueba con comentarios y respuestas.";
$fecha = date("Y-m-d H:i:s");
$tipo = 1; // Post normal
$idcompartido = 0;

$stmt = $conn->prepare("INSERT INTO posts (usuario, texto, fecha, likes, comentarios, tipo, idcompartido) VALUES (?, ?, ?, 0, 0, ?, ?)");
$stmt->bind_param("issii", $usuarioId, $textoPost, $fecha, $tipo, $idcompartido);
$stmt->execute();
$idPost = $stmt->insert_id;
$stmt->close();

// Insertar comentarios principales
$comentarios = [
    "Muy buen post, gracias por compartir.",
    "Interesante punto de vista.",
    "No estoy de acuerdo, pero respeto tu opinión."
];

foreach ($comentarios as $textoComentario) {
    $stmt = $conn->prepare("INSERT INTO comentariospost (post, usuario, texto, fecha, idrespuesta) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("iiss", $idPost, $usuarioId, $textoComentario, $fecha);
    $stmt->execute();
}
$stmt->close();

// Obtener IDs de comentarios recién insertados
$comentarioIds = [];
$result = $conn->query("SELECT id FROM comentariospost WHERE post = $idPost AND idrespuesta = 0 ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $comentarioIds[] = $row['id'];
}
$result->close();

// Insertar respuestas a los dos primeros comentarios
$respuestas = [
    "Gracias por tu comentario.",
    "¿Qué parte no te parece bien?"
];

for ($i = 0; $i < 2; $i++) {
    $stmt = $conn->prepare("INSERT INTO comentariospost (post, usuario, texto, fecha, idrespuesta) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissi", $idPost, $usuarioId, $respuestas[$i], $fecha, $comentarioIds[$i]);
    $stmt->execute();
}
$stmt->close();

echo "Post creado con ID $idPost, con comentarios y respuestas.";
$conn->close();
