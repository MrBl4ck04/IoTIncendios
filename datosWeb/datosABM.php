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
    <style>
        h1 {
            color: #ffffff; /* Texto en blanco */
            font-size: 2.5rem; /* Tamaño del texto */
            text-align: center; /* Centrado */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Sombra */
        }

        body {
            background: linear-gradient(to bottom, #000000, #a5d6a7);
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh; /* Garantiza que el fondo cubra toda la pantalla */
            display: flex;
            flex-direction: column; /* Organiza verticalmente el contenido */
        }

        table {
            background-color: #ffffff; /* Fondo blanco para la tabla */
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra */
        }

        th, td {
            background-color: #ffffff; /* Fondo blanco para las celdas */
        }
    </style>
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
