<?php
/**
 * SICURAH - Get Nodes/Locations
 * Endpoint untuk mengambil semua lokasi node
 */

require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance();
    
    // Query untuk mengambil data node dengan status sensor terbaru
    $sql = "
        SELECT 
            n.id,
            n.node_id,
            n.name,
            n.location,
            n.latitude,
            n.longitude,
            n.status,
            n.last_seen,
            COUNT(s.id) as total_sensors,
            SUM(CASE 
                WHEN sr.value >= s.danger_threshold THEN 1 
                ELSE 0 
            END) as danger_count,
            SUM(CASE 
                WHEN sr.value >= s.warning_threshold AND sr.value < s.danger_threshold THEN 1 
                ELSE 0 
            END) as warning_count
        FROM nodes n
        LEFT JOIN sensors s ON n.id = s.node_id AND s.status = 'active'
        LEFT JOIN (
            SELECT sensor_id, value, timestamp
            FROM sensor_readings
            WHERE (sensor_id, timestamp) IN (
                SELECT sensor_id, MAX(timestamp)
                FROM sensor_readings
                GROUP BY sensor_id
            )
        ) sr ON s.id = sr.sensor_id
        WHERE n.status = 'active'
        GROUP BY n.id
        ORDER BY n.node_id
    ";
    
    $nodes = $db->fetchAll($sql);
    
    // Format data untuk setiap node
    $formattedNodes = [];
    
    foreach ($nodes as $node) {
        $nodeStatus = getNodeStatus($node['last_seen']);
        
        // Tentukan overall status berdasarkan sensor readings
        $overallStatus = 'safe';
        if ($node['danger_count'] > 0) {
            $overallStatus = 'danger';
        } elseif ($node['warning_count'] > 0) {
            $overallStatus = 'warning';
        }
        
        // Get detail sensors untuk node ini
        $sensorsSql = "
            SELECT 
                s.sensor_type,
                s.unit,
                sr.value,
                sr.timestamp,
                CASE 
                    WHEN sr.value >= s.danger_threshold THEN 'danger'
                    WHEN sr.value >= s.warning_threshold THEN 'warning'
                    ELSE 'safe'
                END as status
            FROM sensors s
            LEFT JOIN (
                SELECT sensor_id, value, timestamp
                FROM sensor_readings
                WHERE (sensor_id, timestamp) IN (
                    SELECT sensor_id, MAX(timestamp)
                    FROM sensor_readings
                    GROUP BY sensor_id
                )
            ) sr ON s.id = sr.sensor_id
            WHERE s.node_id = {$node['id']} AND s.status = 'active'
            ORDER BY s.sensor_type
        ";
        
        $sensors = $db->fetchAll($sensorsSql);
        
        $formattedSensors = [];
        foreach ($sensors as $sensor) {
            $formattedSensors[] = [
                'type' => $sensor['sensor_type'],
                'value' => floatval($sensor['value']),
                'unit' => $sensor['unit'],
                'status' => $sensor['status'],
                'timestamp' => $sensor['timestamp']
            ];
        }
        
        $formattedNodes[] = [
            'id' => $node['id'],
            'node_id' => $node['node_id'],
            'name' => $node['name'],
            'location' => $node['location'],
            'latitude' => floatval($node['latitude']),
            'longitude' => floatval($node['longitude']),
            'status' => $nodeStatus,
            'overall_status' => $overallStatus,
            'last_seen' => $node['last_seen'],
            'last_seen_ago' => timeAgo($node['last_seen']),
            'total_sensors' => intval($node['total_sensors']),
            'danger_count' => intval($node['danger_count']),
            'warning_count' => intval($node['warning_count']),
            'sensors' => $formattedSensors
        ];
    }
    
    sendSuccess('Data lokasi node berhasil diambil', [
        'total_nodes' => count($formattedNodes),
        'nodes' => $formattedNodes
    ]);
    
} catch (Exception $e) {
    sendError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
