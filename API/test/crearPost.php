<?php
require_once('../conectar.php'); // Asegúrate de que la ruta sea correcta

// Crear 3 usuarios de prueba
$usuarios = [
    ['nombre' => 'Juan', 'apellidos' => 'Pérez', 'email' => 'juan@example.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
    ['nombre' => 'Ana', 'apellidos' => 'Gómez', 'email' => 'ana@example.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
    ['nombre' => 'Luis', 'apellidos' => 'Martínez', 'email' => 'luis@example.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)]
];

$user_ids = [];

foreach ($usuarios as $u) {
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, email, password, token, bot) VALUES (?, ?, ?, ?, 'token123', 1)");
    $stmt->bind_param("ssss", $u['nombre'], $u['apellidos'], $u['email'], $u['password']);
    if ($stmt->execute()) {
        $user_ids[] = $stmt->insert_id;
    } else {
        echo "Error creando usuario: " . $stmt->error;
    }
    $stmt->close();
}

// Crear 3 posts por usuario
foreach ($user_ids as $idusuario) {
    for ($i = 1; $i <= 3; $i++) {
        $texto = "Este es el post número $i del usuario $idusuario";
        $stmt = $conn->prepare("INSERT INTO posts (idusuario, texto, tipo) VALUES (?, ?, '1')");
        $stmt->bind_param("is", $idusuario, $texto);
        if (!$stmt->execute()) {
            echo "Error creando post para usuario $idusuario: " . $stmt->error;
        }
        $stmt->close();
    }
}

echo "Usuarios y posts creados correctamente.";
?>