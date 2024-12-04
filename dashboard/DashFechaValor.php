<?php
// Configuración de la base de datos
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'onfirebd';

// Conexión a la base de datos
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener ubicaciones para el filtro
$ubicacionesQuery = "SELECT ubicaciones_id, descripcion FROM ubicaciones";
$ubicacionesResult = $conn->query($ubicacionesQuery);

$ubicaciones = [];
if ($ubicacionesResult && $ubicacionesResult->num_rows > 0) {
    while ($row = $ubicacionesResult->fetch_assoc()) {
        $ubicaciones[] = $row;
    }
}

// Manejar el filtro
$ubicacionSeleccionada = isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '' ? (int) $_GET['ubicacion'] : null;

// Consultas SQL para las tablas con o sin filtro
$queries = [
    "flama" => "SELECT CONCAT(flama.fecha, ' ', flama.hora) AS fecha_hora, flama.valor 
                FROM flama 
                INNER JOIN microcontroladores ON flama.microcontroladores_id = microcontroladores.microcontroladores_id " .
                ($ubicacionSeleccionada ? "WHERE microcontroladores.ubicaciones_id = $ubicacionSeleccionada " : "") .
                "ORDER BY flama.fecha, flama.hora",
    "humedad" => "SELECT CONCAT(humedad.fecha, ' ', humedad.hora) AS fecha_hora, humedad.valor 
                  FROM humedad 
                  INNER JOIN microcontroladores ON humedad.microcontroladores_id = microcontroladores.microcontroladores_id " .
                  ($ubicacionSeleccionada ? "WHERE microcontroladores.ubicaciones_id = $ubicacionSeleccionada " : "") .
                  "ORDER BY humedad.fecha, humedad.hora",
    "humo" => "SELECT CONCAT(humo.fecha, ' ', humo.hora) AS fecha_hora, humo.valor 
               FROM humo 
               INNER JOIN microcontroladores ON humo.microcontroladores_id = microcontroladores.microcontroladores_id " .
               ($ubicacionSeleccionada ? "WHERE microcontroladores.ubicaciones_id = $ubicacionSeleccionada " : "") .
               "ORDER BY humo.fecha, humo.hora",
    "temperatura" => "SELECT CONCAT(temperatura.fecha, ' ', temperatura.hora) AS fecha_hora, temperatura.valor 
                      FROM temperatura 
                      INNER JOIN microcontroladores ON temperatura.microcontroladores_id = microcontroladores.microcontroladores_id " .
                      ($ubicacionSeleccionada ? "WHERE microcontroladores.ubicaciones_id = $ubicacionSeleccionada " : "") .
                      "ORDER BY temperatura.fecha, temperatura.hora"
];

$data = [];
foreach ($queries as $sensor => $query) {
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$sensor][] = [
                'fecha_hora' => $row['fecha_hora'],
                'valor' => (float) $row['valor']
            ];
        }
    } else {
        $data[$sensor] = [];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Sensores</title>
    <style>
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
     <!-- Navbar principal -->
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            DashBoards
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown2">
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
    <h1>Evolución de los Sensores a lo largo del Tiempo</h1>

    <!-- Formulario para seleccionar la ubicación -->
    <form method="GET">
        <label for="ubicacion">Selecciona una ubicación:</label>
        <select name="ubicacion" id="ubicacion" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($ubicaciones as $ubicacion): ?>
                <option value="<?= $ubicacion['ubicaciones_id']; ?>" <?= $ubicacionSeleccionada == $ubicacion['ubicaciones_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($ubicacion['descripcion']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <canvas id="sensorChart" width="800" height="400"></canvas>
    </div>
    <script>
    // Datos de PHP a JavaScript
    const dataSensores = <?php echo json_encode($data); ?>;
    console.log(dataSensores); // Depuración: Verifica los datos en la consola

    // Colores asignados por sensor
    const sensorColors = {
        flama: 'rgba(255, 165, 0, 1)', // Amarillo oscuro
        temperatura: 'rgba(255, 0, 0, 1)', // Rojo
        humo: 'rgba(128, 128, 128, 1)', // Plomo
        humedad: 'rgba(0, 0, 255, 1)', // Azul
    };

    // Configuración de los datasets para Chart.js
    const datasets = Object.keys(dataSensores).map(sensor => {
        const sensorData = dataSensores[sensor];
        return {
            label: sensor.charAt(0).toUpperCase() + sensor.slice(1), // Capitaliza el nombre del sensor
            data: sensorData.map(d => ({ x: new Date(d.fecha_hora), y: d.valor })), // Datos mapeados
            borderColor: sensorColors[sensor] || 'rgba(0, 0, 0, 1)', // Color asignado o negro como fallback
            fill: false
        };
    });

    // Crear el gráfico
    const ctx = document.getElementById('sensorChart').getContext('2d');
    const miGrafica = new Chart(ctx, {
        type: 'line', // Tipo de gráfico
        data: {
            datasets: datasets, // Usa los datasets generados dinámicamente
        },
        options: {
            scales: {
                x: {
                    type: 'time', // Escala de tiempo
                    time: {
                        unit: 'day', // Cambia según la unidad que desees
                    },
                },
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
</script>
 <!-- Scripts de Bootstrap -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
