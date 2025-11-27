<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $device_id   = $_POST['device_id'] ?? 0;
    $temperature = $_POST['temperature'] ?? null;
    $humidity    = $_POST['humidity'] ?? null;
    $distance_cm = $_POST['distance_cm'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO sensor_readings (device_id, temperature, humidity, distance_cm)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iddd", $device_id, $temperature, $humidity, $distance_cm);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "ERROR: " . $conn->error;
    }

    $stmt->close();
}
?>