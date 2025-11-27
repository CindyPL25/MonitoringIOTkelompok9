<?php
/**
 * Test Database Connection
 */
require_once __DIR__ . '/api/config.php';

try {
    $db = Database::getInstance();
    echo "✅ Database connected!\n\n";
    
    // Test 1: Cek node Node-1
    echo "TEST 1: Cek Node-1\n";
    $nodeSql = "SELECT * FROM nodes WHERE node_id = 'Node-1'";
    $node = $db->fetchOne($nodeSql);
    if ($node) {
        echo "✅ Node-1 ditemukan (ID: {$node['id']})\n";
        echo "   Name: {$node['name']}\n";
        echo "   Location: {$node['location']}\n\n";
    } else {
        echo "❌ Node-1 tidak ditemukan!\n\n";
    }
    
    // Test 2: Cek sensors untuk Node-1
    echo "TEST 2: Cek Sensors Node-1\n";
    $sensorsSql = "SELECT * FROM sensors WHERE node_id = {$node['id']}";
    $sensors = $db->fetchAll($sensorsSql);
    echo "Jumlah sensor: " . count($sensors) . "\n";
    foreach ($sensors as $sensor) {
        echo "  - {$sensor['sensor_type']} (ID: {$sensor['id']}, Status: {$sensor['status']})\n";
    }
    echo "\n";
    
    // Test 3: Cek latest readings
    echo "TEST 3: Latest Readings\n";
    $readingsSql = "
        SELECT sr.*, s.sensor_type, n.node_id 
        FROM sensor_readings sr
        JOIN sensors s ON sr.sensor_id = s.id
        JOIN nodes n ON s.node_id = n.id
        WHERE n.node_id = 'Node-1'
        ORDER BY sr.timestamp DESC
        LIMIT 10
    ";
    $readings = $db->fetchAll($readingsSql);
    echo "Latest 10 readings:\n";
    foreach ($readings as $r) {
        echo "  - {$r['sensor_type']}: {$r['value']} ({$r['timestamp']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
