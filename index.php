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
            color: #ffffff;
        }
        #filter {
            margin-bottom: 10px;
            color: #ffffff;

        }
        #map {
            width: 70%;
            height: 700px;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
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
        <label for="ubicacion">Filtrar por ubicación:</label>
        <select id="ubicacion" onchange="updateMap()">
            <option value="todas">Todas</option>
        </select>
    </div>
    <div id="map"></div>
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
        console.log('Promedio:', location.promedio);  // Verificar los promedios
        
        const marker = L.marker([location.latitud, location.longitud])
            .addTo(map)
            .bindPopup(`
                Microcontrolador ID: ${location.micro_id}<br>
                Sensor: ${location.tipo}<br>
                Promedio: ${location.promedio || 'N/A'}
            `);

        // Verificar el tipo de sensor y ajustar el tamaño del círculo
        let radius;
        if (location.tipo === 'temperatura') {
            radius = location.promedio ? location.promedio * 800 : 10; // Multiplicar por 1000 para temperatura
        } else if (location.tipo === 'humedad') {
            radius = location.promedio ? location.promedio * 250 : 10; // Multiplicar por 500 para humedad
        } else if (location.tipo === 'flama') {
            radius = location.promedio ? location.promedio * 10 : 10; // Multiplicar por 100 para flama
        } else if (location.tipo === 'humo') {
            radius = location.promedio ? location.promedio * 50 : 10; // Multiplicar por 10 para humo
        } else {
            radius = 10; // Valor predeterminado en caso de que no coincida con ningún tipo
        }

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
    <!-- Botón circular flotante -->
<div id="help-button" onclick="openModal()">?</div>

<!-- Modal -->
<div id="info-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Información Importante</h2>
        <div class="info-box">
    <p>
        El siguiente mapa muestra los promedios de valores en los sensores <strong>FLAMA</strong>, <strong>TEMPERATURA</strong>, <strong>HUMEDAD</strong> y <strong>HUMO</strong>.<br><br>
        Cada burbuja, representada por un color específico, indica la magnitud de los valores del sensor. Mientras más grandes sean los valores, mayor será el tamaño de la burbuja.<br><br>
        <strong>Nota:</strong> En el sensor de <strong>flama</strong>, una burbuja más pequeña significa que el fuego está más cerca.
        Asimismo considerar el sensor de <strong>humedad</strong> , mientras mas grande la burbuja indica mayor humedad <strong>menor riesgo</strong> de incendio.
    </p>
</div>
    </div>
</div>

<!-- Agrega esto en la sección <style> existente -->
<style>
    /* Botón circular */
    #help-button {
        position: fixed;
        top: 780px;
        right: 200px;
        width: 50px;
        height: 50px;
        background-color: #254d32;
        color: white;
        font-size: 24px;
        font-weight: bold;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }
    .info-box {
        background-color: #fff;  /* Fondo blanco o color claro */
        padding: 20px;
        margin: 20px auto;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        max-width: 600px; /* Ancho máximo para centrar */
        font-family: Arial, sans-serif; /* Fuente simple y clara */
        line-height: 1.6; /* Espaciado entre líneas */
        color: #333; /* Color de texto oscuro */
        text-align: justify; /* Alineación justificada */
    }

    .info-box p {
        margin: 0; /* Eliminar margen por defecto */
    }

    .info-box strong {
        color: #254d32; /* Color distintivo para palabras clave */
    }
    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1001;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 20px;
        border-radius: 10px;
        width: 400px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-btn:hover {
        color: black;
    }
</style>

<!-- Agrega esto antes de cerrar </body> -->
<script>
    // Abrir modal
    function openModal() {
        document.getElementById('info-modal').style.display = 'flex';
    }

    // Cerrar modal
    function closeModal() {
        document.getElementById('info-modal').style.display = 'none';
    }

    // Cerrar modal al hacer clic fuera del contenido
    window.onclick = function(event) {
        const modal = document.getElementById('info-modal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

</body>
</html>
