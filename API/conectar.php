<?php

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// Configuración de la base de datos
$host = 'localhost';
$username = 'username';
$password = 'password';
$dbname = 'redNetwork';

// Crear la conexión con MySQL usando mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Comprobar si hubo un error en la conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar el conjunto de caracteres a utf8mb4 para soportar emojis y caracteres especiales
$conn->set_charset("utf8mb4");
?>
