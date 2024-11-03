<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onfireBD";

try {
    // Crear conexión a la base de datos
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer el contenido de la solicitud POST
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Verificar que los datos JSON contengan las claves necesarias
if (isset($data['Cos_Taylor'])) {  // Cambia 'valor' por 'Cos_Taylor'
    // Definir la fecha y la hora actuales
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    // Preparar la inserción en la tabla 
    $stmt = $conn->prepare("INSERT INTO humo (fecha, hora, valor) VALUES (:fecha, :hora, :valor)");
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':hora', $hora);
    $stmt->bindParam(':valor', $data['Cos_Taylor']);  // Cambia 'valor' por 'Cos_Taylor'

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(["message" => "Dato de temperatura insertado exitosamente"]);
    } else {
        echo json_encode(["message" => "Error al insertar el dato de temperatura"]);
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
