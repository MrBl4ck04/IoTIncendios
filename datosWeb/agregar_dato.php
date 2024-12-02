<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';
$mensaje = '';
$tipo_notificacion = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'];
    $sql = "INSERT INTO $sensor (fecha, hora, valor) VALUES (CURDATE(), CURTIME(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $valor);
    if ($stmt->execute()) {
        $mensaje = 'Dato agregado exitosamente';
        $tipo_notificacion = 'success';
    } else {
        $mensaje = 'Error al agregar el dato';
        $tipo_notificacion = 'error';
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
    <script>
        // Función para mostrar la notificación
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.remove();
                if (type === 'success') {
                    window.location.href = 'ver_datos.php?sensor=<?= $sensor ?>'; // Redirigir después de 3 segundos
                }
            }, 3000);
        }

        // Mostrar notificación si existe un mensaje
        <?php if ($mensaje): ?>
            window.onload = function() {
                showNotification('<?= $mensaje ?>', '<?= $tipo_notificacion ?>');
            };
        <?php endif; ?>
    </script>
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
