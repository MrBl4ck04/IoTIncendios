<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
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
                    window.location.href = 'usersABM.php';
                }
            }, 3000);
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="http://127.0.0.1:5501/frontend/indexAdmin.html">OnFire</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/index.php">Puntos de alerta</a>
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
        <h1>Modificar Usuario</h1>

        <?php
        $id = $_GET['id'];
        $sql = "SELECT * FROM usuarios WHERE usuarios_id = $id";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'];
            $password = $_POST['password'];

            $sql = "UPDATE usuarios SET usuario='$usuario', password='$password' WHERE usuarios_id=$id";
            if ($conn->query($sql) === TRUE) {
                echo "<script>showNotification('Usuario modificado exitosamente.', 'success');</script>";
            } else {
                echo "<script>showNotification('Error: {$conn->error}', 'danger');</script>";
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
