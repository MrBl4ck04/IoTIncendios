<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';
$id = $_GET['id'] ?? '';

if ($sensor && $id) {
    $sql = "DELETE FROM $sensor WHERE {$sensor}_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Dato eliminado exitosamente'); window.location.href='ver_datos.php?sensor=$sensor';</script>";
    } else {
        echo "<script>alert('Error al eliminar el dato');</script>";
    }
}
?>
