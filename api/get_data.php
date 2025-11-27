<?php
/**
 * SICURAH - Get Latest Sensor Data
 * Endpoint untuk mengambil data sensor terbaru dari semua node
 */

require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance();
    
    // Query untuk mengambil data sensor terbaru
    $sql = "
        SELECT 
            n.id as node_id,
            n.node_id as node_code,
            n.name as node_name,
            n.location,
            n.latitude,
            n.longitude,
            n.status as node_status,
            n.last_seen,
            s.id as sensor_id,
            s.sensor_type,
            s.unit,
            s.warning_threshold,
            s.danger_threshold,
            sr.value,
            sr.timestamp,
            CASE 
                WHEN sr.value >= s.danger_threshold THEN 'danger'
                WHEN sr.value >= s.warning_threshold THEN 'warning'
                ELSE 'safe'
            END as status
        FROM nodes n
        LEFT JOIN sensors s ON n.id = s.node_id
        LEFT JOIN (
            SELECT sensor_id, value, timestamp
            FROM sensor_readings
            WHERE (sensor_id, timestamp) IN (
                SELECT sensor_id, MAX(timestamp)
                FROM sensor_readings
                GROUP BY sensor_id
            )
        ) sr ON s.id = sr.sensor_id
        WHERE n.status = 'active' AND s.status = 'active'
        ORDER BY n.node_id, s.sensor_type
    ";
    
    $result = $db->fetchAll($sql);
    
    // Grouping data berdasarkan node
    $nodes = [];
    $summary = [
        'total_nodes' => 0,
        'online_nodes' => 0,
        'offline_nodes' => 0,
        'total_sensors' => 0,
        'safe_sensors' => 0,
        'warning_sensors' => 0,
        'danger_sensors' => 0
    ];
    
    foreach ($result as $row) {
        $nodeCode = $row['node_code'];
        
        // Initialize node jika belum ada
        if (!isset($nodes[$nodeCode])) {
            $nodes[$nodeCode] = [
                'node_id' => $row['node_id'],
                'node_code' => $nodeCode,
                'node_name' => $row['node_name'],
                'location' => $row['location'],
                'latitude' => floatval($row['latitude']),
                'longitude' => floatval($row['longitude']),
                'status' => getNodeStatus($row['last_seen']),
                'last_seen' => $row['last_seen'],
                'last_seen_ago' => timeAgo($row['last_seen']),
                'sensors' => []
            ];
            
            $summary['total_nodes']++;
            
            if ($nodes[$nodeCode]['status'] === 'online') {
                $summary['online_nodes']++;
            } else {
                $summary['offline_nodes']++;
            }
        }
        
        // Tambahkan sensor data
        if ($row['sensor_id']) {
            $sensorData = [
                'sensor_id' => $row['sensor_id'],
                'type' => $row['sensor_type'],
                'value' => floatval($row['value']),
                'unit' => $row['unit'],
                'status' => $row['status'],
                'warning_threshold' => floatval($row['warning_threshold']),
                'danger_threshold' => floatval($row['danger_threshold']),
                'timestamp' => $row['timestamp'],
                'time_ago' => timeAgo($row['timestamp'])
            ];
            
            $nodes[$nodeCode]['sensors'][] = $sensorData;
            
            // Update summary
            $summary['total_sensors']++;
            
            switch ($row['status']) {
                case 'safe':
                    $summary['safe_sensors']++;
                    break;
                case 'warning':
                    $summary['warning_sensors']++;
                    break;
                case 'danger':
                    $summary['danger_sensors']++;
                    break;
            }
        }
    }
    
    // Convert ke indexed array
    $nodesList = array_values($nodes);
    
    // Send response
    sendSuccess('Data berhasil diambil', [
        'summary' => $summary,
        'nodes' => $nodesList,
        'last_update' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    sendError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
