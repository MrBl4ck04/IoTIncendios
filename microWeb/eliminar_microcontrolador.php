<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM microcontroladores WHERE microcontroladores_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: microABM.php");
        exit;
    } else {
        echo "Error al eliminar el microcontrolador.";
    }
}
?>
