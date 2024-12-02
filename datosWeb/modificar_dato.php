<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';
$id = $_GET['id'] ?? '';
$mensaje = '';
$tipo_notificacion = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'];
    $sql = "UPDATE $sensor SET valor = ? WHERE {$sensor}_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $valor, $id);
    if ($stmt->execute()) {
        $mensaje = "Dato modificado exitosamente";
        $tipo_notificacion = 'success';
    } else {
        $mensaje = "Error al modificar el dato";
        $tipo_notificacion = 'error';
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
    <style>
        /* Estilos de notificación */
        .notificacion {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            opacity: 0; /* Oculta inicialmente */
            transition: opacity 0.5s ease; /* Transición suave */
        }

        .notificacion.error {
            background-color: #f44336;
        }

        .notificacion.success {
            background-color: #4CAF50;
        }

        .notificacion.show {
            opacity: 1; /* Muestra la notificación */
        }
    </style>
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
