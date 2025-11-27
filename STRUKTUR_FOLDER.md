# 📁 STRUKTUR FOLDER MONITORINGIOT (UPDATED)

## ✅ Folder Sudah Dirapikan dan Dikelompokkan!

```
MonitoringIOT/
│
├── 📂 api/                              # 🔧 Backend API (PHP)
│   ├── config.php                      # ⚙️ Database config & helper functions
│   ├── post_reading.php                # 📥 Endpoint menerima data dari ESP32 ✅
│   ├── get_data.php                    # 📊 Get data sensor terbaru
│   ├── get_history.php                 # 📜 Get historical data
│   ├── get_nodes.php                   # 🗺️ Get info nodes/lokasi
│   └── get_notifications.php           # 🔔 Get notifikasi & alerts
│
├── 📂 arduino/                          # 🤖 Kode ESP32
│   └── sicurah_esp32_v3_FIXED.ino      # ✅ FILE FINAL - GUNAKAN INI!
│       # File lama (sicurah_esp32.ino) sudah dihapus
│
├── 📂 assets/                           # 🖼️ Media files
│   └── foto/                           # Foto project
│
├── 📂 database/                         # 💾 Database SQL
│   └── MonitoringIOT.sql               # ✅ Database lengkap + sample data
│
├── 📂 docs/                             # 📚 Dokumentasi
│   ├── CHECKLIST.md                    # ✅ Feature checklist
│   ├── IMPORT_DATABASE.md              # 📥 Cara import database
│   ├── INSTALLATION_GUIDE.md           # 📖 Panduan instalasi lengkap
│   ├── QUICK_START.md                  # ⚡ Quick start 3 langkah
│   └── SUMMARY.md                      # 📋 Summary project
│
├── 📂 web/                              # 🌐 Frontend Website
│   ├── about.html                      # ℹ️ About page
│   ├── dashboard.html                  # 🎛️ Dashboard utama (MAIN PAGE) ⭐
│   ├── history.html                    # 📊 History data sensor
│   ├── index.html                      # 🏠 Landing page
│   ├── script.js                       # 💻 JavaScript utama (API calls)
│   ├── style.css                       # 🎨 CSS styling
│   ├── test-api.html                   # 🧪 Test API endpoints
│   └── test-modal.html                 # 🧪 Test modal UI
│
├── test_database.php                    # ✅ Test koneksi database
├── test_esp32_data.php                  # ✅ Simulasi data ESP32
└── README.md                            # 📖 README utama

```

---

## 🔄 Perubahan yang Dilakukan

### ✅ File Dipindahkan
- **HTML, JS, CSS** → Pindah ke folder `web/`
- **Dokumentasi MD** → Pindah ke folder `docs/`
- **Arduino Code** → Tetap di `arduino/`, file lama dihapus

### ✅ File Dihapus
- ❌ `sicurah_esp32.ino` (versi lama - format JSON salah)
- File duplikat sudah dibersihkan sebelumnya:
  - ❌ `config.php` (root) 
  - ❌ `insert_data.php` (root)
  - ❌ `sicurah_database.sql` (database lama)

### ✅ Path Updated
- `web/script.js` → API path diubah dari `http://localhost/MonitoringIOT/api` ke `../api` (path relatif)

---

## 🔗 Alur Data (SUDAH TERINTEGRASI!)

```
┌─────────────┐
│   ESP32     │  📡 Kirim data setiap 30 detik
│  (Arduino)  │  POST → http://IP/MonitoringIOT/api/post_reading.php
└──────┬──────┘
       │
       ▼
┌─────────────────────────────┐
│  api/post_reading.php       │  🔧 Validasi & simpan ke database
│  - Validate API Key         │
│  - Parse JSON               │
│  - Insert to sensor_readings│
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│  Database: MonitoringIOT    │  💾 MySQL Database
│  Table: sensor_readings     │  ✅ Data tersimpan real-time
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│  api/get_data.php           │  📊 Website ambil data
│  api/get_history.php        │
│  api/get_nodes.php          │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│  web/dashboard.html         │  🌐 Dashboard tampilkan data
│  web/script.js              │  Auto-refresh 60 detik
│  + Chart.js visualization   │
└─────────────────────────────┘
```

