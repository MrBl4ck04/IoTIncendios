<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onfirebd";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['valor'])) {
        $microcontroladores_id = rand(1, 2);

        // Usar fecha y hora del servidor
        $stmt = $conn->prepare("
            INSERT INTO humedad (fecha, hora, valor, microcontroladores_id)
            VALUES (CURRENT_DATE(), CURRENT_TIME(), :valor, :microcontroladores_id)
        ");
        $stmt->bindParam(':valor', $data['valor']);
        $stmt->bindParam(':microcontroladores_id', $microcontroladores_id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Dato insertado exitosamente"]);
        } else {
            echo json_encode(["message" => "Error al insertar el dato"]);
        }
    } else {
        echo json_encode(["message" => "Datos incompletos"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}

$conn = null;
?>
