<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

// Verificar ID del post
$idPost = isset($_GET['idpost']) ? intval($_GET['idpost']) : 0;
if ($idPost <= 0) {
    die("ID de post no válido.");
}

// Verificar que el post sea de una página administrada por el admin
$sql = "SELECT p.*, pa.usuario AS propietario FROM posts p 
        JOIN paginas pa ON p.usuario = pa.id
        WHERE p.id = ? AND p.tipo = 2 AND pa.usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idPost, $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No se encontró el post o no tienes permisos.");
}

$post = $result->fetch_assoc();
$stmt->close();

// Obtener imágenes del post
$sql = "SELECT id, ruta FROM imagenes WHERE post_id = ? AND tipo = 'post'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idPost);
$stmt->execute();
$resImg = $stmt->get_result();
$imagenes = $resImg->fetch_all(MYSQLI_ASSOC);
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
<div class="container py-4">
    <h2>Editar Post</h2>
    <form action="actualizarPost.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="idpost" value="<?= $post['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Texto del post</label>
            <textarea name="texto" class="form-control" rows="4" required><?= htmlspecialchars($post['texto']) ?></textarea>
        </div>

        <?php if (!empty($imagenes)): ?>
            <div class="mb-3">
                <label class="form-label">Imágenes actuales</label><br>
                <?php foreach ($imagenes as $img): ?>
                    <div class="mb-2">
                        <img src="../<?= $img['ruta'] ?>" class="img-thumbnail" style="max-width: 200px">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Nuevas imágenes (opcional)</label>
            <input type="file" name="imagenes[]" multiple class="form-control" accept="image/*">
        </div>

        <button class="btn btn-success">Guardar cambios</button>
        <a href="gestorPagina.php?id=<?= $post['usuario'] ?>" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
