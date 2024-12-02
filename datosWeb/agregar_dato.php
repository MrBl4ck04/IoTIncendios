<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'];
    $sql = "INSERT INTO $sensor (fecha, hora, valor) VALUES (CURDATE(), CURTIME(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $valor);
    if ($stmt->execute()) {
        echo "<script>alert('Dato agregado exitosamente'); window.location.href='ver_datos.php?sensor=$sensor';</script>";
    } else {
        echo "<script>alert('Error al agregar el dato');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Dato</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Agregar Dato al Sensor: <?= ucfirst($sensor) ?></h1>
        <form method="POST">
            <label for="valor">Valor:</label>
            <input type="number" name="valor" required>
            <button type="submit">Guardar Dato</button>
        </form>
    </div>
</body>
</html>
