<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';

// Obtener datos del sensor
$sql = "SELECT * FROM $sensor";
$result = $conn->query($sql);

// Obtener las columnas de la tabla
$columns = [];
if ($result && $result->num_rows > 0) {
    $columns = array_keys($result->fetch_assoc());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos del Sensor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Datos del Sensor: <?= ucfirst($sensor) ?></h1>
        
        <button onclick="window.location.href='agregar_dato.php?sensor=<?= $sensor ?>'">Agregar Dato</button>
        <button onclick="window.location.href='exportar_csv.php?sensor=<?= $sensor ?>'">Exportar a CSV</button>


        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?= ucfirst($column) ?></th>
                    <?php endforeach; ?>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= $value ?></td>
                        <?php endforeach; ?>
                        <td>
                            <a href="modificar_dato.php?sensor=<?= $sensor ?>&id=<?= $row[$columns[0]] ?>">Modificar</a>
                            <a href="eliminar_dato.php?sensor=<?= $sensor ?>&id=<?= $row[$columns[0]] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
       
    </div>
</body>
</html>
