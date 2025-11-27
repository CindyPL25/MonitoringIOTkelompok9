<?php
/**
 * Test Script - Simulasi Data dari ESP32
 * Untuk testing tanpa hardware ESP32
 */

// Data yang akan dikirim (simulasi dari ESP32)
$testData = [
    'node_id' => 'Node-1',
    'api_key' => 'SICURAH_2024_SECRET_KEY',
    'readings' => [
        ['sensor_type' => 'rain', 'value' => 2500],
        ['sensor_type' => 'soil_moisture', 'value' => 1500],
        ['sensor_type' => 'temperature', 'value' => 28.5],
        ['sensor_type' => 'tilt', 'value' => 5.2]
    ]
];

// Kirim ke API
$ch = curl_init('http://localhost/MonitoringIOT/api/post_reading.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Tampilkan hasil
echo "HTTP Code: " . $httpCode . "\n\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
