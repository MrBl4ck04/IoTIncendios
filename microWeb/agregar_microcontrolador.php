<?php
include 'config.php';

// Obtener ubicaciones
$sql = "SELECT ubicaciones_id, descripcion FROM ubicaciones";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $ubicacion_id = $_POST['ubicacion'];

    if ($nombre && $ubicacion_id) {
        $stmt = $conn->prepare("INSERT INTO microcontroladores (nombre, ubicaciones_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nombre, $ubicacion_id);
        if ($stmt->execute()) {
            echo "<script>showNotification('Microcontrolador agregado exitosamente.', 'success');</script>";
        } else {
            $error = "Error al agregar microcontrolador.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Microcontrolador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script>
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            DashBoards
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="http://localhost/IoTIncendios/dashboard/DashFechaValor.php">Fechas</a>
                            <a class="dropdown-item" href="http://localhost/IoTIncendios/dashboard/DashUbicacionValor.php">Ubicaciones</a>    
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

    <div class="container" style="margin-top: 100px;">
        <h1>Agregar Microcontrolador</h1>

        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="ubicacion">Ubicación:</label>
                <select id="ubicacion" name="ubicacion" class="form-control" required>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <option value="<?= $row['ubicaciones_id'] ?>"><?= htmlspecialchars($row['descripcion']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">Guardar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
