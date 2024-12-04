<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';
$id = $_GET['id'] ?? '';

// Obtener datos del sensor
$sql = "SELECT * FROM $sensor";
$result = $conn->query($sql);

// Obtener las columnas de la tabla
$columns = [];
if ($result && $result->num_rows > 0) {
    $columns = array_keys($result->fetch_assoc());
}

if ($sensor && $id && isset($_POST['confirmar_eliminacion'])) {
    $sql = "DELETE FROM $sensor WHERE {$sensor}_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensaje = "Dato eliminado exitosamente";
    } else {
        $mensaje = "Error al eliminar el dato";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos del Sensor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        h1 {
            color: #ffffff; /* Texto en blanco */
            font-size: 2.5rem; /* Tamaño del texto */
            text-align: center; /* Centrado */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Sombra */
        }

        body {
            background: linear-gradient(to bottom, #181d27, #254d32, #3a7d44, #69b578, #d0db97);
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
    <!-- Navbar principal con enlaces -->
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
        <h1 class="my-4">Datos del Sensor: <?= ucfirst($sensor) ?></h1>
        
        <div class="mb-4">
            <button onclick="window.location.href='agregar_dato.php?sensor=<?= $sensor ?>'" class="btn btn-success">Agregar Dato</button>
            <button onclick="window.location.href='exportar_csv.php?sensor=<?= $sensor ?>'" class="btn btn-success">Exportar a CSV</button>
        </div>

        <!-- Notificación -->
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?= $mensaje ?></div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?= ucfirst($column) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row[$columns[0]] ?>" oncontextmenu="mostrarMenuContextual(event, '<?= $row[$columns[0]] ?>')">
                        <?php foreach ($row as $value): ?>
                            <td><?= $value ?></td>
                        <?php endforeach; ?>
                        <td>
                            <!-- Menú contextual dentro de cada fila -->
                            <div id="menu-contextual-<?= $row[$columns[0]] ?>" class="dropdown-menu" style="display: none;">
                                <a class="dropdown-item" href="modificar_dato.php?sensor=<?= $sensor ?>&id=<?= $row[$columns[0]] ?>">Modificar</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="mostrarModal('<?= $row[$columns[0]] ?>')">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Confirmación -->
    <div id="modal-confirmacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacionLabel">Confirmación de Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este dato?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="id" id="dato-id">
                        <button type="submit" name="confirmar_eliminacion" class="btn btn-danger">Eliminar</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables
        let selectedId = null;

        // Función para mostrar el modal de confirmación
        function mostrarModal(id) {
            selectedId = id;
            document.getElementById('dato-id').value = selectedId;
            $('#modal-confirmacion').modal('show');
        }

        // Función para mostrar el menú contextual
        function mostrarMenuContextual(event, id) {
            event.preventDefault();
            
            var menu = document.getElementById('menu-contextual-' + id);
            menu.style.display = 'block';
            menu.style.left = event.pageX + 'px';
            menu.style.top = event.pageY + 'px';
        }

        // Cerrar el menú contextual al hacer clic fuera
        document.addEventListener('click', function() {
            var menus = document.querySelectorAll('.dropdown-menu');
            menus.forEach(function(menu) {
                menu.style.display = 'none';
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
