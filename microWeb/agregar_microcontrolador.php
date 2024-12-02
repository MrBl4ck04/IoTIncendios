<?php
include 'config.php';

// Obtener ubicaciones
$sql = "SELECT ubicaciones_id, descripcion FROM ubicaciones";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $ubicacion_id = $_POST['ubicacion'];

    if ($nombre && $ubicacion_id) {
        $stmt = $conn->prepare("INSERT INTO microcontroladores (nombre, ubicaciones_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nombre, $ubicacion_id);
        if ($stmt->execute()) {
            header("Location: microABM.php");
            exit;
        } else {
            $error = "Error al agregar microcontrolador.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Microcontrolador</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Agregar Microcontrolador</h1>
        <?php if (isset($error)) : ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            
            <label for="ubicacion">Ubicación:</label>
            <select id="ubicacion" name="ubicacion" required>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <option value="<?= $row['ubicaciones_id'] ?>"><?= htmlspecialchars($row['descripcion']) ?></option>
                <?php endwhile; ?>
            </select>
            
            <button type="submit" class="button">Guardar</button>
        </form>
    </div>
</body>
</html>
