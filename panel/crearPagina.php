<?php
require_once "verificarSesion.php";
require_once "../API/conectar.php";

$conn->set_charset("utf8");

// Obtener palabras clave desde la base de datos
$palabras = [];
$sql = "SELECT id, palabra FROM palabrasclave ORDER BY palabra ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $palabras[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Página - Panel Latinadd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">Crear nueva página</h2>

    <form action="crearPaginaProcesar.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la página</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>

        <div class="mb-3">
            <label for="tema" class="form-label">Tema</label>
            <input type="text" class="form-control" name="tema" id="tema" required>
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen de portada</label>
            <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="palabras" class="form-label">Palabras clave</label>
            <select class="form-select" name="palabras[]" id="palabras" multiple size="10">
                <?php foreach ($palabras as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['palabra']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Puedes seleccionar varias manteniendo Ctrl o Shift.</div>
        </div>

        <button type="submit" class="btn btn-primary">Crear página</button>
    </form>
</div>

</body>
</html>
