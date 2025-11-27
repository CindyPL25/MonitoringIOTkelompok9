# SICURAH - Sistem Monitoring Tanah Longsor
## Panduan Instalasi dan Penggunaan Lengkap

**Kelompok 9 - PSTI B**
- Rima Dwi Puspitasari (2315061038)
- Sindy Puji Lestari (2315061042)
- Raissa Syahputra (2315061106)

---

## 📋 Daftar Isi
1. [Instalasi Database](#1-instalasi-database)
2. [Konfigurasi PHP API](#2-konfigurasi-php-api)
3. [Setup Perangkat IoT](#3-setup-perangkat-iot)
4. [Update Frontend](#4-update-frontend)
5. [Testing](#5-testing)
6. [Troubleshooting](#6-troubleshooting)

---

## 1. Instalasi Database

### Langkah 1: Import Database

1. **Buka phpMyAdmin**
   - Pastikan XAMPP sudah berjalan (Apache & MySQL aktif)
   - Buka browser: `http://localhost/phpmyadmin`

2. **Import file SQL**
   - Klik tab "Import"
   - Klik "Choose File" dan pilih: `database/MonitoringIOT.sql`
   - Klik "Go" untuk import
   - Tunggu hingga muncul pesan "Import has been successfully finished"

3. **Verifikasi Database**
   - Database `MonitoringIOT` akan muncul di sidebar kiri
   - Klik database tersebut untuk melihat 6 tabel:
     - `users` (1 admin user)
     - `nodes` (3 lokasi node)
     - `sensors` (12 sensor: 4 jenis × 3 node)
     - `sensor_readings` (data historis)
     - `notifications` (alert sistem)
     - `system_logs` (log aktivitas)

### Alternatif: Import via Command Line

```bash
# Buka Command Prompt di folder xampp/mysql/bin
cd C:\xampp\mysql\bin

# Jalankan perintah import
mysql -u root -p < "C:\xampp\htdocs\MonitoringIOT\database\MonitoringIOT.sql"

# Tekan Enter (password kosong di XAMPP default)
```

---

## 2. Konfigurasi PHP API

### File yang Sudah Dibuat:

```
api/
├── config.php              # Konfigurasi database & helper functions
├── get_data.php            # Endpoint: Ambil data sensor terbaru
├── get_history.php         # Endpoint: Ambil data historis
├── get_nodes.php           # Endpoint: Ambil semua lokasi node
├── get_notifications.php   # Endpoint: Ambil notifikasi/alert
└── post_reading.php        # Endpoint: Terima data dari IoT device
```

### Test API Endpoints

1. **Test Koneksi Database**
   - Buka: `http://localhost/MonitoringIOT/api/config.php`
   - Seharusnya tidak ada error

2. **Test Get Data**
   ```
   http://localhost/MonitoringIOT/api/get_data.php
   ```
   Response:
   ```json
   {
     "success": true,
     "message": "Data berhasil diambil",
     "data": {
       "summary": {...},
       "nodes": [...]
     }
   }
   ```

3. **Test Get History**
   ```
   http://localhost/MonitoringIOT/api/get_history.php?hours=24
   ```

4. **Test Get Nodes**
   ```
   http://localhost/MonitoringIOT/api/get_nodes.php
   ```

5. **Test Get Notifications**
   ```
   http://localhost/MonitoringIOT/api/get_notifications.php?limit=10
   ```

---

## 3. Setup Perangkat IoT

### Hardware Requirements:
- **ESP32 Development Board** (Wemos/NodeMCU ESP32)
- **Rain Gauge Sensor** (Tipping Bucket)
- **Soil Moisture Sensor** (Capacitive type)
- **MPU6050** (Gyroscope/Accelerometer untuk tilt)
- **DHT22** (Temperature & Humidity sensor)
- Kabel jumper, breadboard, power supply

### Wiring Diagram:

```
ESP32          Sensor
===========================================
GPIO 34  <-->  Rain Gauge (Analog OUT)
GPIO 35  <-->  Soil Moisture (Analog OUT)
GPIO 4   <-->  DHT22 (Data Pin)
GPIO 21  <-->  MPU6050 (SDA)
GPIO 22  <-->  MPU6050 (SCL)
3.3V     <-->  VCC (semua sensor)
GND      <-->  GND (semua sensor)
```

### Software Setup:

1. **Install Arduino IDE**
   - Download: https://www.arduino.cc/en/software
   - Install ESP32 board support:
     - File → Preferences → Additional Board Manager URLs:
     ```
     https://dl.espressif.com/dl/package_esp32_index.json
     ```
     - Tools → Board → Boards Manager → Search "esp32" → Install

2. **Install Required Libraries**
   - Tools → Manage Libraries, install:
     - `DHT sensor library` by Adafruit
     - `MPU6050` by Electronic Cats
     - `ArduinoJson` by Benoit Blanchon

3. **Upload Code**
   - Buka file: `arduino/sicurah_esp32.ino`
   - **PENTING: Edit konfigurasi berikut:**
   
   ```cpp
   // Line 30-31: Ganti dengan WiFi Anda
   const char* WIFI_SSID = "NamaWiFiAnda";
   const char* WIFI_PASSWORD = "PasswordWiFiAnda";
   
   // Line 34: Ganti dengan IP komputer XAMPP
   const char* SERVER_URL = "http://192.168.1.100/MonitoringIOT/api/post_reading.php";
   
   // Line 36: Pilih Node ID (Node-1, Node-2, atau Node-3)
   const char* NODE_ID = "Node-1";
   ```
   
   - **Cara cek IP komputer:**
     - Windows: Buka CMD → ketik `ipconfig` → lihat "IPv4 Address"
     - Contoh: `192.168.1.100`
   
   - Upload ke ESP32:
     - Tools → Board → ESP32 Dev Module
     - Tools → Port → Pilih COM port ESP32
     - Klik tombol Upload (→)

4. **Monitor Serial**
   - Tools → Serial Monitor (atau Ctrl+Shift+M)
   - Set baud rate: 115200
   - Lihat output koneksi WiFi dan pengiriman data

---

## 4. Update Frontend

### Ubah script.js untuk Koneksi ke API

Buka file `script.js` dan tambahkan kode berikut:

```javascript
// ========== API CONFIGURATION ==========
const API_BASE_URL = 'http://localhost/MonitoringIOT/api';

// ========== FETCH DATA FROM API ==========
async function fetchSensorData() {
    try {
        const response = await fetch(`${API_BASE_URL}/get_data.php`);
        const result = await response.json();
        
        if (result.success) {
            updateDashboardWithRealData(result.data);
        }
    } catch (error) {
        console.error('Error fetching sensor data:', error);
    }
}

async function fetchHistoryData(sensorType = null, hours = 24) {
    try {
        const url = `${API_BASE_URL}/get_history.php?hours=${hours}${sensorType ? '&sensor_type=' + sensorType : ''}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            updateHistoryChartsWithRealData(result.data);
        }
    } catch (error) {
        console.error('Error fetching history:', error);
    }
}

async function fetchNodesData() {
    try {
        const response = await fetch(`${API_BASE_URL}/get_nodes.php`);
        const result = await response.json();
        
        if (result.success) {
            renderRealLocations(result.data.nodes);
        }
    } catch (error) {
        console.error('Error fetching nodes:', error);
    }
}

async function fetchNotifications() {
    try {
        const response = await fetch(`${API_BASE_URL}/get_notifications.php?limit=20`);
        const result = await response.json();
        
        if (result.success) {
            renderRealNotifications(result.data.notifications);
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

// ========== UPDATE FUNCTIONS ==========
function updateDashboardWithRealData(data) {
    const { summary, nodes } = data;
    
    // Update summary cards
    document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = summary.total_nodes;
    document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = summary.online_nodes;
    document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = summary.danger_sensors;
    
    // Update charts dengan data real
    if (nodes.length > 0) {
        updateChartsWithNodeData(nodes);
    }
}

// ========== AUTO UPDATE ==========
// Ganti updateData() yang lama dengan fetch data real
setInterval(() => {
    fetchSensorData();
    fetchNotifications();
}, 60000); // Update setiap 1 menit

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    fetchSensorData();
    fetchHistoryData();
    fetchNodesData();
    fetchNotifications();
});
```

**Instruksi lengkap untuk integrasi frontend akan diberikan di langkah berikutnya**

---

## 5. Testing

### Test Flow Lengkap:

1. **Test Database**
   - ✅ Pastikan semua tabel terisi dengan sample data
   - ✅ Query manual di phpMyAdmin: `SELECT * FROM nodes;`

2. **Test API Endpoints**
   - ✅ Buka semua endpoint di browser
   - ✅ Pastikan response JSON dengan `"success": true`

3. **Test IoT Device**
   - ✅ Upload code ke ESP32
   - ✅ Buka Serial Monitor
   - ✅ Pastikan WiFi terhubung
   - ✅ Lihat data terkirim setiap 1 menit
   - ✅ Cek database: `SELECT * FROM sensor_readings ORDER BY timestamp DESC LIMIT 10;`

4. **Test Frontend**
   - ✅ Buka `http://localhost/MonitoringIOT/`
   - ✅ Dashboard menampilkan data real dari database
   - ✅ History view menampilkan grafik data
   - ✅ Map view menampilkan lokasi node
   - ✅ Notifications menampilkan alert

---

## 6. Troubleshooting

### Problem 1: Database Connection Failed
**Error:** "Database connection failed"

**Solusi:**
- Pastikan MySQL di XAMPP aktif (indikator hijau)
- Cek username/password di `api/config.php`:
  ```php
  define('DB_USER', 'root');
  define('DB_PASS', '');  // Kosong untuk XAMPP default
  ```

### Problem 2: API Returns Empty Data
**Error:** `"nodes": []`

**Solusi:**
- Import ulang database
- Cek apakah tabel terisi: `SELECT COUNT(*) FROM nodes;`

### Problem 3: ESP32 WiFi Connection Failed
**Error:** "WiFi Connection Failed!"

**Solusi:**
- Cek SSID dan password di code Arduino
- Pastikan WiFi 2.4GHz (ESP32 tidak support 5GHz)
- Cek jarak router dengan ESP32

### Problem 4: ESP32 Can't Send Data to Server
**Error:** "Error on sending POST: -1"

**Solusi:**
- Cek IP komputer dengan `ipconfig`
- Update `SERVER_URL` di Arduino code
- Pastikan firewall tidak memblokir port 80
- Test API di browser dari device lain di jaringan yang sama

### Problem 5: CORS Error di Frontend
**Error:** "Access to fetch has been blocked by CORS policy"

**Solusi:**
- File `api/config.php` sudah include CORS headers
- Pastikan akses via `http://localhost` bukan `file://`

---

## 📊 Database Schema Reference

### Table: nodes
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| node_id | VARCHAR(50) | Unique identifier (Node-1, Node-2, Node-3) |
| name | VARCHAR(100) | Node name |
| location | VARCHAR(255) | Location description |
| latitude | DECIMAL(10,8) | GPS latitude |
| longitude | DECIMAL(11,8) | GPS longitude |
| status | ENUM | active/inactive |
| last_seen | TIMESTAMP | Last data received |

### Table: sensors
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| node_id | INT | Foreign key to nodes |
| sensor_type | VARCHAR(50) | rain/soil_moisture/tilt/temperature |
| unit | VARCHAR(20) | mm/% /°/°C |
| warning_threshold | DECIMAL | Warning level |
| danger_threshold | DECIMAL | Danger level |

### Table: sensor_readings
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| sensor_id | INT | Foreign key to sensors |
| value | DECIMAL(10,2) | Sensor value |
| timestamp | TIMESTAMP | Reading time |

---

## 🔗 API Endpoints Summary

### GET /api/get_data.php
**Response:** Latest sensor readings from all nodes
```json
{
  "success": true,
  "data": {
    "summary": {...},
    "nodes": [...]
  }
}
```

### GET /api/get_history.php?hours=24&sensor_type=rain
**Parameters:**
- `hours` (optional): Number of hours (default: 24)
- `sensor_type` (optional): Filter by type
- `node_id` (optional): Filter by node

### GET /api/get_nodes.php
**Response:** All node locations with sensor status

### GET /api/get_notifications.php?limit=20&level=danger
**Parameters:**
- `limit` (optional): Max results (default: 20)
- `level` (optional): info/warning/danger
- `unread_only` (optional): true/false

### POST /api/post_reading.php
**Body:**
```json
{
  "node_id": "Node-1",
  "api_key": "SICURAH_2024_SECRET_KEY",
  "readings": [
    {"sensor_type": "rain", "value": 45.5},
    {"sensor_type": "soil_moisture", "value": 72.3},
    {"sensor_type": "tilt", "value": 8.2},
    {"sensor_type": "temperature", "value": 28.5}
  ]
}
```

---

## 📞 Support

Jika ada pertanyaan atau masalah, hubungi tim developer:
- Rima: [email/kontak]
- Sindy: [email/kontak]
- Raissa: [email/kontak]

---

## 📝 Changelog

**Version 1.0.0** (2024)
- ✅ Initial release
- ✅ Database schema dengan 6 tabel
- ✅ 5 PHP API endpoints
- ✅ ESP32 IoT device code
- ✅ Frontend dashboard dengan Chart.js
- ✅ Real-time monitoring system

---

**© 2024 SICURAH - Kelompok 9 PSTI B - STMIK Widya Pratama Palu**
