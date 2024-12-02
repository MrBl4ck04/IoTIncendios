<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="styles.css">
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
</head>
<body>
    <div class="container">
        <h1>Gestión de Usuarios</h1>
        <a href="add_user.php" class="button">Agregar Usuario</a>
        <table id="usuariosTable">
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
        <div id="contextMenu" class="context-menu">
            <ul>
                <li id="editUser">Modificar</li>
                <li id="deleteUser">Eliminar</li>
            </ul>
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <h2>Confirmar Eliminación</h2>
                <p>¿Estás seguro de que deseas eliminar este usuario?</p>
                <div class="modal-actions">
                    <button id="cancelDelete" onclick="closeModal()" class="button secondary">Cancelar</button>
                    <button id="confirmDelete" class="button delete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

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
