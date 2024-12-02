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
        echo "<script>showNotification('Microcontrolador actualizado exitosamente.', 'success');</script>";
    } else {
        echo "<script>showNotification('Error: " . $conn->error . "', 'danger');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Microcontrolador</title>
    <!-- Cargar Bootstrap desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Función para mostrar la notificación
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.remove();
                if (type === 'success') {
                    window.location.href = 'microABM.php'; // Redirigir después de 3 segundos si es éxito
                }
            }, 3000);
        }
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="indexAdmin.html">OnFire</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/IoTIncendios/mapa.php">Puntos de alerta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/IoTIncendios/usersWEb/usersABM.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/IoTIncendios/microWEb/microABM.php">Microcontroladores</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Datos Sensores
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="http://localhost/IoTIncendios/datosWeb/ver_datos.php?sensor=flama">Flama</a>
                            <a class="dropdown-item" href="http://localhost/IoTIncendios/datosWeb/ver_datos.php?sensor=humedad">Humedad</a>
                            <a class="dropdown-item" href="http://localhost/IoTIncendios/datosWeb/ver_datos.php?sensor=humo">Humo</a>
                            <a class="dropdown-item" href="http://localhost/IoTIncendios/datosWeb/ver_datos.php?sensor=temperatura">Temperatura</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/IoTIncendios/contacto/indexContacto.html">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://127.0.0.1:5501/frontend/index.html">Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container" style="margin-top: 120px;">
        <h1 class="my-4">Editar Microcontrolador</h1>

        <form method="POST">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($microcontrolador['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="ubicacion_id">Ubicación:</label>
                <select class="form-control" name="ubicacion_id" required>
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
            </div>

            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
