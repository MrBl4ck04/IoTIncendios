<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
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
    <div class="container">
        <h1>Agregar Usuario</h1>
        <form method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" required>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" required>
            <button type="submit" class="button">Guardar</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'];
            $password = $_POST['password'];

            $sql = "INSERT INTO usuarios (usuario, password) VALUES ('$usuario', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "<script>showNotification('Usuario agregado exitosamente.', 'success');</script>";
            } else {
                echo "<script>showNotification('Error: {$conn->error}', 'error');</script>";
            }
        }
        ?>
    </div>
</body>
</html>
