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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Datos del Sensor: <?= ucfirst($sensor) ?></h1>
        
        <button onclick="window.location.href='agregar_dato.php?sensor=<?= $sensor ?>'">Agregar Dato</button>
        <button onclick="window.location.href='exportar_csv.php?sensor=<?= $sensor ?>'">Exportar a CSV</button>

        <!-- Notificación -->
        <?php if (isset($mensaje)): ?>
            <div class="notificacion"><?= $mensaje ?></div>
        <?php endif; ?>

        <table>
            <thead>
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
                            <div id="menu-contextual-<?= $row[$columns[0]] ?>" class="menu-contextual" oncontextmenu="evitarCierreMenu(event)">
                                <a href="modificar_dato.php?sensor=<?= $sensor ?>&id=<?= $row[$columns[0]] ?>">Modificar</a>
                                <a href="javascript:void(0);" onclick="mostrarModal('<?= $row[$columns[0]] ?>')">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Confirmación -->
    <div id="modal-confirmacion" class="modal">
        <div class="modal-content">
            <p>¿Estás seguro de que deseas eliminar este dato?</p>
            <div class="modal-actions">
                <form method="POST">
                    <input type="hidden" name="id" id="dato-id">
                    <button type="submit" name="confirmar_eliminacion" class="button">Eliminar</button>
                </form>
                <button id="cancelarEliminar" class="button secondary" onclick="cerrarModal()">Cancelar</button>
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
            document.getElementById('modal-confirmacion').style.display = 'flex';
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modal-confirmacion').style.display = 'none';
        }

        // Función para mostrar el menú contextual
        function mostrarMenuContextual(event, id) {
            event.preventDefault();
            
            var menu = document.getElementById('menu-contextual-' + id);
            menu.style.display = 'block';
            menu.style.left = event.pageX + 'px';
            menu.style.top = event.pageY + 'px';
        }

        // Función para prevenir el cierre inmediato del menú
        function evitarCierreMenu(event) {
            event.stopPropagation();
        }

        // Cerrar el menú contextual al hacer clic fuera
        document.addEventListener('click', function() {
            var menus = document.querySelectorAll('.menu-contextual');
            menus.forEach(function(menu) {
                menu.style.display = 'none';
            });
        });
    </script>
</body>
</html>
