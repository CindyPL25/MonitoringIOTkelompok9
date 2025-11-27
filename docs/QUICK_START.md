# 🚀 QUICK START - SICURAH MonitoringIOT

## Panduan Cepat 3 Langkah!

### ✅ Langkah 1: Import Database (2 menit)

**Via phpMyAdmin:**
```
1. Buka: http://localhost/phpmyadmin
2. Klik tab "Import"
3. Pilih file: database/MonitoringIOT.sql
4. Klik "Go" dan tunggu selesai
✓ Database "MonitoringIOT" siap digunakan!
```

**Via Command Line (alternatif):**
```bash
cd C:\xampp\mysql\bin
mysql -u root -p < "C:\xampp\htdocs\MonitoringIOT\database\MonitoringIOT.sql"
# Tekan Enter (password kosong)
```

---

### ✅ Langkah 2: Test API (1 menit)

```
1. Buka: http://localhost/MonitoringIOT/test-api.html
2. Klik tombol "Run All Tests"
3. Semua test harus ✓ Success (hijau)
```

**Jika ada yang error:**
- Pastikan Apache & MySQL di XAMPP aktif (hijau)
- Cek database sudah diimport dengan benar

---

### ✅ Langkah 3: Buka Website (langsung jalan!)

```
Buka: http://localhost/MonitoringIOT/
✓ Dashboard akan menampilkan data REAL dari database!
```

**Yang akan kamu lihat:**
- 📊 Dashboard dengan data 3 node lokasi
- 📈 Grafik real-time sensor
- 🗺️ Map dengan lokasi monitoring
- 🔔 Notifikasi alert
- 📜 History data 24 jam

---

## 🔌 Setup Alat IoT (ESP32)

### Edit File Arduino:
Buka: `arduino/sicurah_esp32.ino`

**Ubah 3 hal ini:**

```cpp
// 1. WiFi kamu
const char* WIFI_SSID = "NamaWiFiKamu";
const char* WIFI_PASSWORD = "PasswordWiFiKamu";

// 2. IP Komputer (cek dengan ipconfig di CMD)
const char* SERVER_URL = "http://192.168.1.XXX/MonitoringIOT/api/post_reading.php";

// 3. Pilih Node ID
const char* NODE_ID = "Node-1";  // Node-1, Node-2, atau Node-3
```

**Cara cek IP komputer:**
```bash
# Buka CMD, ketik:
ipconfig

# Lihat bagian "IPv4 Address"
# Contoh: 192.168.1.100
```

### Upload ke ESP32:
```
1. Install Arduino IDE
2. Install library: DHT, MPU6050, ArduinoJson
3. Pilih Board: ESP32 Dev Module
4. Upload code
5. Buka Serial Monitor (115200 baud)
6. Data akan terkirim setiap 1 menit!
```

---

## 📊 Isi Database (Sudah Lengkap!)

**Database: MonitoringIOT**

✅ **3 User Accounts:**
- admin / admin123 (Administrator)
- operator1 / admin123 (Operator)
- viewer1 / admin123 (Viewer)

✅ **3 Node Locations:**
- Node-1: Petobo, Palu Selatan (Lat: -0.924, Lng: 119.870)
- Node-2: Balaroa, Palu Barat (Lat: -0.9055, Lng: 119.855)
- Node-3: Jono Oge, Sigi (Lat: -0.892, Lng: 119.842)

✅ **12 Sensors:** (4 jenis × 3 node)
- Rain Gauge (Curah Hujan)
- Soil Moisture (Kelembaban Tanah)
- Tilt Sensor (Kemiringan)
- Temperature (Suhu)

✅ **96+ Sensor Readings:**
- Data historis 24 jam terakhir (Node-1)
- Data real-time (Node-2 & Node-3)
- Status: Safe, Warning, Danger

✅ **9 Notifications:**
- 3 Danger alerts
- 3 Warning alerts  
- 3 Info messages

✅ **10 System Logs:**
- Login history
- Sensor data received
- System activities

---

## 🧪 Test Koneksi Database

**Di phpMyAdmin, coba query ini:**

```sql
-- Cek semua node
SELECT * FROM nodes;

-- Cek data sensor terbaru
SELECT * FROM sensor_readings 
ORDER BY timestamp DESC 
LIMIT 10;

-- Cek notifikasi
SELECT * FROM notifications 
ORDER BY created_at DESC;
```

---

## 📡 Test API di Browser

Buka URL ini satu-satu:

```
✓ http://localhost/MonitoringIOT/api/get_data.php
✓ http://localhost/MonitoringIOT/api/get_nodes.php
✓ http://localhost/MonitoringIOT/api/get_notifications.php
✓ http://localhost/MonitoringIOT/api/get_history.php?hours=24
```

Semua harus return JSON dengan `"success": true`

---

## 🐛 Troubleshooting Cepat

### ❌ Error: Database connection failed
```
Solusi:
1. Pastikan MySQL di XAMPP aktif (hijau)
2. Import ulang database
3. Cek api/config.php (DB_NAME harus 'MonitoringIOT')
```

### ❌ API returns empty data
```
Solusi:
1. Import ulang database/MonitoringIOT.sql
2. Cek di phpMyAdmin: database MonitoringIOT ada isinya
```

### ❌ ESP32 tidak bisa connect ke server
```
Solusi:
1. Cek WiFi SSID dan password benar
2. Cek IP komputer dengan ipconfig
3. Update SERVER_URL di Arduino code
4. HP dan komputer harus 1 WiFi
```

---

## 📞 Need Help?

Lihat file lengkap:
- **INSTALLATION_GUIDE.md** - Panduan detail
- **README.md** - Informasi lengkap

---

## ✨ Selesai!

Website sudah connect ke database dan siap terima data dari alat IoT! 🎉

**Test Flow:**
1. ✅ Database import → Success
2. ✅ API test → All green
3. ✅ Website buka → Data tampil
4. ✅ ESP32 upload → Data masuk database
5. ✅ Website refresh → Data update otomatis

**MANTAP! Semua sudah siap! 🚀**

---

**© 2024 SICURAH - Kelompok 9 PSTI B**
