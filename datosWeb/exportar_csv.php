<?php
include 'config.php';

$sensor = $_GET['sensor'] ?? '';

$sql = "SELECT * FROM $sensor";
$result = $conn->query($sql);
$columns = array_keys($result->fetch_assoc());

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$sensor.'_data.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, $columns);

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
?>
