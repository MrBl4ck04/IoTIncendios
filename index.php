<?php
// Configuración de conexión a la base de datos
$host = "localhost";
$user = "root"; // Cambiar por el usuario de tu base de datos
$password = ""; // Cambiar por la contraseña de tu base de datos
$database = "onfirebd";

$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos si hay solicitud de sensor
$sensor = isset($_GET['sensor']) ? $_GET['sensor'] : 'todos';
$data = [];

if ($sensor === 'todos') {
    $query = "
        SELECT 
            ubicaciones.latitud, 
            ubicaciones.longitud, 
            microcontroladores.microcontroladores_id AS micro_id, 
            'todos' AS tipo,
            (
                COALESCE((SELECT AVG(valor) FROM flama WHERE flama.microcontroladores_id = microcontroladores.microcontroladores_id ORDER BY fecha DESC, hora DESC LIMIT 20), 0) +
                COALESCE((SELECT AVG(valor) FROM humedad WHERE humedad.microcontroladores_id = microcontroladores.microcontroladores_id ORDER BY fecha DESC, hora DESC LIMIT 20), 0) +
                COALESCE((SELECT AVG(valor) FROM humo WHERE humo.microcontroladores_id = microcontroladores.microcontroladores_id ORDER BY fecha DESC, hora DESC LIMIT 20), 0) +
                COALESCE((SELECT AVG(valor) FROM temperatura WHERE temperatura.microcontroladores_id = microcontroladores.microcontroladores_id ORDER BY fecha DESC, hora DESC LIMIT 20), 0)
            ) / 4 AS promedio -- Promedio de todos los sensores
        FROM microcontroladores
        JOIN ubicaciones ON microcontroladores.ubicaciones_id = ubicaciones.ubicaciones_id
    ";
} else {
    $query = "
        SELECT 
            ubicaciones.latitud, 
            ubicaciones.longitud, 
            microcontroladores.microcontroladores_id AS micro_id, 
            '$sensor' AS tipo,
            AVG($sensor.valor) AS promedio -- Promedio de los últimos valores del sensor seleccionado
        FROM microcontroladores
        JOIN ubicaciones ON microcontroladores.ubicaciones_id = ubicaciones.ubicaciones_id
        JOIN (
            SELECT * 
            FROM $sensor
            WHERE $sensor.microcontroladores_id = microcontroladores.microcontroladores_id
            ORDER BY $sensor.fecha DESC, $sensor.hora DESC
            LIMIT 20
        ) AS ultimos ON ultimos.microcontroladores_id = microcontroladores.microcontroladores_id
        GROUP BY microcontroladores.microcontroladores_id
    ";
}
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

// Si es una solicitud AJAX, devolver datos en JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puntos de Alerta</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f4;
        }
        h1 {
            margin: 20px;
            color: #333;
        }
        #filter {
            margin-bottom: 10px;
        }
        #map {
            width: 90%;
            height: 500px;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <h1>Puntos de Alerta</h1>
    <div id="filter">
        <label for="sensor">Filtrar por sensor:</label>
        <select id="sensor" onchange="updateMap()">
            <option value="todos">Todos</option>
            <option value="flama">Flama</option>
            <option value="temperatura">Temperatura</option>
            <option value="humo">Humo</option>
            <option value="humedad">Humedad</option>
        </select>
    </div>
    <div id="map"></div>
    <script>
        const map = L.map('map').setView([-16.5125, -68.1235], 15); // Coordenadas del Bosquecillo de Auquisamaña
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let markers = [];
        let circles = [];

        // Función para obtener color según el tipo de sensor
        const getColorBySensor = (sensor) => {
            switch(sensor) {
                case 'flama': return 'orange';
                case 'temperatura': return 'red';
                case 'humo': return 'gray';
                case 'humedad': return 'blue';
                default: return 'green';
            }
        };

        function updateMap() {
    const sensor = document.getElementById('sensor').value;

    // Realizar una solicitud AJAX para obtener los datos
    fetch(`index.php?sensor=${sensor}&ajax=1`)
        .then(response => response.json())
        .then(data => {
            // Eliminar marcadores y círculos existentes
            markers.forEach(marker => map.removeLayer(marker));
            circles.forEach(circle => map.removeLayer(circle));
            markers = [];
            circles = [];

            // Agregar nuevos marcadores y círculos
            data.forEach(location => {
                const marker = L.marker([location.latitud, location.longitud])
                    .addTo(map)
                    .bindPopup(`
                        Microcontrolador ID: ${location.micro_id}<br>
                        Sensor: ${location.tipo}<br>
                        Promedio: ${location.promedio || 'N/A'}
                    `);
                
                // Ajustar el tamaño del círculo según el promedio
                const baseRadius = 5; // Tamaño base mínimo
                const scaleFactor = 2; // Factor para escalar el promedio
                const radius = location.promedio ? location.promedio * 0.5 : 10; // Más pequeño
const circle = L.circle([location.latitud, location.longitud], {
    color: getColorBySensor(location.tipo), // Color dinámico
    fillColor: getColorBySensor(location.tipo),
    fillOpacity: 0.3, // Transparencia del círculo
    radius: radius // Radio ajustado
}).addTo(map);


                markers.push(marker);
                circles.push(circle);
            });
        })
        .catch(err => console.error('Error al obtener los datos:', err));
}


        // Cargar todos los puntos al inicio
        updateMap();
    </script>
</body>
</html>
