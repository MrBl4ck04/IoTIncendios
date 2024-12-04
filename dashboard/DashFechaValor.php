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
    <link rel="stylesheet" href="./style.css"> <!-- Vincula el archivo de estilos externo -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
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

</body>
</html>
