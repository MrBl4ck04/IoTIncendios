<?php
include 'db_connection.php';

$id = $_GET['id'];
$sql = "DELETE FROM usuarios WHERE usuarios_id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: usersABM.php");
} else {
    echo "Error al eliminar: " . $conn->error;
}
?>