---

## 📊 Status Setiap Komponen

### 🟢 ESP32 (Hardware)
- ✅ Kode: `arduino/sicurah_esp32_v3_FIXED.ino`
- ✅ Format JSON: `{"node_id": "Node-1", "api_key": "...", "readings": [{"sensor_type": "rain", "value": 2500}, ...]}`
- ✅ Interval: 30 detik
- ✅ WiFi: Configured
- ✅ Sensors: Rain, Soil Moisture, Temperature (DHT11), Tilt (MPU6050)

### 🟢 Backend API (PHP)
- ✅ Endpoint POST: `api/post_reading.php` - Terima data ESP32
- ✅ Endpoint GET: `api/get_data.php` - Data terbaru
- ✅ Endpoint GET: `api/get_history.php` - Historical data
- ✅ Endpoint GET: `api/get_nodes.php` - Node info
- ✅ Endpoint GET: `api/get_notifications.php` - Alerts
- ✅ Database config: `api/config.php`

### 🟢 Database (MySQL)
- ✅ Nama: `MonitoringIOT`
- ✅ Node-1 configured: 4 sensors (rain, soil_moisture, tilt, temperature)
- ✅ Sample data: 100+ historical readings
- ✅ Real-time data: ESP32 berhasil insert data ✅
- ✅ Tables: users, nodes, sensors, sensor_readings, notifications, system_logs

### 🟢 Frontend (Website)
- ✅ Path API: `../api` (relatif dari folder web)
- ✅ Dashboard: `web/dashboard.html`
- ✅ JavaScript: `web/script.js`
- ✅ Auto-refresh: 60 detik
- ✅ Charts: Chart.js

---

## 🎯 URL Akses

| Component | URL |
|-----------|-----|
| **Dashboard** | `http://localhost/MonitoringIOT/web/dashboard.html` ⭐ |
| Landing Page | `http://localhost/MonitoringIOT/web/index.html` |
| History | `http://localhost/MonitoringIOT/web/history.html` |
| API Test | `http://localhost/MonitoringIOT/web/test-api.html` |
| API Endpoint | `http://localhost/MonitoringIOT/api/` |
| phpMyAdmin | `http://localhost/phpmyadmin` |

---

## ✅ Verifikasi Integrasi

### Test 1: Database Connection
```bash
cd C:\xampp\htdocs\MonitoringIOT
php test_database.php
```
**Expected Output:**
```
✅ Database connected!
✅ Node-1 ditemukan (ID: 1)
Jumlah sensor: 4
Latest 10 readings: ... (menampilkan data terbaru)
```

### Test 2: ESP32 → Database
1. Upload `arduino/sicurah_esp32_v3_FIXED.ino` ke ESP32
2. Buka Serial Monitor (115200 baud)
3. Lihat response: `inserted_count: 4` ✅

### Test 3: Website → Database
1. Buka `http://localhost/MonitoringIOT/web/dashboard.html`
2. Lihat card sensor (harus tampil data dari database)
3. Cek browser console (F12) - tidak ada error ✅

---

## 📦 File Penting

| File | Lokasi | Fungsi | Status |
|------|--------|--------|--------|
| ESP32 Code | `arduino/sicurah_esp32_v3_FIXED.ino` | Firmware ESP32 | ✅ FINAL |
| Database | `database/MonitoringIOT.sql` | Database schema + data | ✅ Ready |
| API Config | `api/config.php` | Database connection | ✅ Configured |
| API POST | `api/post_reading.php` | Receive ESP32 data | ✅ Working |
| Dashboard | `web/dashboard.html` | Main dashboard | ✅ Working |
| JavaScript | `web/script.js` | API calls & UI | ✅ Path fixed |

---

## 🚀 Next Steps

1. ✅ **Struktur folder sudah rapi**
2. ✅ **Database sudah terima data dari ESP32**
3. ✅ **Website sudah akses database**
4. ✅ **Integrasi ESP32 → API → Database → Website BERHASIL!**

**SISTEM SIAP DIGUNAKAN!** 🎉

---

Last updated: 2025-11-20 14:10 WIB
