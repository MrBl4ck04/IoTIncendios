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

    if (isset($data['valor']) && isset($data['fecha']) && isset($data['hora'])) {
        $stmt = $conn->prepare("INSERT INTO temperatura (fecha, hora, valor, microcontroladores_id) VALUES (:fecha, :hora, :valor, 1)");
        $stmt->bindParam(':fecha', $data['fecha']);
        $stmt->bindParam(':hora', $data['hora']);
        $stmt->bindParam(':valor', $data['valor']);
        
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
