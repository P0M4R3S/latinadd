<?php
session_start();

// Verificar si el admin está logueado
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

require_once "../API/conectar.php";
$conn->set_charset("utf8");

$admin_id = $_SESSION['admin_id'];
$nombreAdmin = $_SESSION['admin_nombre'] ?? 'Administrador';

// Obtener páginas del admin
$sql = "SELECT id, nombre, tema, fecha FROM paginas WHERE usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$paginas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Latinadd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-3">
        <span class="navbar-brand">Panel de Control</span>
        <span class="text-white">Bienvenido, <?= htmlspecialchars($nombreAdmin) ?></span>
        <a href="logout.php" class="btn btn-sm btn-outline-light ms-3">Cerrar sesión</a>
    </nav>

    <div class="container mt-4">
        <h3>Opciones disponibles</h3>
        <ul class="list-group mb-5">
            <li class="list-group-item"><a href="crearPagina.php">➕ Crear nueva página</a></li>
            <li class="list-group-item"><a href="gestionar_contenido.php">📄 Gestionar contenido</a></li>
            <li class="list-group-item"><a href="usuarios.php">👥 Ver usuarios</a></li>
        </ul>

        <h4>Mis páginas</h4>
        <?php if (count($paginas) === 0): ?>
            <p class="text-muted">No has creado ninguna página aún.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-secondary">
                        <tr>
                            <th>Nombre</th>
                            <th>Tema</th>
                            <th>Fecha de creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paginas as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td><?= htmlspecialchars($p['tema']) ?></td>
                                <td><?= date("d/m/Y", strtotime($p['fecha'])) ?></td>
                                <td>
                                    <a href="gestorPagina.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Gestionar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
