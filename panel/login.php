<?php
require_once '../API/conectar.php';
session_start();

$usuario = $_POST['usuario'] ?? '';
$clave = $_POST['clave'] ?? '';

if (empty($usuario) || empty($clave)) {
    header("Location: index.php?error=Faltan campos.");
    exit;
}

$conn->set_charset("utf8");

$sql = "SELECT id, usuario, clave FROM admin WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($clave, $admin['clave'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        header("Location: panel.php");
        exit;
    } else {
        header("Location: index.php?error=Contraseña incorrecta.");
        exit;
    }
} else {
    header("Location: index.php?error=Usuario no encontrado.");
    exit;
}
