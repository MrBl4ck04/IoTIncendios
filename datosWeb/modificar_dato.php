<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';
$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'];
    $sql = "UPDATE $sensor SET valor = ? WHERE {$sensor}_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $valor, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Dato modificado exitosamente'); window.location.href='ver_datos.php?sensor=$sensor';</script>";
    } else {
        echo "<script>alert('Error al modificar el dato');</script>";
    }
}

// Obtener el dato a modificar
$sql = "SELECT * FROM $sensor WHERE {$sensor}_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$datos = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Dato</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Modificar Dato del Sensor: <?= ucfirst($sensor) ?></h1>
        <form method="POST">
            <label for="valor">Valor:</label>
            <input type="number" name="valor" value="<?= $datos['valor'] ?>" required>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
