-- ============================================
-- SICURAH Database - Sistem Monitoring IoT
-- Database Name: MonitoringIOT
-- Kelompok 9 - PSTI B
-- Rima Dwi Puspitasari (2315061038)
-- Sindy Puji Lestari (2315061042)
-- Raissa Syahputra (2315061106)
-- ============================================

-- Drop database jika sudah ada
DROP DATABASE IF EXISTS MonitoringIOT;

-- Buat database baru
CREATE DATABASE MonitoringIOT 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE MonitoringIOT;

-- ============================================
-- Table 1: users (Admin & User Management)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'operator', 'viewer') DEFAULT 'viewer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table 2: nodes (IoT Device Locations)
-- ============================================
CREATE TABLE nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    altitude DECIMAL(8, 2) DEFAULT 0,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    device_type VARCHAR(50) DEFAULT 'ESP32',
    firmware_version VARCHAR(20) DEFAULT '1.0.0',
    last_seen TIMESTAMP NULL,
    installation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_node_id (node_id),
    INDEX idx_status (status),
    INDEX idx_last_seen (last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table 3: sensors (Sensor Configuration)
-- ============================================
CREATE TABLE sensors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id INT NOT NULL,
    sensor_type VARCHAR(50) NOT NULL,
    sensor_model VARCHAR(100),
    unit VARCHAR(20) NOT NULL,
    min_value DECIMAL(10, 2) DEFAULT 0,
    max_value DECIMAL(10, 2) DEFAULT 100,
    warning_threshold DECIMAL(10, 2) NOT NULL,
    danger_threshold DECIMAL(10, 2) NOT NULL,
    calibration_offset DECIMAL(10, 4) DEFAULT 0,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    last_calibration DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (node_id) REFERENCES nodes(id) ON DELETE CASCADE,
    INDEX idx_node_sensor (node_id, sensor_type),
    INDEX idx_sensor_type (sensor_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table 4: sensor_readings (Time Series Data)
-- ============================================
CREATE TABLE sensor_readings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    raw_value DECIMAL(10, 4),
    status ENUM('safe', 'warning', 'danger') DEFAULT 'safe',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(id) ON DELETE CASCADE,
    INDEX idx_sensor_timestamp (sensor_id, timestamp),
    INDEX idx_timestamp (timestamp),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table 5: notifications (Alert System)
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id INT NOT NULL,
    sensor_id INT,
    level ENUM('info', 'warning', 'danger', 'critical') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    read_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (node_id) REFERENCES nodes(id) ON DELETE CASCADE,
    FOREIGN KEY (sensor_id) REFERENCES sensors(id) ON DELETE SET NULL,
    FOREIGN KEY (read_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_node_level (node_id, level),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table 6: system_logs (Activity Logs)
-- ============================================
CREATE TABLE system_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    log_type ENUM('info', 'warning', 'error', 'system', 'sensor_reading', 'user_action') NOT NULL,
    action VARCHAR(100),
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_log_type (log_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert Admin User
INSERT INTO users (username, password_hash, full_name, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator SICURAH', 'admin@sicurah.com', 'admin', 'active'),
('operator1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Lapangan 1', 'operator1@sicurah.com', 'operator', 'active'),
('viewer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Monitoring', 'viewer@sicurah.com', 'viewer', 'active');

-- Insert Node Locations (3 lokasi monitoring)
INSERT INTO nodes (node_id, name, location, latitude, longitude, altitude, status, device_type, firmware_version, last_seen, installation_date) VALUES
('Node-1', 'Stasiun Monitoring A', 'Kelurahan Petobo, Palu Selatan, Kota Palu', -0.924000, 119.870000, 85.5, 'active', 'ESP32', '1.0.0', NOW(), '2024-01-15'),
('Node-2', 'Stasiun Monitoring B', 'Kelurahan Balaroa, Palu Barat, Kota Palu', -0.905500, 119.855000, 120.3, 'active', 'ESP32', '1.0.0', NOW() - INTERVAL 5 MINUTE, '2024-01-20'),
('Node-3', 'Stasiun Monitoring C', 'Kelurahan Jono Oge, Sigi Biromaru, Kab. Sigi', -0.892000, 119.842000, 95.8, 'active', 'ESP32', '1.0.0', NOW() - INTERVAL 2 MINUTE, '2024-02-01');

-- Insert Sensor Configuration (4 jenis sensor × 3 node = 12 sensors)
INSERT INTO sensors (node_id, sensor_type, sensor_model, unit, min_value, max_value, warning_threshold, danger_threshold, status, last_calibration) VALUES
-- Node-1 Sensors
(1, 'rain', 'Tipping Bucket Rain Gauge', 'mm', 0, 200, 40, 60, 'active', '2024-11-01'),
(1, 'soil_moisture', 'Capacitive Soil Moisture v1.2', '%', 0, 100, 70, 85, 'active', '2024-11-01'),
(1, 'tilt', 'MPU6050 Gyroscope', '°', 0, 45, 12, 18, 'active', '2024-11-01'),
(1, 'temperature', 'DHT22 Temperature Sensor', '°C', -10, 50, 32, 38, 'active', '2024-11-01'),

-- Node-2 Sensors
(2, 'rain', 'Tipping Bucket Rain Gauge', 'mm', 0, 200, 40, 60, 'active', '2024-11-01'),
(2, 'soil_moisture', 'Capacitive Soil Moisture v1.2', '%', 0, 100, 70, 85, 'active', '2024-11-01'),
(2, 'tilt', 'MPU6050 Gyroscope', '°', 0, 45, 12, 18, 'active', '2024-11-01'),
(2, 'temperature', 'DHT22 Temperature Sensor', '°C', -10, 50, 32, 38, 'active', '2024-11-01'),

-- Node-3 Sensors
(3, 'rain', 'Tipping Bucket Rain Gauge', 'mm', 0, 200, 40, 60, 'active', '2024-11-01'),
(3, 'soil_moisture', 'Capacitive Soil Moisture v1.2', '%', 0, 100, 70, 85, 'active', '2024-11-01'),
(3, 'tilt', 'MPU6050 Gyroscope', '°', 0, 45, 12, 18, 'active', '2024-11-01'),
(3, 'temperature', 'DHT22 Temperature Sensor', '°C', -10, 50, 32, 38, 'active', '2024-11-01');

-- Insert Historical Sensor Readings (Data 24 jam terakhir - lebih lengkap)
-- Node-1 Data (setiap 1 jam, 24 data points per sensor)
INSERT INTO sensor_readings (sensor_id, value, raw_value, status, timestamp) VALUES
-- Rain sensor (ID 1) - Node-1
(1, 5.2, 5.2234, 'safe', NOW() - INTERVAL 23 HOUR),
(1, 8.5, 8.5123, 'safe', NOW() - INTERVAL 22 HOUR),
(1, 12.3, 12.3456, 'safe', NOW() - INTERVAL 21 HOUR),
(1, 18.7, 18.7234, 'safe', NOW() - INTERVAL 20 HOUR),
(1, 25.4, 25.4123, 'safe', NOW() - INTERVAL 19 HOUR),
(1, 32.8, 32.8345, 'safe', NOW() - INTERVAL 18 HOUR),
(1, 38.2, 38.2456, 'safe', NOW() - INTERVAL 17 HOUR),
(1, 45.6, 45.6234, 'warning', NOW() - INTERVAL 16 HOUR),
(1, 52.3, 52.3123, 'warning', NOW() - INTERVAL 15 HOUR),
(1, 58.9, 58.9456, 'warning', NOW() - INTERVAL 14 HOUR),
(1, 64.2, 64.2234, 'danger', NOW() - INTERVAL 13 HOUR),
(1, 61.5, 61.5123, 'danger', NOW() - INTERVAL 12 HOUR),
(1, 55.8, 55.8345, 'warning', NOW() - INTERVAL 11 HOUR),
(1, 48.3, 48.3456, 'warning', NOW() - INTERVAL 10 HOUR),
(1, 42.1, 42.1234, 'warning', NOW() - INTERVAL 9 HOUR),
(1, 35.7, 35.7123, 'safe', NOW() - INTERVAL 8 HOUR),
(1, 28.4, 28.4456, 'safe', NOW() - INTERVAL 7 HOUR),
(1, 22.6, 22.6234, 'safe', NOW() - INTERVAL 6 HOUR),
(1, 18.2, 18.2123, 'safe', NOW() - INTERVAL 5 HOUR),
(1, 15.3, 15.3345, 'safe', NOW() - INTERVAL 4 HOUR),
(1, 12.8, 12.8456, 'safe', NOW() - INTERVAL 3 HOUR),
(1, 10.5, 10.5234, 'safe', NOW() - INTERVAL 2 HOUR),
(1, 8.2, 8.2123, 'safe', NOW() - INTERVAL 1 HOUR),
(1, 6.5, 6.5456, 'safe', NOW()),

-- Soil Moisture (ID 2) - Node-1
(2, 45.3, 45.3234, 'safe', NOW() - INTERVAL 23 HOUR),
(2, 48.7, 48.7123, 'safe', NOW() - INTERVAL 22 HOUR),
(2, 52.4, 52.4456, 'safe', NOW() - INTERVAL 21 HOUR),
(2, 56.8, 56.8234, 'safe', NOW() - INTERVAL 20 HOUR),
(2, 61.2, 61.2123, 'safe', NOW() - INTERVAL 19 HOUR),
(2, 65.5, 65.5345, 'safe', NOW() - INTERVAL 18 HOUR),
(2, 68.9, 68.9456, 'safe', NOW() - INTERVAL 17 HOUR),
(2, 73.4, 73.4234, 'warning', NOW() - INTERVAL 16 HOUR),
(2, 77.8, 77.8123, 'warning', NOW() - INTERVAL 15 HOUR),
(2, 82.1, 82.1456, 'warning', NOW() - INTERVAL 14 HOUR),
(2, 86.5, 86.5234, 'danger', NOW() - INTERVAL 13 HOUR),
(2, 88.2, 88.2123, 'danger', NOW() - INTERVAL 12 HOUR),
(2, 85.7, 85.7345, 'danger', NOW() - INTERVAL 11 HOUR),
(2, 82.4, 82.4456, 'warning', NOW() - INTERVAL 10 HOUR),
(2, 78.9, 78.9234, 'warning', NOW() - INTERVAL 9 HOUR),
(2, 74.2, 74.2123, 'warning', NOW() - INTERVAL 8 HOUR),
(2, 68.5, 68.5456, 'safe', NOW() - INTERVAL 7 HOUR),
(2, 62.8, 62.8234, 'safe', NOW() - INTERVAL 6 HOUR),
(2, 58.3, 58.3123, 'safe', NOW() - INTERVAL 5 HOUR),
(2, 54.7, 54.7345, 'safe', NOW() - INTERVAL 4 HOUR),
(2, 51.2, 51.2456, 'safe', NOW() - INTERVAL 3 HOUR),
(2, 48.6, 48.6234, 'safe', NOW() - INTERVAL 2 HOUR),
(2, 46.3, 46.3123, 'safe', NOW() - INTERVAL 1 HOUR),
(2, 44.5, 44.5456, 'safe', NOW()),

-- Tilt sensor (ID 3) - Node-1
(3, 4.2, 4.2345, 'safe', NOW() - INTERVAL 23 HOUR),
(3, 5.1, 5.1234, 'safe', NOW() - INTERVAL 22 HOUR),
(3, 6.3, 6.3456, 'safe', NOW() - INTERVAL 21 HOUR),
(3, 7.5, 7.5234, 'safe', NOW() - INTERVAL 20 HOUR),
(3, 8.8, 8.8123, 'safe', NOW() - INTERVAL 19 HOUR),
(3, 10.2, 10.2345, 'safe', NOW() - INTERVAL 18 HOUR),
(3, 11.5, 11.5456, 'safe', NOW() - INTERVAL 17 HOUR),
(3, 13.2, 13.2234, 'warning', NOW() - INTERVAL 16 HOUR),
(3, 14.8, 14.8123, 'warning', NOW() - INTERVAL 15 HOUR),
(3, 16.3, 16.3456, 'warning', NOW() - INTERVAL 14 HOUR),
(3, 18.5, 18.5234, 'danger', NOW() - INTERVAL 13 HOUR),
(3, 19.2, 19.2123, 'danger', NOW() - INTERVAL 12 HOUR),
(3, 17.8, 17.8345, 'warning', NOW() - INTERVAL 11 HOUR),
(3, 15.4, 15.4456, 'warning', NOW() - INTERVAL 10 HOUR),
(3, 13.7, 13.7234, 'warning', NOW() - INTERVAL 9 HOUR),
(3, 11.9, 11.9123, 'safe', NOW() - INTERVAL 8 HOUR),
(3, 10.3, 10.3456, 'safe', NOW() - INTERVAL 7 HOUR),
(3, 8.7, 8.7234, 'safe', NOW() - INTERVAL 6 HOUR),
(3, 7.5, 7.5123, 'safe', NOW() - INTERVAL 5 HOUR),
(3, 6.8, 6.8345, 'safe', NOW() - INTERVAL 4 HOUR),
(3, 6.2, 6.2456, 'safe', NOW() - INTERVAL 3 HOUR),
(3, 5.5, 5.5234, 'safe', NOW() - INTERVAL 2 HOUR),
(3, 5.0, 5.0123, 'safe', NOW() - INTERVAL 1 HOUR),
(3, 4.8, 4.8456, 'safe', NOW()),

-- Temperature (ID 4) - Node-1
(4, 24.5, 24.5234, 'safe', NOW() - INTERVAL 23 HOUR),
(4, 25.2, 25.2123, 'safe', NOW() - INTERVAL 22 HOUR),
(4, 26.3, 26.3456, 'safe', NOW() - INTERVAL 21 HOUR),
(4, 27.5, 27.5234, 'safe', NOW() - INTERVAL 20 HOUR),
(4, 28.8, 28.8123, 'safe', NOW() - INTERVAL 19 HOUR),
(4, 29.7, 29.7345, 'safe', NOW() - INTERVAL 18 HOUR),
(4, 30.5, 30.5456, 'safe', NOW() - INTERVAL 17 HOUR),
(4, 31.2, 31.2234, 'safe', NOW() - INTERVAL 16 HOUR),
(4, 30.8, 30.8123, 'safe', NOW() - INTERVAL 15 HOUR),
(4, 29.9, 29.9456, 'safe', NOW() - INTERVAL 14 HOUR),
(4, 28.6, 28.6234, 'safe', NOW() - INTERVAL 13 HOUR),
(4, 27.4, 27.4123, 'safe', NOW() - INTERVAL 12 HOUR),
(4, 26.8, 26.8345, 'safe', NOW() - INTERVAL 11 HOUR),
(4, 26.2, 26.2456, 'safe', NOW() - INTERVAL 10 HOUR),
(4, 25.9, 25.9234, 'safe', NOW() - INTERVAL 9 HOUR),
(4, 25.5, 25.5123, 'safe', NOW() - INTERVAL 8 HOUR),
(4, 25.2, 25.2456, 'safe', NOW() - INTERVAL 7 HOUR),
(4, 24.8, 24.8234, 'safe', NOW() - INTERVAL 6 HOUR),
(4, 24.5, 24.5123, 'safe', NOW() - INTERVAL 5 HOUR),
(4, 24.3, 24.3345, 'safe', NOW() - INTERVAL 4 HOUR),
(4, 24.1, 24.1456, 'safe', NOW() - INTERVAL 3 HOUR),
(4, 24.0, 24.0234, 'safe', NOW() - INTERVAL 2 HOUR),
(4, 23.8, 23.8123, 'safe', NOW() - INTERVAL 1 HOUR),
(4, 23.5, 23.5456, 'safe', NOW());

-- Node-2 Current Data (sensor ID 5-8)
INSERT INTO sensor_readings (sensor_id, value, raw_value, status, timestamp) VALUES
(5, 55.8, 55.8234, 'warning', NOW()),
(6, 78.4, 78.4123, 'warning', NOW()),
(7, 14.2, 14.2456, 'warning', NOW()),
(8, 27.3, 27.3234, 'safe', NOW());

-- Node-3 Current Data (sensor ID 9-12)
INSERT INTO sensor_readings (sensor_id, value, raw_value, status, timestamp) VALUES
(9, 32.5, 32.5234, 'safe', NOW()),
(10, 62.8, 62.8123, 'safe', NOW()),
(11, 8.7, 8.7456, 'safe', NOW()),
(12, 25.9, 25.9234, 'safe', NOW());

-- Insert Notifications (Alert History)
INSERT INTO notifications (node_id, sensor_id, level, title, message, is_read, created_at) VALUES
(1, 1, 'danger', 'BAHAYA! Curah Hujan Sangat Tinggi', 'Curah hujan di Stasiun A mencapai 64.2 mm. Segera lakukan evakuasi!', FALSE, NOW() - INTERVAL 13 HOUR),
(1, 2, 'danger', 'BAHAYA! Kelembaban Tanah Kritis', 'Kelembaban tanah di Stasiun A mencapai 88.2%. Potensi longsor sangat tinggi!', FALSE, NOW() - INTERVAL 12 HOUR),
(1, 3, 'danger', 'BAHAYA! Pergeseran Tanah Terdeteksi', 'Kemiringan tanah di Stasiun A mencapai 19.2°. Pergeseran tanah terdeteksi!', FALSE, NOW() - INTERVAL 12 HOUR),
(2, 5, 'warning', 'PERINGATAN: Curah Hujan Meningkat', 'Curah hujan di Stasiun B mencapai 55.8 mm. Waspadai kondisi tanah.', FALSE, NOW() - INTERVAL 1 HOUR),
(2, 6, 'warning', 'PERINGATAN: Kelembaban Tanah Tinggi', 'Kelembaban tanah di Stasiun B mencapai 78.4%. Pantau terus perkembangan.', FALSE, NOW() - INTERVAL 30 MINUTE),
(2, 7, 'warning', 'PERINGATAN: Kemiringan Tanah Meningkat', 'Kemiringan tanah di Stasiun B mencapai 14.2°. Waspada pergeseran tanah.', FALSE, NOW() - INTERVAL 15 MINUTE),
(3, NULL, 'info', 'Node C Online', 'Stasiun Monitoring C berhasil terhubung ke sistem.', TRUE, NOW() - INTERVAL 2 MINUTE),
(1, NULL, 'info', 'Kalibrasi Sensor Selesai', 'Semua sensor di Stasiun A telah dikalibrasi.', TRUE, NOW() - INTERVAL 1 DAY),
(2, NULL, 'info', 'Pemeliharaan Rutin', 'Pemeliharaan rutin sensor di Stasiun B telah selesai.', TRUE, NOW() - INTERVAL 2 DAY);

-- Insert System Logs
INSERT INTO system_logs (user_id, log_type, action, message, ip_address, created_at) VALUES
(1, 'system', 'system_start', 'Sistem SICURAH berhasil dijalankan', '127.0.0.1', NOW() - INTERVAL 1 DAY),
(1, 'user_action', 'login', 'Admin login ke sistem', '192.168.1.100', NOW() - INTERVAL 23 HOUR),
(NULL, 'sensor_reading', 'data_received', 'Node-1 mengirim 4 pembacaan sensor', '192.168.1.101', NOW() - INTERVAL 1 HOUR),
(NULL, 'sensor_reading', 'data_received', 'Node-2 mengirim 4 pembacaan sensor', '192.168.1.102', NOW() - INTERVAL 30 MINUTE),
(NULL, 'sensor_reading', 'data_received', 'Node-3 mengirim 4 pembacaan sensor', '192.168.1.103', NOW() - INTERVAL 2 MINUTE),
(1, 'warning', 'threshold_exceeded', 'Sensor curah hujan Node-1 melebihi batas bahaya', '127.0.0.1', NOW() - INTERVAL 13 HOUR),
(1, 'warning', 'threshold_exceeded', 'Sensor kelembaban Node-1 melebihi batas bahaya', '127.0.0.1', NOW() - INTERVAL 12 HOUR),
(2, 'user_action', 'view_data', 'Operator melihat dashboard monitoring', '192.168.1.105', NOW() - INTERVAL 5 HOUR),
(3, 'user_action', 'view_report', 'Viewer mengakses laporan historis', '192.168.1.110', NOW() - INTERVAL 3 HOUR),
(1, 'info', 'sensor_calibration', 'Kalibrasi sensor Node-1 selesai', '127.0.0.1', NOW() - INTERVAL 1 DAY);

-- ============================================
-- CREATE VIEWS (Optional - untuk query lebih mudah)
-- ============================================

-- View: Latest Sensor Readings per Node
CREATE OR REPLACE VIEW v_latest_readings AS
SELECT 
    n.node_id,
    n.name AS node_name,
    n.location,
    n.status AS node_status,
    s.sensor_type,
    sr.value,
    s.unit,
    sr.status,
    sr.timestamp
FROM nodes n
JOIN sensors s ON n.id = s.node_id
JOIN sensor_readings sr ON s.id = sr.sensor_id
WHERE sr.id IN (
    SELECT MAX(id) FROM sensor_readings GROUP BY sensor_id
)
ORDER BY n.node_id, s.sensor_type;

-- View: Active Alerts
CREATE OR REPLACE VIEW v_active_alerts AS
SELECT 
    n.node_id,
    n.name AS node_name,
    n.location,
    no.level,
    no.title,
    no.message,
    no.created_at,
    TIMESTAMPDIFF(MINUTE, no.created_at, NOW()) AS minutes_ago
FROM notifications no
JOIN nodes n ON no.node_id = n.id
WHERE no.is_read = FALSE
ORDER BY no.created_at DESC;

-- ============================================
-- STORED PROCEDURES (Optional - untuk operasi kompleks)
-- ============================================

DELIMITER //

-- Procedure: Get Node Summary
CREATE PROCEDURE sp_get_node_summary(IN p_node_id VARCHAR(50))
BEGIN
    SELECT 
        n.node_id,
        n.name,
        n.location,
        n.status,
        n.last_seen,
        COUNT(DISTINCT s.id) AS total_sensors,
        COUNT(DISTINCT sr.id) AS total_readings,
        SUM(CASE WHEN sr.status = 'danger' THEN 1 ELSE 0 END) AS danger_count,
        SUM(CASE WHEN sr.status = 'warning' THEN 1 ELSE 0 END) AS warning_count
    FROM nodes n
    LEFT JOIN sensors s ON n.id = s.node_id
    LEFT JOIN sensor_readings sr ON s.id = sr.sensor_id 
        AND sr.timestamp >= NOW() - INTERVAL 1 HOUR
    WHERE n.node_id = p_node_id
    GROUP BY n.id;
END //

DELIMITER ;

-- ============================================
-- INDEXES (untuk performance)
-- ============================================

-- Tambahan indexes untuk query optimization
CREATE INDEX idx_readings_sensor_time ON sensor_readings(sensor_id, timestamp DESC);
CREATE INDEX idx_notifications_unread ON notifications(is_read, created_at DESC);
CREATE INDEX idx_logs_type_time ON system_logs(log_type, created_at DESC);

-- ============================================
-- DATABASE INFO
-- ============================================

SELECT 'Database MonitoringIOT berhasil dibuat!' AS status,
       (SELECT COUNT(*) FROM users) AS total_users,
       (SELECT COUNT(*) FROM nodes) AS total_nodes,
       (SELECT COUNT(*) FROM sensors) AS total_sensors,
       (SELECT COUNT(*) FROM sensor_readings) AS total_readings,
       (SELECT COUNT(*) FROM notifications) AS total_notifications,
       (SELECT COUNT(*) FROM system_logs) AS total_logs;

-- ============================================
-- SELESAI! Database siap digunakan
-- ============================================
