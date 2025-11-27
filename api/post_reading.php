<?php
/**
 * SICURAH - Post Sensor Reading
 * Endpoint untuk menerima data sensor dari perangkat IoT
 * 
 * Method: POST
 * Content-Type: application/json
 * 
 * Body Format:
 * {
 *   "node_id": "Node-1",
 *   "api_key": "SICURAH_2024_SECRET_KEY",
 *   "readings": [
 *     {"sensor_type": "rain", "value": 45.5},
 *     {"sensor_type": "soil_moisture", "value": 72.3},
 *     {"sensor_type": "tilt", "value": 8.2},
 *     {"sensor_type": "temperature", "value": 28.5}
 *   ]
 * }
 */

require_once __DIR__ . '/config.php';

try {
    // Validasi method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Method tidak diizinkan. Gunakan POST.', 405);
    }
    
    // Get POST data
    $rawData = file_get_contents('php://input');
    $postData = json_decode($rawData, true);
    
    // Debug: Log raw input
    $debugInfo = [
        'raw_input' => $rawData,
        'json_error' => json_last_error_msg(),
        'parsed_data' => $postData
    ];
    
    // Validasi data
    if (!isset($postData['node_id']) || !isset($postData['readings'])) {
        sendError('Data tidak lengkap. Required: node_id, readings', 400, $debugInfo);
    }
    
    // Validasi API Key
    if (!isset($postData['api_key']) || $postData['api_key'] !== API_KEY) {
        sendError('API Key tidak valid', 401);
    }
    
    $nodeId = $postData['node_id'];
    $readings = $postData['readings'];
    
    if (!is_array($readings) || count($readings) === 0) {
        sendError('Readings harus berupa array dan tidak boleh kosong', 400);
    }
    
    $db = Database::getInstance();
    
    // Cek apakah node ada
    $nodeSql = "SELECT id FROM nodes WHERE node_id = '" . $db->escape($nodeId) . "' AND status = 'active'";
    $node = $db->fetchOne($nodeSql);
    
    if (!$node) {
        sendError('Node tidak ditemukan atau tidak aktif: ' . $nodeId, 404);
    }
    
    $nodeDbId = $node['id'];
    
    // Begin transaction
    $db->beginTransaction();
    
    $insertedCount = 0;
    $notificationsCreated = [];
    
    try {
        // Log untuk debugging
        $debugLog = [];
        
        foreach ($readings as $reading) {
            if (!isset($reading['sensor_type']) || !isset($reading['value'])) {
                $debugLog[] = "Skipped: missing sensor_type or value";
                continue;
            }
            
            $sensorType = $reading['sensor_type'];
            $value = floatval($reading['value']);
            $debugLog[] = "Processing: $sensorType = $value";
            
            // Get sensor ID dan thresholds
            $sensorSql = "
                SELECT id, warning_threshold, danger_threshold, unit
                FROM sensors 
                WHERE node_id = $nodeDbId 
                AND sensor_type = '" . $db->escape($sensorType) . "' 
                AND status = 'active'
            ";
            
            $sensor = $db->fetchOne($sensorSql);
            
            if (!$sensor) {
                $debugLog[] = "Sensor NOT FOUND: $sensorType for node $nodeId";
                continue; // Skip jika sensor tidak ditemukan
            }
            
            $debugLog[] = "Sensor FOUND: $sensorType (ID: {$sensor['id']})";
            
            $sensorId = $sensor['id'];
            $warningThreshold = floatval($sensor['warning_threshold']);
            $dangerThreshold = floatval($sensor['danger_threshold']);
            
            // Insert sensor reading
            $insertSql = "
                INSERT INTO sensor_readings (sensor_id, value, timestamp)
                VALUES ($sensorId, $value, NOW())
            ";
            
            if ($db->query($insertSql)) {
                $insertedCount++;
                
                // Cek threshold dan buat notifikasi jika perlu
                if ($value >= $dangerThreshold) {
                    $level = 'danger';
                    $message = "BAHAYA! Sensor $sensorType di $nodeId mencapai $value {$sensor['unit']} (Batas bahaya: $dangerThreshold {$sensor['unit']})";
                    
                    $notifInsert = "
                        INSERT INTO notifications (node_id, level, message, is_read)
                        VALUES ($nodeDbId, '$level', '" . $db->escape($message) . "', 0)
                    ";
                    
                    if ($db->query($notifInsert)) {
                        $notificationsCreated[] = [
                            'level' => $level,
                            'sensor_type' => $sensorType,
                            'value' => $value,
                            'threshold' => $dangerThreshold
                        ];
                    }
                } elseif ($value >= $warningThreshold) {
                    $level = 'warning';
                    $message = "PERINGATAN! Sensor $sensorType di $nodeId mencapai $value {$sensor['unit']} (Batas peringatan: $warningThreshold {$sensor['unit']})";
                    
                    $notifInsert = "
                        INSERT INTO notifications (node_id, level, message, is_read)
                        VALUES ($nodeDbId, '$level', '" . $db->escape($message) . "', 0)
                    ";
                    
                    if ($db->query($notifInsert)) {
                        $notificationsCreated[] = [
                            'level' => $level,
                            'sensor_type' => $sensorType,
                            'value' => $value,
                            'threshold' => $warningThreshold
                        ];
                    }
                }
            }
        }
        
        // Update last_seen untuk node
        $updateNodeSql = "UPDATE nodes SET last_seen = NOW() WHERE id = $nodeDbId";
        $db->query($updateNodeSql);
        
        // Log activity
        $logSql = "
            INSERT INTO system_logs (log_type, message)
            VALUES ('sensor_reading', 'Node $nodeId mengirim $insertedCount pembacaan sensor')
        ";
        $db->query($logSql);
        
        // Commit transaction
        $db->commit();
        
        sendSuccess('Data sensor berhasil disimpan', [
            'node_id' => $nodeId,
            'inserted_count' => $insertedCount,
            'total_readings' => count($readings),
            'notifications_created' => count($notificationsCreated),
            'alerts' => $notificationsCreated,
            'timestamp' => date('Y-m-d H:i:s'),
            'debug_log' => $debugLog
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    sendError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
