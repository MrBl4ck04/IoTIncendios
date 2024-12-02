<?php
include 'config.php';

// Obtener el ID del microcontrolador a editar
$id = $_GET['id'];
$sql = "SELECT * FROM microcontroladores WHERE microcontroladores_id = $id";
$result = $conn->query($sql);
$microcontrolador = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $ubicacion_id = $_POST['ubicacion_id'];

    // Actualizar los datos del microcontrolador
    $sql = "UPDATE microcontroladores SET nombre='$nombre', ubicaciones_id='$ubicacion_id' WHERE microcontroladores_id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Microcontrolador actualizado exitosamente.'); window.location.href = 'microABM.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Microcontrolador</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Editar Microcontrolador</h1>

        <form method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($microcontrolador['nombre']); ?>" required>

            <label for="ubicacion_id">Ubicación:</label>
            <select name="ubicacion_id" required>
                <?php
                // Obtener las ubicaciones para el selector
                $sql_ubicaciones = "SELECT * FROM ubicaciones";
                $ubicaciones_result = $conn->query($sql_ubicaciones);
                while ($ubicacion = $ubicaciones_result->fetch_assoc()) {
                    $selected = ($microcontrolador['ubicaciones_id'] == $ubicacion['ubicaciones_id']) ? 'selected' : '';
                    echo "<option value='{$ubicacion['ubicaciones_id']}' $selected>{$ubicacion['descripcion']}</option>";
                }
                ?>
            </select>

            <button type="submit" class="button">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
