<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "BD_Taylor";

try {
    // Crear conexión a la base de datos
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Configuración para manejar excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer el contenido de la solicitud POST
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Verificar que los datos JSON contengan las claves necesarias
    if (isset($data['Cos_Taylor'], $data['Cos_Trig'], $data['Error'], $data['RGB'], $data['NTaylor'])) {
        // Preparar la consulta SQL
        $stmt = $conn->prepare("INSERT INTO CosTaylor (Cos_Taylor, Cos_Trig, Error, RGB, NTaylor)
                                VALUES (:Cos_Taylor, :Cos_Trig, :Error, :RGB, :NTaylor)");

        // Enlazar los valores con los parámetros de la consulta
        $stmt->bindParam(':Cos_Taylor', $data['Cos_Taylor']);
        $stmt->bindParam(':Cos_Trig', $data['Cos_Trig']);
        $stmt->bindParam(':Error', $data['Error']);
        $stmt->bindParam(':RGB', $data['RGB']);
        $stmt->bindParam(':NTaylor', $data['NTaylor']);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(["message" => "Datos insertados exitosamente"]);
        } else {
            echo json_encode(["message" => "Error al insertar los datos"]);
        }
    } else {
        echo json_encode(["message" => "Datos incompletos en el JSON recibido"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Error en la conexión: " . $e->getMessage()]);
}

// Cerrar la conexión
$conn = null;
?>

