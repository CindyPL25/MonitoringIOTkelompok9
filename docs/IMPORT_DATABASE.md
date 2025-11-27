# 📥 CARA IMPORT DATABASE - MonitoringIOT

## ⚡ SUPER MUDAH! (2 Menit Selesai)

---

## 📍 File Database Yang Benar:

```
✅ database/MonitoringIOT.sql  ← IMPORT FILE INI!
```

**PENTING:** Jangan import file lain! Hanya file ini yang benar dan lengkap.

---

## 🎯 Metode 1: Via phpMyAdmin (RECOMMENDED)

### Langkah-langkah:

**1. Buka phpMyAdmin**
```
URL: http://localhost/phpmyadmin
```

**2. Klik Tab "Import"**
```
Di menu atas, klik tab "Import"
```

**3. Pilih File SQL**
```
- Klik tombol "Choose File" atau "Browse"
- Cari file: C:\xampp\htdocs\MonitoringIOT\database\MonitoringIOT.sql
- Pilih file tersebut
```

**4. Klik "Go"**
```
- Scroll ke bawah
- Klik tombol "Go"
- Tunggu proses import selesai (5-10 detik)
```

**5. Verifikasi**
```
✓ Di sidebar kiri, akan muncul database "MonitoringIOT"
✓ Klik database tersebut
✓ Akan muncul 6 tabel:
  - users
  - nodes
  - sensors
  - sensor_readings
  - notifications
  - system_logs
```

**6. Cek Isi Data**
```sql
-- Klik tab "SQL" dan jalankan query ini:
SELECT 'users' as tabel, COUNT(*) as total FROM users
UNION ALL
SELECT 'nodes', COUNT(*) FROM nodes
UNION ALL
SELECT 'sensors', COUNT(*) FROM sensors
UNION ALL
SELECT 'sensor_readings', COUNT(*) FROM sensor_readings
UNION ALL
SELECT 'notifications', COUNT(*) FROM notifications
UNION ALL
SELECT 'system_logs', COUNT(*) FROM system_logs;

-- Expected Result:
-- users: 3
-- nodes: 3
-- sensors: 12
-- sensor_readings: 100+
-- notifications: 9
-- system_logs: 10
```

**✅ SELESAI! Database siap digunakan!**

---

## 🎯 Metode 2: Via Command Line (Alternative)

### Langkah-langkah:

**1. Buka Command Prompt (CMD)**
```
Windows Key + R
Ketik: cmd
Enter
```

**2. Masuk ke Folder MySQL XAMPP**
```bash
cd C:\xampp\mysql\bin
```

**3. Jalankan Perintah Import**
```bash
mysql -u root -p < "C:\xampp\htdocs\MonitoringIOT\database\MonitoringIOT.sql"
```

**4. Tekan Enter**
```
Password: [Tekan Enter saja - default XAMPP password kosong]
```

**5. Tunggu Selesai**
```
Proses import akan berjalan (5-10 detik)
Tidak ada output = berhasil!
```

**6. Verifikasi**
```bash
# Masuk ke MySQL
mysql -u root -p

# Tekan Enter (password kosong)

# Di prompt MySQL, ketik:
SHOW DATABASES;

# Harusnya muncul database: MonitoringIOT

# Gunakan database:
USE MonitoringIOT;

# Lihat tabel:
SHOW TABLES;

# Keluar:
EXIT;
```

**✅ SELESAI! Database siap digunakan!**

---

## 🧪 Test Database Setelah Import

### Test 1: Cek Semua Tabel Ada
```sql
SHOW TABLES;

-- Expected: 6 tables
-- users
-- nodes
-- sensors
-- sensor_readings
-- notifications
-- system_logs
```

### Test 2: Cek Data Users
```sql
SELECT username, full_name, role FROM users;

-- Expected: 3 users
-- admin        Administrator SICURAH      admin
-- operator1    Operator Lapangan 1        operator
-- viewer1      Viewer Monitoring          viewer
```

### Test 3: Cek Data Nodes
```sql
SELECT node_id, name, location, status FROM nodes;

-- Expected: 3 nodes
-- Node-1   Stasiun Monitoring A   Petobo, Palu Selatan    active
-- Node-2   Stasiun Monitoring B   Balaroa, Palu Barat     active
-- Node-3   Stasiun Monitoring C   Jono Oge, Sigi          active
```

### Test 4: Cek Data Sensors
```sql
SELECT 
    sensor_type, 
    COUNT(*) as total,
    GROUP_CONCAT(DISTINCT unit) as units
FROM sensors 
GROUP BY sensor_type;

-- Expected: 4 jenis sensor
-- rain              3    mm
-- soil_moisture     3    %
-- temperature       3    °C
-- tilt              3    °
```

### Test 5: Cek Data Readings
```sql
SELECT 
    COUNT(*) as total_readings,
    MIN(timestamp) as oldest,
    MAX(timestamp) as newest
FROM sensor_readings;

-- Expected: 100+ readings
-- oldest: 23-24 jam yang lalu
-- newest: now()
```

### Test 6: Cek Notifications
```sql
SELECT 
    level,
    COUNT(*) as total
FROM notifications
GROUP BY level;

-- Expected:
-- danger     3
-- info       3
-- warning    3
```

---

## ❌ Troubleshooting

### Problem 1: "Table already exists"
```
Penyebab: Database sudah pernah diimport sebelumnya

Solusi:
1. Di phpMyAdmin, klik database "MonitoringIOT"
2. Klik tab "Operations"
3. Scroll ke bawah, klik "Drop the database (DROP)"
4. Konfirmasi
5. Import ulang file MonitoringIOT.sql
```

