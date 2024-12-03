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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .context-menu {
            position: absolute;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }
        .context-menu ul {
            list-style: none;
            padding: 5px 0;
            margin: 0;
        }
        .context-menu li {
            padding: 8px 16px;
            cursor: pointer;
        }
        .context-menu li:hover {
            background-color: #f1f1f1;
        }
        h1 {
        color: #ffffff; /* Texto en blanco */
        font-size: 2.5rem; /* Tamaño del texto (ajusta según lo necesites) */
        text-align: center; /* Centrado (opcional) */
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Sombra para mejor visibilidad */
        }
        body {
        background: linear-gradient(to bottom, #000000, #a5d6a7);
        margin: 0;
        padding: 0;
        color: #333;
        min-height: 100vh; /* Garantiza que el fondo cubra al menos la altura de la ventana */
        display: flex; /* Útil si quieres centrar contenido */
        }

    table {
        background-color: #ffffff; /* Fondo blanco para la tabla */
        border-radius: 8px; /* Bordes redondeados (opcional) */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra (opcional) */
    }

    th, td {
        background-color: #ffffff; /* Fondo blanco para las celdas */
    }
    </style>
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
                        <a class="nav-link" href="http://localhost/IoTIncendios/contacto/indexContactoAD.html">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://127.0.0.1:5501/frontend/index.html">Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px;">
        <h1>Microcontroladores</h1>
        <table id="microcontroladoresTable" class="table table-striped">
            <thead class="thead-dark">
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
        <a href="agregar_microcontrolador.php" class="btn btn-success">Agregar Microcontrolador</a>
        <a href="agregar_ubicacion.php" class="btn btn-success">Agregar Ubicación</a>
    </div>

    <!-- Modal de Confirmación -->
    <div id="modalEliminar" class="modal" style="display: none;">
        <div class="modal-content">
            <p>¿Estás seguro de que deseas eliminar este microcontrolador?</p>
            <div class="modal-actions">
                <button id="cancelarEliminar" class="btn btn-secondary">Cancelar</button>
                <button id="confirmarEliminar" class="btn btn-danger">Eliminar</button>
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
        document.addEventListener('click', (e) => {
            if (!contextMenu.contains(e.target)) {
                contextMenu.style.display = 'none';
            }
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
