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

// Obtener datos si hay solicitud de sensor y ubicación
$sensor = isset($_GET['sensor']) ? $_GET['sensor'] : 'todos';
$ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : 'todas';

$data = [];
$promedios = []; // Para almacenar los promedios por sensor

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
            ) / 4 AS promedio
        FROM microcontroladores
        JOIN ubicaciones ON microcontroladores.ubicaciones_id = ubicaciones.ubicaciones_id
    ";
    if ($ubicacion !== 'todas') {
        $query .= " WHERE ubicaciones.ubicaciones_id = $ubicacion";
    }
} else {
    $query = "
        SELECT 
            ubicaciones.latitud, 
            ubicaciones.longitud, 
            microcontroladores.microcontroladores_id AS micro_id, 
            '$sensor' AS tipo,
            (
                SELECT AVG(valor) 
                FROM $sensor 
                WHERE $sensor.microcontroladores_id = microcontroladores.microcontroladores_id 
                ORDER BY fecha DESC, hora DESC
                LIMIT 20
            ) AS promedio
        FROM microcontroladores
        JOIN ubicaciones ON microcontroladores.ubicaciones_id = ubicaciones.ubicaciones_id
    ";
    if ($ubicacion !== 'todas') {
        $query .= " WHERE ubicaciones.ubicaciones_id = $ubicacion";
    }
}

// Calcular promedios globales por sensor
$sensores = ['flama', 'humedad', 'humo', 'temperatura'];
foreach ($sensores as $tipo) {
    $promedioQuery = "SELECT AVG(valor) AS promedio FROM $tipo";
    $result = $conn->query($promedioQuery);
    $row = $result->fetch_assoc();
    $promedios[$tipo] = round($row['promedio'], 2);
}

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Obtener ubicaciones para el filtro
$ubicaciones = [];
$ubicacionQuery = "SELECT ubicaciones_id, descripcion FROM ubicaciones";
$ubicacionResult = $conn->query($ubicacionQuery);

if ($ubicacionResult && $ubicacionResult->num_rows > 0) {
    while ($row = $ubicacionResult->fetch_assoc()) {
        $ubicaciones[] = $row;
    }
}

$conn->close();

// Si es una solicitud AJAX, devolver datos en JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Pasar promedios a JavaScript
echo "<script>const promedios = " . json_encode($promedios) . ";</script>";
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
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../frontend/index.html">OnFire</a>
    
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarOpen">
                <span class="navbar-toggler-icon"></span>
            </button>
    
            <div class="collapse navbar-collapse" id="navbarOpen">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="goBack()">Volver</a>
                    </li>
                </ul>
            </div>
    
        </div>
    </nav>
    <div class="container" style="margin-top: 100px;"></div>
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
        <label for="ubicacion">Filtrar por ubicación:</label>
        <select id="ubicacion" onchange="updateMap()">
            <option value="todas">Todas</option>
        </select>
    </div>
    <div id="map"></div>
    </div>
    <script>
        const map = L.map('map').setView([-16.0654, -61.0579], 8); // Coordenadas iniciales
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let markers = [];
        let circles = [];

        // Agregar ubicaciones al filtro
        const ubicaciones = <?php echo json_encode($ubicaciones); ?>;
        const ubicacionSelect = document.getElementById('ubicacion');
        ubicaciones.forEach(ubicacion => {
            const option = document.createElement('option');
            option.value = ubicacion.ubicaciones_id;
            option.textContent = ubicacion.descripcion;
            ubicacionSelect.appendChild(option);
        });

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
            const ubicacion = document.getElementById('ubicacion').value;

            // Realizar una solicitud AJAX para obtener los datos
            fetch(`index.php?sensor=${sensor}&ubicacion=${ubicacion}&ajax=1`)
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

                        const radius = location.promedio ? location.promedio * 10 : 10;
                        const circle = L.circle([location.latitud, location.longitud], {
                            color: getColorBySensor(location.tipo),
                            fillColor: getColorBySensor(location.tipo),
                            fillOpacity: 0.3,
                            radius: radius
                        }).addTo(map);

                        markers.push(marker);
                        circles.push(circle);
                    });
                })
                .catch(err => console.error('Error al obtener los datos:', err));
        }

        // Cargar todos los puntos al inicio
        updateMap();

        // Crear el control de la leyenda
    const legend = L.control({ position: 'bottomright' });

legend.onAdd = function (map) {
    const div = L.DomUtil.create('div', 'info legend');
    const sensors = ['flama', 'temperatura', 'humo', 'humedad'];
    const labels = ['flama: nm', 'temperatura: celcius', 'humo: kg/m3', 'humedad: %'];
    const colors = ['orange', 'red', 'gray', 'blue'];

    div.innerHTML = `<h4>Promedios por Sensor</h4>`;
            for (let i = 0; i < sensors.length; i++) {
                div.innerHTML += `
                    <i style="background: ${colors[i]}; width: 18px; height: 18px; display: inline-block; margin-right: 5px; border-radius: 3px;"></i>
                    ${labels[i]}: ${promedios[sensors[i]] || 'N/A'}<br>`;
            }
            return div;
        };
// Añadir la leyenda al mapa
legend.addTo(map);



    </script>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>

    <!-- Scripts de Bootstrap -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
