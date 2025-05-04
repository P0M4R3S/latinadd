<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

$idPagina = $_GET['id'] ?? null;

if (!$idPagina || !is_numeric($idPagina)) {
    die("ID de página no válido.");
}

// Verificar que la página pertenezca al admin
$sql = "SELECT * FROM paginas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idPagina, $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Página no encontrada o sin permiso.");
}

$pagina = $result->fetch_assoc();
$stmt->close();

// Obtener posts de la página
$sql = "SELECT id, texto, fecha FROM posts WHERE tipo = 2 AND usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idPagina);
$stmt->execute();
$res = $stmt->get_result();
$posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Página - <?= htmlspecialchars($pagina['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2>Editar datos de la página</h2>
    <form action="actualizarPagina.php" method="POST" enctype="multipart/form-data" class="mb-5">
        <input type="hidden" name="id" value="<?= $pagina['id'] ?>">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($pagina['nombre']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tema</label>
            <input type="text" name="tema" class="form-control" value="<?= htmlspecialchars($pagina['tema']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($pagina['descripcion']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagen actual:</label><br>
            <img src="<?= $pagina['imagen'] ?>" alt="Portada" class="img-fluid" style="max-width:200px">
        </div>
        <div class="mb-3">
            <label class="form-label">Cambiar imagen</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
        </div>
        <button class="btn btn-primary">Actualizar página</button>
    </form>

    <h2>Nuevo post en esta página</h2>
    <form action="nuevoPostPagina.php" method="POST" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="idpagina" value="<?= $pagina['id'] ?>">
        <div class="mb-3">
            <label class="form-label">Texto del post</label>
            <textarea name="texto" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Imágenes (opcional)</label>
            <input type="file" name="imagenes[]" multiple class="form-control" accept="image/*">
        </div>
        <button class="btn btn-success">Publicar post</button>
    </form>

    <h2>Posts publicados</h2>
    <?php if (empty($posts)): ?>
        <p class="text-muted">Esta página aún no tiene posts publicados.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($posts as $p): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= date("d/m/Y H:i", strtotime($p['fecha'])) ?></strong><br>
                        <?= nl2br(htmlspecialchars(substr($p['texto'], 0, 100))) ?>
                    </div>
                    <form action="editarPost.php" method="POST" target="_blank" class="m-0">
                        <input type="hidden" name="idpost" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-outline-primary btn-sm">Ver</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
