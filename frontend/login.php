<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onfirebd";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]));
}

// Obtener los datos enviados por el cliente
$data = json_decode(file_get_contents("php://input"), true);
$usuario = $data['usuario'];
$password = $data['password'];

// Consulta para verificar las credenciales
$sql = "SELECT * FROM usuarios WHERE usuario = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Credenciales incorrectas"]);
}

$stmt->close();
$conn->close();
