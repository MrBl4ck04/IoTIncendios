<?php
include 'config.php';

// Obtener los sensores de la base de datos
$sensores = [];
$sql = "SHOW TABLES";
$result = $conn->query($sql);
while ($row = $result->fetch_row()) {
    if (!in_array($row[0], ['usuarios', 'ubicaciones', 'microcontroladores'])) {
        $sensores[] = $row[0];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sensores</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Gestión de Sensores y Datos</h1>
        <div class="sensor-buttons">
            <?php foreach ($sensores as $sensor): ?>
                <button onclick="window.location.href='ver_datos.php?sensor=<?= $sensor ?>'"><?= ucfirst($sensor) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