### Problem 2: "Access denied for user"
```
Penyebab: Username/password MySQL salah

Solusi:
1. Default XAMPP username: root
2. Default XAMPP password: kosong (empty)
3. Pastikan MySQL XAMPP sedang running (hijau)
```

### Problem 3: "Cannot connect to MySQL server"
```
Penyebab: MySQL XAMPP tidak aktif

Solusi:
1. Buka XAMPP Control Panel
2. Klik "Start" pada MySQL
3. Tunggu sampai status hijau
4. Coba import lagi
```

### Problem 4: "File not found"
```
Penyebab: Path file salah

Solusi:
1. Pastikan file ada di: C:\xampp\htdocs\MonitoringIOT\database\MonitoringIOT.sql
2. Copy full path yang benar
3. Gunakan double quotes "..." untuk path dengan spasi
```

### Problem 5: Import tampak berhasil tapi tabel kosong
```
Penyebab: Import tidak complete atau error

Solusi:
1. Drop database yang ada
2. Tutup semua aplikasi yang mengakses database
3. Import ulang dengan perhatikan pesan error
4. Pastikan file MonitoringIOT.sql tidak corrupt (size ~37KB)
```

---

## ✅ Checklist Setelah Import

```
[ ] Database "MonitoringIOT" muncul di phpMyAdmin
[ ] Ada 6 tabel (users, nodes, sensors, dll)
[ ] Table users ada 3 records
[ ] Table nodes ada 3 records
[ ] Table sensors ada 12 records
[ ] Table sensor_readings ada 100+ records
[ ] Table notifications ada 9 records
[ ] Table system_logs ada 10 records
[ ] Bisa query: SELECT * FROM nodes; (no error)
[ ] Timestamp data correct (hari ini)
```

---

## 🎯 Next Steps Setelah Import

### 1. Test API
```
Buka: http://localhost/MonitoringIOT/test-api.html
Klik: "Run All Tests"
Hasil: Semua hijau (Success)
```

### 2. Buka Website
```
Buka: http://localhost/MonitoringIOT/
Dashboard tampil dengan data
```

### 3. Setup ESP32 (Optional)
```
Edit: arduino/sicurah_esp32.ino
Update: WiFi SSID, Password, SERVER_URL
Upload ke ESP32
```

---

## 📊 Database Schema Quick Reference

### Table: users
- **Purpose:** User accounts & authentication
- **Records:** 3 (admin, operator1, viewer1)
- **Key Columns:** username, password_hash, role

### Table: nodes
- **Purpose:** IoT device locations
- **Records:** 3 (Node-1, Node-2, Node-3)
- **Key Columns:** node_id, name, location, latitude, longitude

### Table: sensors
- **Purpose:** Sensor configuration
- **Records:** 12 (4 types × 3 nodes)
- **Key Columns:** sensor_type, unit, warning_threshold, danger_threshold

### Table: sensor_readings
- **Purpose:** Time-series sensor data
- **Records:** 100+ (historical data)
- **Key Columns:** sensor_id, value, status, timestamp

### Table: notifications
- **Purpose:** Alert & notification history
- **Records:** 9 (danger, warning, info)
- **Key Columns:** level, message, is_read, created_at

### Table: system_logs
- **Purpose:** System activity logging
- **Records:** 10 (login, sensor data, errors)
- **Key Columns:** log_type, message, created_at

---

## 🔧 Advanced: Manual Database Creation

Jika import gagal terus, bisa create manual:

**1. Create Database:**
```sql
CREATE DATABASE MonitoringIOT 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

**2. Use Database:**
```sql
USE MonitoringIOT;
```

**3. Import File:**
- Buka file MonitoringIOT.sql dengan text editor
- Copy semua isi (kecuali 3 baris pertama CREATE DATABASE)
- Paste di phpMyAdmin SQL tab
- Click "Go"

---

## 📞 Butuh Bantuan?

**Jika masih ada masalah:**

1. Screenshot error message
2. Cek file size MonitoringIOT.sql (harusnya ~37KB)
3. Pastikan XAMPP MySQL running (port 3306 aktif)
4. Coba restart MySQL di XAMPP Control Panel
5. Baca INSTALLATION_GUIDE.md untuk troubleshooting detail

---

## ✨ Tips

💡 **Backup Database**
```sql
-- Setelah berhasil import, buat backup:
-- Di phpMyAdmin, pilih database MonitoringIOT
-- Klik tab "Export"
-- Klik "Go"
-- Save file backup
```

💡 **Reset Database**
```sql
-- Jika mau reset ke kondisi awal:
DROP DATABASE MonitoringIOT;
-- Kemudian import ulang file MonitoringIOT.sql
```

💡 **Check MySQL Port**
```bash
# Di XAMPP Control Panel
# Klik "Config" untuk MySQL
# Lihat port (default: 3306)
# Pastikan tidak bentrok dengan aplikasi lain
```

---

## 🎉 SUCCESS!

**Jika sudah berhasil import:**

✅ Database "MonitoringIOT" active  
✅ 6 Tables created  
✅ 100+ Records inserted  
✅ Ready to connect with API  
✅ Website can display data  
✅ IoT device can send data  

**MANTAP! Database sudah siap digunakan! 🚀**

---

**Next:**  
→ Test API: `QUICK_START.md`  
→ Full Guide: `INSTALLATION_GUIDE.md`  

---

**© 2024 SICURAH - Kelompok 9 PSTI B**
