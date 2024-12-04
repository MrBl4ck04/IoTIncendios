<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Sensores por Ubicación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
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
</body>
</html>
