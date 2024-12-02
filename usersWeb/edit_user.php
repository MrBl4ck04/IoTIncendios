<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
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
                echo "<script>showNotification('Error: {$conn->error}', 'error');</script>";
            }
        }
        ?>

        <form method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
            <button type="submit" class="button">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
