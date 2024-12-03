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

// Consultas SQL para las tablas
$queries = [
    "flama" => "SELECT CONCAT(fecha, ' ', hora) AS fecha_hora, valor FROM flama ORDER BY fecha, hora",
    "humedad" => "SELECT CONCAT(fecha, ' ', hora) AS fecha_hora, valor FROM humedad ORDER BY fecha, hora",
    "humo" => "SELECT CONCAT(fecha, ' ', hora) AS fecha_hora, valor FROM humo ORDER BY fecha, hora",
    "temperatura" => "SELECT CONCAT(fecha, ' ', hora) AS fecha_hora, valor FROM temperatura ORDER BY fecha, hora"
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
    <h1>Evolución de los Sensores a lo largo del Tiempo</h1>
    <canvas id="sensorChart" width="800" height="400"></canvas>
    <script>
        // Datos de PHP a JavaScript
        const dataSensores = <?php echo json_encode($data); ?>;
        console.log(dataSensores); // Depuración: Verifica los datos en la consola

        // Verifica que hay datos antes de graficar
        if (Object.keys(dataSensores).length === 0) {
            alert('No hay datos disponibles para mostrar.');
        }

        // Configuración de los datasets para Chart.js
        const datasets = Object.keys(dataSensores).map(sensor => {
            const sensorData = dataSensores[sensor];
            return {
                label: sensor.charAt(0).toUpperCase() + sensor.slice(1),
                data: sensorData.map(d => ({ x: new Date(d.fecha_hora), y: d.valor })),
                borderColor: getRandomColor(),
                fill: false
            };
        });

        // Función para generar colores aleatorios
        function getRandomColor() {
            return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 1)`;
        }

        // Crear el gráfico
        const ctx = document.getElementById('sensorChart').getContext('2d'); // Asegúrate de que el ID sea correcto
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

