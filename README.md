# SICURAH - Sistem Monitoring Tanah Longsor 🌍

**Kelompok 9 - PSTI B - STMIK Widya Pratama Palu**
- Rima Dwi Puspitasari (2315061038)
- Sindy Puji Lestari (2315061042)
- Raissa Syahputra (2315061106)

---

## 🚀 Quick Start (3 Langkah)

### 1. Import Database
```bash
# Buka phpMyAdmin: http://localhost/phpmyadmin
# Klik "Import" → Pilih file: database/MonitoringIOT.sql
# Klik "Go"
```

### 2. Test API
```bash
# Buka browser: http://localhost/MonitoringIOT/test-api.html
# Klik "Run All Tests"
# Pastikan semua ✓ Success (hijau)
```

### 3. Jalankan Website
```bash
# Buka: http://localhost/MonitoringIOT/
# Dashboard akan menampilkan data dari database
```

---

## 📁 File Structure

```
MonitoringIOT/
├── index.html              # Main dashboard
├── script.js               # Frontend logic + API integration
├── style.css               # Styling
├── test-api.html           # API testing tool
├── INSTALLATION_GUIDE.md   # Panduan lengkap
│
├── api/
│   ├── config.php          # Database connection
│   ├── get_data.php        # Get latest sensor data
│   ├── get_history.php     # Get historical data
│   ├── get_nodes.php       # Get node locations
│   ├── get_notifications.php # Get alerts
│   └── post_reading.php    # Receive IoT data (POST)
│
├── database/
│   └── MonitoringIOT.sql    # Complete database schema
│
└── arduino/
    └── sicurah_esp32.ino   # ESP32 IoT device code
```

---

## 🔧 Konfigurasi

### Mode Data (script.js line 2-3)
```javascript
const API_BASE_URL = 'http://localhost/MonitoringIOT/api';
const USE_REAL_DATA = true; // true = data dari database, false = dummy data
```

### WiFi ESP32 (arduino/sicurah_esp32.ino line 30-36)
```cpp
const char* WIFI_SSID = "NamaWiFiAnda";
const char* WIFI_PASSWORD = "PasswordWiFiAnda";
const char* SERVER_URL = "http://192.168.1.100/MonitoringIOT/api/post_reading.php";
const char* NODE_ID = "Node-1"; // Node-1, Node-2, atau Node-3
```

**Cek IP komputer:** Buka CMD → ketik `ipconfig` → lihat IPv4 Address

---

## 📡 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/get_data.php` | GET | Data sensor terbaru |
| `/api/get_history.php?hours=24` | GET | Data historis |
| `/api/get_nodes.php` | GET | Lokasi semua node |
| `/api/get_notifications.php` | GET | Alert/notifikasi |
| `/api/post_reading.php` | POST | Terima data dari IoT |

---

## 🔌 Hardware Setup (ESP32)

### Pin Configuration:
```
ESP32 GPIO 34  →  Rain Gauge Sensor
ESP32 GPIO 35  →  Soil Moisture Sensor
ESP32 GPIO 4   →  DHT22 (Temperature)
ESP32 GPIO 21  →  MPU6050 SDA (Tilt sensor)
ESP32 GPIO 22  →  MPU6050 SCL (Tilt sensor)
```

### Libraries Required (Arduino IDE):
- DHT sensor library (by Adafruit)
- MPU6050 (by Electronic Cats)
- ArduinoJson (by Benoit Blanchon)
- WiFi (built-in ESP32)
- HTTPClient (built-in ESP32)

---

## 🧪 Testing

### 1. Test Database
```sql
-- Di phpMyAdmin, jalankan query:
SELECT * FROM nodes;
SELECT * FROM sensors;
SELECT * FROM sensor_readings ORDER BY timestamp DESC LIMIT 10;
```

### 2. Test API (Browser)
```
http://localhost/MonitoringIOT/api/get_data.php
http://localhost/MonitoringIOT/api/get_nodes.php
```

### 3. Test IoT Device
```
1. Upload code ke ESP32
2. Buka Serial Monitor (115200 baud)
3. Tunggu WiFi connect
4. Lihat data terkirim setiap 1 menit
5. Cek database: sensor_readings bertambah
```

---

## 🐛 Troubleshooting

### ❌ Database Connection Failed
**Solusi:**
- Pastikan MySQL di XAMPP aktif (hijau)
- Cek username/password di `api/config.php`

### ❌ API Returns Empty Data
**Solusi:**
- Import ulang database
- Cek tabel ada data: `SELECT COUNT(*) FROM sensor_readings;`

### ❌ ESP32 Can't Send Data
**Solusi:**
- Cek IP komputer dengan `ipconfig`
- Update `SERVER_URL` di Arduino code
- Pastikan ESP32 dan komputer 1 WiFi
- Test API di browser dari HP (harus bisa akses)

### ❌ CORS Error
**Solusi:**
- Jangan buka file dengan `file://` 
- Harus lewat `http://localhost/`

---

## 📊 Database Schema

### Table: nodes (3 records)
- Node-1, Node-2, Node-3
- Berisi: GPS coordinates, status, last_seen

### Table: sensors (12 records)
- 4 sensor types × 3 nodes = 12 sensors
- Types: rain, soil_moisture, tilt, temperature
- Berisi: thresholds (warning & danger)

### Table: sensor_readings
- Time-series data dari IoT devices
- Format: sensor_id, value, timestamp

### Table: notifications
- Alert otomatis jika sensor > threshold
- Levels: info, warning, danger

---

## 📈 Features

✅ **Real-time Monitoring** - Data update setiap 1 menit  
✅ **Multi-location** - Support 3+ node locations  
✅ **Smart Alerts** - Auto notification jika bahaya  
✅ **Historical Data** - Chart data 24 jam terakhir  
✅ **IoT Integration** - ESP32 kirim data via HTTP POST  
✅ **Responsive UI** - Mobile & desktop friendly  
✅ **API Documentation** - RESTful API dengan JSON  

---

## 📞 Support

Lihat **INSTALLATION_GUIDE.md** untuk panduan lengkap dan troubleshooting detail.

---

## 📝 Version History

**v1.0.0** (2024)
- ✅ Initial release
- ✅ Complete database backend
- ✅ 5 PHP API endpoints
- ✅ ESP32 IoT device integration
- ✅ Real-time dashboard
- ✅ Automatic alerts system

---

**© 2024 SICURAH - Monitoring Tanah Longsor**
