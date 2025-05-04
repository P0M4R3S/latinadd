<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

$idPost = $_GET['idpost'] ?? null;

if (!$idPost || !is_numeric($idPost)) {
    die("ID de post no válido.");
}

// Verificar que el post pertenece a una página del admin
$sql = "SELECT p.*, g.usuario AS propietario
        FROM posts p
        JOIN paginas g ON p.usuario = g.id
        WHERE p.id = ? AND p.tipo = 2 AND g.usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idPost, $_SESSION['admin_id']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("No tienes permiso para editar este post.");
}

$post = $res->fetch_assoc();
$stmt->close();

// Cargar imágenes
$sql = "SELECT id, ruta FROM imagenes WHERE post_id = ? AND tipo = 'post'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idPost);
$stmt->execute();
$res = $stmt->get_result();
$imagenes = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2>Editar post</h2>
    <form action="actualizarPostPagina.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="idpost" value="<?= $post['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Texto</label>
            <textarea name="texto" class="form-control" rows="4" required><?= htmlspecialchars($post['texto']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Imágenes actuales</label><br>
            <?php foreach ($imagenes as $img): ?>
                <div class="mb-2">
                    <img src="../<?= $img['ruta'] ?>" alt="" style="max-width: 150px;">
                    <label class="form-check-label ms-2">
                        <input type="checkbox" name="eliminar[]" value="<?= $img['id'] ?>">
                        Eliminar
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Añadir nuevas imágenes</label>
            <input type="file" name="imagenes[]" class="form-control" accept="image/*" multiple>
        </div>

        <button class="btn btn-success">Guardar cambios</button>
    </form>
</div>
</body>
</html>
