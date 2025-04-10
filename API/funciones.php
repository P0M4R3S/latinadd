<?php
// Archivo: funciones.php

// Función para crear un nuevo token seguro
function nuevoToken() {
    return bin2hex(random_bytes(rand(5, 8))); // Token hexadecimal de entre 10 y 16 caracteres
}

// Función para validar un token de sesión
function validarSesion($id, $token) {
    include 'conectar.php';

    $sql_check = "SELECT id FROM usuarios WHERE id = ? AND token = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("is", $id, $token);
    $stmt->execute();
    $stmt->store_result();

    $isValid = ($stmt->num_rows > 0) ? 1 : 0;

    $stmt->close();
    $conn->close();

    return $isValid;
}
