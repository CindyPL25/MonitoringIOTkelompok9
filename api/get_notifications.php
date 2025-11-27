<?php
/**
 * SICURAH - Get Notifications
 * Endpoint untuk mengambil notifikasi/alert
 */

require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance();
    
    // Parameter dari GET request
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $level = isset($_GET['level']) ? $_GET['level'] : null; // info, warning, danger
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
    
    // Build WHERE clause
    $whereConditions = [];
    
    if ($level) {
        $level = $db->escape($level);
        $whereConditions[] = "n.level = '$level'";
    }
    
    if ($unreadOnly) {
        $whereConditions[] = "n.is_read = 0";
    }
    
    $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Query untuk mengambil notifikasi
    $sql = "
        SELECT 
            n.id,
            n.node_id,
            nd.node_id as node_code,
            nd.name as node_name,
            nd.location,
            n.level,
            n.message,
            n.is_read,
            n.created_at
        FROM notifications n
        LEFT JOIN nodes nd ON n.node_id = nd.id
        $whereClause
        ORDER BY n.created_at DESC
        LIMIT $limit
    ";
    
    $notifications = $db->fetchAll($sql);
    
    // Format notifikasi
    $formattedNotifications = [];
    
    foreach ($notifications as $notif) {
        $formattedNotifications[] = [
            'id' => $notif['id'],
            'node_code' => $notif['node_code'],
            'node_name' => $notif['node_name'],
            'location' => $notif['location'],
            'level' => $notif['level'],
            'message' => $notif['message'],
            'is_read' => $notif['is_read'] == 1,
            'created_at' => $notif['created_at'],
            'time_ago' => timeAgo($notif['created_at'])
        ];
    }
    
    // Get summary
    $summarySql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN level = 'danger' THEN 1 ELSE 0 END) as danger,
            SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warning,
            SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info
        FROM notifications
    ";
    
    $summary = $db->fetchOne($summarySql);
    
    sendSuccess('Notifikasi berhasil diambil', [
        'summary' => [
            'total' => intval($summary['total']),
            'unread' => intval($summary['unread']),
            'danger' => intval($summary['danger']),
            'warning' => intval($summary['warning']),
            'info' => intval($summary['info'])
        ],
        'notifications' => $formattedNotifications,
        'filters' => [
            'limit' => $limit,
            'level' => $level,
            'unread_only' => $unreadOnly
        ]
    ]);
    
} catch (Exception $e) {
    sendError('Terjadi kesalahan: ' . $e->getMessage(), 500);
}
?>
