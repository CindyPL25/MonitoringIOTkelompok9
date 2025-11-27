<?php
/**
 * SICURAH - Get Historical Sensor Data
 * Endpoint untuk mengambil data historis sensor
 */

require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance();
    
    // Parameter dari GET request
    $nodeId = isset($_GET['node_id']) ? $_GET['node_id'] : null;
    $sensorType = isset($_GET['sensor_type']) ? $_GET['sensor_type'] : null;
    $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24; // Default 24 jam
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100; // Default 100 data points
    
    // Build WHERE clause
    $whereConditions = ["sr.timestamp >= DATE_SUB(NOW(), INTERVAL $hours HOUR)"];
    
    if ($nodeId) {
        $nodeId = $db->escape($nodeId);
        $whereConditions[] = "n.node_id = '$nodeId'";
    }
    
    if ($sensorType) {
        $sensorType = $db->escape($sensorType);
        $whereConditions[] = "s.sensor_type = '$sensorType'";
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Query untuk mengambil data historis
    $sql = "
        SELECT 
            n.node_id,
            n.name as node_name,
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
        FROM sensor_readings sr
        JOIN sensors s ON sr.sensor_id = s.id
        JOIN nodes n ON s.node_id = n.id
        WHERE $whereClause
        ORDER BY sr.timestamp DESC
        LIMIT $limit
    ";
    
    $result = $db->fetchAll($sql);
    
    // Group data berdasarkan sensor type
    $groupedData = [];
    
    foreach ($result as $row) {
        $key = $row['sensor_type'];
        
        if (!isset($groupedData[$key])) {
            $groupedData[$key] = [
                'sensor_type' => $row['sensor_type'],
                'unit' => $row['unit'],
                'warning_threshold' => floatval($row['warning_threshold']),
                'danger_threshold' => floatval($row['danger_threshold']),
                'data' => []
            ];
        }
        
        $groupedData[$key]['data'][] = [
            'node_id' => $row['node_id'],
            'node_name' => $row['node_name'],
            'value' => floatval($row['value']),
            'status' => $row['status'],
            'timestamp' => $row['timestamp']
        ];
    }
    
    // Calculate statistics untuk setiap sensor type
    foreach ($groupedData as &$sensorData) {
        $values = array_column($sensorData['data'], 'value');
        
        $sensorData['statistics'] = [
            'count' => count($values),
            'min' => count($values) > 0 ? min($values) : 0,
            'max' => count($values) > 0 ? max($values) : 0,
            'avg' => count($values) > 0 ? array_sum($values) / count($values) : 0,
            'latest' => count($values) > 0 ? $values[0] : 0
        ];
        
        // Reverse order untuk chart (oldest to newest)
        $sensorData['data'] = array_reverse($sensorData['data']);
    }
    
    $response = [
        'sensor_types' => array_values($groupedData),
        'filters' => [
            'node_id' => $nodeId,
            'sensor_type' => $sensorType,
            'hours' => $hours,
            'limit' => $limit
        ],
        'total_records' => count($result)
    ];
    
    sendSuccess('Data historis berhasil diambil', $response);
    
} catch (Exception $e) {
    sendError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
