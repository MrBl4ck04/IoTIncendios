<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];

    // Insertar nueva ubicación en la base de datos
    $sql = "INSERT INTO ubicaciones (descripcion, latitud, longitud) VALUES ('$descripcion', '$latitud', '$longitud')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>
                showNotification('Ubicación agregada exitosamente.', 'success');
                setTimeout(function() {
                    window.location.href = 'microABM.php'; // Redirige después de mostrar la notificación
                }, 3000); // Redirección después de 3 segundos
              </script>";
    } else {
        echo "<script>
                showNotification('Error: " . $conn->error . "', 'error');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Ubicación</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Función para mostrar notificaciones emergentes
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Agregar Nueva Ubicación</h1>

        <form method="POST">
            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" required>

            <label for="latitud">Latitud:</label>
            <input type="text" name="latitud" required>

            <label for="longitud">Longitud:</label>
            <input type="text" name="longitud" required>

            <button type="submit" class="button">Guardar Ubicación</button>
        </form>
    </div>
</body>
</html>
