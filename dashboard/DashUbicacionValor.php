<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Sensores por Ubicación</title>
    <style>
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <h1>Promedio de Valores de Sensores por Ubicación</h1>
        <canvas id="sensorChart"></canvas>
    </div>

    <?php
    // Configuración de la base de datos
    $db_config = [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'onfirebd',
    ];

    // Consultas SQL para los sensores
    $queries = [
        "flama" => "
            SELECT c.valor, u.descripcion
            FROM flama c
            JOIN microcontroladores m ON c.microcontroladores_id = m.microcontroladores_id
            JOIN ubicaciones u ON m.ubicaciones_id = u.ubicaciones_id
        ",
        "humedad" => "
            SELECT h.valor, u.descripcion
            FROM humedad h
            JOIN microcontroladores m ON h.microcontroladores_id = m.microcontroladores_id
            JOIN ubicaciones u ON m.ubicaciones_id = u.ubicaciones_id
        ",
        "humo" => "
            SELECT h.valor, u.descripcion
            FROM humo h
            JOIN microcontroladores m ON h.microcontroladores_id = m.microcontroladores_id
            JOIN ubicaciones u ON m.ubicaciones_id = u.ubicaciones_id
        ",
        "temperatura" => "
            SELECT t.valor, u.descripcion
            FROM temperatura t
            JOIN microcontroladores m ON t.microcontroladores_id = m.microcontroladores_id
            JOIN ubicaciones u ON m.ubicaciones_id = u.ubicaciones_id
        ",
    ];

    $data = [];

    try {
        $conn = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
        
        foreach ($queries as $sensor => $query) {
            $result = $conn->query($query);
            $sensorData = [];
            while ($row = $result->fetch_assoc()) {
                $sensorData[] = $row;
            }
            $data[$sensor] = $sensorData;
        }

        $conn->close();
    } catch (Exception $e) {
        echo "<script>console.error('Error al cargar los datos: {$e->getMessage()}');</script>";
    }

    // Convertir los datos a JSON para JavaScript
    echo "<script>const sensorData = " . json_encode($data) . ";</script>";
    ?>

    <script>
        // Procesar los datos en JavaScript
        const ubicaciones = [];
        const flamaValues = [];
        const humedadValues = [];
        const humoValues = [];
        const temperaturaValues = [];

        for (const [sensor, values] of Object.entries(sensorData)) {
            const grouped = values.reduce((acc, item) => {
                acc[item.descripcion] = acc[item.descripcion] || [];
                acc[item.descripcion].push(parseFloat(item.valor));
                return acc;
            }, {});

            for (const [ubicacion, valores] of Object.entries(grouped)) {
                if (!ubicaciones.includes(ubicacion)) ubicaciones.push(ubicacion);

                const promedio = valores.reduce((a, b) => a + b, 0) / valores.length;
                if (sensor === 'flama') flamaValues.push(promedio);
                if (sensor === 'humedad') humedadValues.push(promedio);
                if (sensor === 'humo') humoValues.push(promedio);
                if (sensor === 'temperatura') temperaturaValues.push(promedio);
            }
        }

        // Crear el gráfico de barras
        const ctx = document.getElementById('sensorChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ubicaciones,
                datasets: [
                    { label: 'Flama', data: flamaValues, backgroundColor: 'yellow' },
                    { label: 'Humedad', data: humedadValues, backgroundColor: 'blue' },
                    { label: 'Humo', data: humoValues, backgroundColor: 'grey' },
                    { label: 'Temperatura', data: temperaturaValues, backgroundColor: 'red' },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Promedio de Valores de Sensores por Ubicación' },
                },
                scales: {
                    x: { title: { display: true, text: 'Ubicación' } },
                    y: { title: { display: true, text: 'Valor Promedio' }, beginAtZero: true },
                },
            },
        });
    </script>
     <!-- Scripts de Bootstrap -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
