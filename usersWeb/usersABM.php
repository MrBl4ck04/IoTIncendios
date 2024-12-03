<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script>
        function openDeleteModal(userId) {
            const modal = document.getElementById('deleteModal');
            const confirmButton = document.getElementById('confirmDelete');
            modal.style.display = 'block';

            confirmButton.onclick = function () {
                window.location.href = `delete_user.php?id=${userId}`;
            };
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
    </script>
    <style>
        h1 {
            color: #ffffff; /* Texto en blanco */
            font-size: 2.5rem; /* Tamaño del texto */
            text-align: center; /* Centrado */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Sombra */
        }

        body {
            background: linear-gradient(to bottom, #000000, #a5d6a7);
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh; /* Garantiza que el fondo cubra toda la pantalla */
            display: flex;
            flex-direction: column; /* Organiza verticalmente el contenido */
        }

        table {
            background-color: #ffffff; /* Fondo blanco para la tabla */
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra */
        }

        th, td {
            background-color: #ffffff; /* Fondo blanco para las celdas */
        }
    </style>
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
        <h1>Gestión de Usuarios</h1>
        <a href="add_user.php" class="btn btn-success mb-3">Agregar Usuario</a>
        <table class="table table-striped" id="usuariosTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Contraseña</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM usuarios";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-id='{$row['usuarios_id']}'>
                                <td>{$row['usuarios_id']}</td>
                                <td>{$row['usuario']}</td>
                                <td>{$row['password']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No hay usuarios registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Menú contextual -->
        <div id="contextMenu" class="dropdown-menu" style="position: absolute; display: none;">
            <a class="dropdown-item" href="#" id="editUser">Modificar</a>
            <a class="dropdown-item" href="#" id="deleteUser">Eliminar</a>
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div id="deleteModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Eliminación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="closeModal()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este usuario?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                        <button type="button" id="confirmDelete" class="btn btn-danger">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Variables
        const contextMenu = document.getElementById('contextMenu');
        let selectedUserId = null;

        // Mostrar menú contextual
        document.querySelector('#usuariosTable tbody').addEventListener('contextmenu', function (e) {
            e.preventDefault();
            const row = e.target.closest('tr');
            if (row) {
                selectedUserId = row.getAttribute('data-id');
                contextMenu.style.top = `${e.pageY}px`;
                contextMenu.style.left = `${e.pageX}px`;
                contextMenu.style.display = 'block';
            }
        });

        // Ocultar menú contextual al hacer clic fuera
        document.addEventListener('click', function () {
            contextMenu.style.display = 'none';
        });

        // Acciones del menú contextual
        document.getElementById('editUser').addEventListener('click', function () {
            window.location.href = `edit_user.php?id=${selectedUserId}`;
        });

        document.getElementById('deleteUser').addEventListener('click', function () {
            openDeleteModal(selectedUserId);
        });
    </script>
</body>
</html>
