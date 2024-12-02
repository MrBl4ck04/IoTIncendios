<?php
include 'config.php';

// Obtener microcontroladores y ubicaciones
$sql = "SELECT m.microcontroladores_id, m.nombre, u.descripcion 
        FROM microcontroladores m 
        JOIN ubicaciones u ON m.ubicaciones_id = u.ubicaciones_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Microcontroladores</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Microcontroladores</h1>
        <table id="microcontroladoresTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr data-id="<?= $row['microcontroladores_id'] ?>">
                        <td><?= $row['microcontroladores_id'] ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="agregar_microcontrolador.php" class="button">Agregar Microcontrolador</a>
        <a href="agregar_ubicacion.php" class="button">Agregar Ubicación</a> <!-- Botón para agregar ubicaciones -->
    </div>

    <!-- Modal de Confirmación -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <p>¿Estás seguro de que deseas eliminar este microcontrolador?</p>
            <div class="modal-actions">
                <button id="cancelarEliminar" class="button secondary">Cancelar</button>
                <button id="confirmarEliminar" class="button">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Menú contextual -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li id="editarMicrocontrolador">Editar</li>
            <li id="eliminarMicrocontrolador">Eliminar</li>
        </ul>
    </div>

    <script>
        // Variables
        const contextMenu = document.getElementById('contextMenu');
        const modalEliminar = document.getElementById('modalEliminar');
        const confirmarEliminar = document.getElementById('confirmarEliminar');
        const cancelarEliminar = document.getElementById('cancelarEliminar');
        let selectedId = null;

        // Mostrar menú contextual
        document.getElementById('microcontroladoresTable').addEventListener('contextmenu', (e) => {
            e.preventDefault();
            const row = e.target.closest('tr');
            if (row) {
                selectedId = row.getAttribute('data-id');
                contextMenu.style.top = `${e.pageY}px`;
                contextMenu.style.left = `${e.pageX}px`;
                contextMenu.style.display = 'block';
            }
        });

        // Ocultar menú contextual al hacer clic fuera
        document.addEventListener('click', () => {
            contextMenu.style.display = 'none';
        });

        // Acción de editar
        document.getElementById('editarMicrocontrolador').addEventListener('click', () => {
            window.location.href = `editar_microcontrolador.php?id=${selectedId}`;
        });

        // Acción de eliminar
        document.getElementById('eliminarMicrocontrolador').addEventListener('click', () => {
            modalEliminar.style.display = 'flex';
        });

        // Confirmar eliminación
        confirmarEliminar.addEventListener('click', () => {
            window.location.href = `eliminar_microcontrolador.php?id=${selectedId}`;
        });

        // Cancelar eliminación
        cancelarEliminar.addEventListener('click', () => {
            modalEliminar.style.display = 'none';
        });
    </script>
</body>
</html>
