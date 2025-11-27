#include <Wire.h>
#include <DHT.h>
#include <MPU6050.h>
#include <math.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ========== DEKLARASI PIN & SENSOR ==========
#define SDA_PIN 21
#define SCL_PIN 22
#define DHTPIN 19
#define DHTTYPE DHT11
#define RAIN_AO 32
#define RAIN_DO 33
#define SOIL_AO 34
#define SOIL_DO 35
#define LED_TILT 13    // 🔴 Merah
#define LED_DHT 12     // ⚪ Putih  
#define LED_RAIN 14    // 🔵 Biru
#define LED_SOIL 27    // 🟢 Hijau
#define BUZZER_PIN 4

// ========== OBJEK SENSOR ==========
DHT dht(DHTPIN, DHTTYPE);
MPU6050 mpu;

// ========== KONFIGURASI PARAMETER ==========
int soilWetThreshold = 1800;    // analog < 1800 → tanah basah
int humidityThreshold = 85;     // > 85% → lembab tinggi  
int tiltThresholdDeg = 30;      // > 30° → miring

// ========== KONFIGURASI WIFI & SERVER ==========
const char* ssid = ".Antique";
const char* password = "12122121";
const char* serverName = "http://172.19.182.126/MonitoringIOT/api/post_reading.php";
const char* API_KEY = "SICURAH_2024_SECRET_KEY";
const char* NODE_ID = "Node-1";

// ========== VARIABEL TIMING ==========
unsigned long lastSendTime = 0;
const unsigned long SEND_INTERVAL = 30000; // Kirim data setiap 30 detik

void setup() {
  Serial.begin(115200);
  delay(500);

  // ========== INISIALISASI PIN ==========
  pinMode(RAIN_AO, INPUT);
  pinMode(RAIN_DO, INPUT);
  pinMode(SOIL_AO, INPUT);
  pinMode(SOIL_DO, INPUT);
  pinMode(LED_TILT, OUTPUT);
  pinMode(LED_DHT, OUTPUT);
  pinMode(LED_RAIN, OUTPUT);
  pinMode(LED_SOIL, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  // Matikan semua LED dan buzzer awal
  digitalWrite(LED_TILT, LOW);
  digitalWrite(LED_DHT, LOW);
  digitalWrite(LED_RAIN, LOW);
  digitalWrite(LED_SOIL, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  // ========== INISIALISASI SENSOR ==========
  dht.begin();
  Wire.begin(SDA_PIN, SCL_PIN);
  mpu.initialize();

  Serial.println("===== SISTEM MONITORING SICURAH V3.0 AKTIF =====");
  if (mpu.testConnection()) {
    Serial.println("✅ MPU6050 Terdeteksi");
  } else {
    Serial.println("⚠ MPU6050 Tidak Terdeteksi!");
  }

  // ========== KONEKSI WIFI ==========
  Serial.print("🔗 Menghubungkan ke WiFi: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Terhubung!");
    Serial.print("📡 Alamat IP ESP32: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n❌ WiFi Gagal Terhubung!");
  }
  
  Serial.println("===============================================");
}

void loop() {
  // ========== BACA SEMUA SENSOR ==========
  int rainAnalog = analogRead(RAIN_AO);
  int rainDigital = digitalRead(RAIN_DO);
  int soilAnalog = analogRead(SOIL_AO);
  int soilDigital = digitalRead(SOIL_DO);
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  
  // Baca sensor MPU6050
  int16_t ax, ay, az;
  mpu.getAcceleration(&ax, &ay, &az);
  float pitch = atan2(ax, sqrt(ay * ay + az * az)) * 180.0 / PI;
  float roll = atan2(ay, sqrt(ax * ax + az * az)) * 180.0 / PI;
  float tilt = max(abs(pitch), abs(roll));

  // ========== LOGIKA KONDISI ==========
  bool isRaining   = (rainDigital == LOW);
  bool soilWet     = (soilAnalog < soilWetThreshold) || (soilDigital == LOW);
  bool humidHigh   = (humidity > humidityThreshold);
  bool tiltDanger  = (tilt > tiltThresholdDeg);

  // ========== INDIKATOR LED ==========
  digitalWrite(LED_RAIN, isRaining ? HIGH : LOW);
  digitalWrite(LED_SOIL, soilWet ? HIGH : LOW);
  digitalWrite(LED_DHT, humidHigh ? HIGH : LOW);
  digitalWrite(LED_TILT, tiltDanger ? HIGH : LOW);

  // ========== HITUNG JUMLAH BAHAYA ==========
  int dangerCount = (isRaining ? 1 : 0) + (soilWet ? 1 : 0) + 
                   (humidHigh ? 1 : 0) + (tiltDanger ? 1 : 0);

  // ========== KENDALI BUZZER ==========
  kontrolBuzzer(dangerCount);

  // ========== TAMPILKAN DI SERIAL MONITOR ==========
  tampilkanSerialMonitor(rainAnalog, rainDigital, soilAnalog, soilDigital,
                        temperature, humidity, tilt, pitch, roll, dangerCount);

  // ========== KIRIM DATA KE SERVER ==========
  unsigned long currentTime = millis();
  if (currentTime - lastSendTime >= SEND_INTERVAL) {
    lastSendTime = currentTime;
    
    if (WiFi.status() == WL_CONNECTED) {
      kirimDataKeServer(rainAnalog, soilAnalog, temperature, tilt);
    } else {
      Serial.println("⚠ WiFi tidak terhubung! Mencoba reconnect...");
      setupWiFi();
    }
  }

  delay(100); // Delay kecil untuk stabilitas
}

// ========== FUNGSI KENDALI BUZZER ==========
void kontrolBuzzer(int dangerCount) {
  if (dangerCount == 0) {
    noTone(BUZZER_PIN);
  } 
  else if (dangerCount == 1) {
    tone(BUZZER_PIN, 800);
    delay(100);
    noTone(BUZZER_PIN);
    delay(900);
  } 
  else if (dangerCount == 2) {
    tone(BUZZER_PIN, 1000);
    delay(150);
    noTone(BUZZER_PIN);
    delay(500);
  } 
  else if (dangerCount == 3) {
    tone(BUZZER_PIN, 1200);
    delay(200);
    noTone(BUZZER_PIN);
    delay(300);
  } 
  else if (dangerCount >= 4) {
    for (int i = 0; i < 4; i++) {
      tone(BUZZER_PIN, 1800);
      delay(150);
      tone(BUZZER_PIN, 2000);
      delay(150);
      noTone(BUZZER_PIN);
      delay(80);
    }
  }
}

// ========== FUNGSI TAMPILKAN SERIAL MONITOR ==========
void tampilkanSerialMonitor(int rainAnalog, int rainDigital, int soilAnalog, 
                           int soilDigital, float temperature, float humidity,
                           float tilt, float pitch, float roll, int dangerCount) {
  Serial.println("\n===== STATUS SENSOR =====");
  Serial.printf("💧 Rain AO=%d DO=%d → %s\n", rainAnalog, rainDigital, 
                (rainDigital == LOW) ? "HUJAN" : "TIDAK HUJAN");
  Serial.printf("🌱 Soil AO=%d DO=%d → %s\n", soilAnalog, soilDigital,
                (soilAnalog < soilWetThreshold || soilDigital == LOW) ? "TANAH BASAH" : "KERING");
  Serial.printf("🌤 DHT Suhu=%.1f°C | Lembab=%.1f%% → %s\n", temperature, humidity, 
                (humidity > humidityThreshold) ? "LEMBAB TINGGI" : "NORMAL");
  Serial.printf("⚠ MPU6050 Tilt=%.2f° (Pitch=%.2f°, Roll=%.2f°) → %s\n", tilt, pitch, roll, 
                (tilt > tiltThresholdDeg) ? "MIRING" : "NORMAL");
  Serial.printf("🔢 Jumlah Kondisi Bahaya: %d\n", dangerCount);

  // Status berdasarkan dangerCount
  if (dangerCount == 0) Serial.println("✅ STATUS: AMAN");
  else if (dangerCount == 1) Serial.println("⚠ PERINGATAN: Satu kondisi terdeteksi!");
  else if (dangerCount == 2) Serial.println("🚨 SIAGA: Dua kondisi aktif!");
  else if (dangerCount == 3) Serial.println("🚨⚠ BAHAYA TINGGI: Tiga kondisi aktif!");
  else Serial.println("🚨🚨🚨 BAHAYA MAKSIMUM: Semua sensor melebihi ambang batas!");
  Serial.println("====================================");
}

// ========== FUNGSI KIRIM DATA KE SERVER (FIXED!) ==========
void kirimDataKeServer(int rainAnalog, int soilAnalog, float temperature, float tilt) {
  HTTPClient http;
  
  // Buat JSON payload dengan format yang BENAR
  StaticJsonDocument<512> doc;
  doc["node_id"] = NODE_ID;
  doc["api_key"] = API_KEY;
  
  JsonArray readings = doc.createNestedArray("readings");
  
  // PENTING: Harus ada field "sensor_type" dan "value" untuk setiap sensor!
  JsonObject rainData = readings.createNestedObject();
  rainData["sensor_type"] = "rain";
  rainData["value"] = rainAnalog;  // ✅ PHP butuh field "value"
  
  JsonObject soilData = readings.createNestedObject();
  soilData["sensor_type"] = "soil_moisture";
  soilData["value"] = soilAnalog;  // ✅ PHP butuh field "value"
  
  JsonObject tempData = readings.createNestedObject();
  tempData["sensor_type"] = "temperature";
  tempData["value"] = temperature;  // ✅ PHP butuh field "value"
  
  JsonObject tiltData = readings.createNestedObject();
  tiltData["sensor_type"] = "tilt";
  tiltData["value"] = tilt;  // ✅ PHP butuh field "value"
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("📡 Mengirim data ke server...");
  Serial.println("JSON Payload: " + jsonString);
  
  http.begin(serverName);
  http.addHeader("Content-Type", "application/json");
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    Serial.printf("✅ Data terkirim ke server (Kode: %d)\n", httpResponseCode);
    String response = http.getString();
    Serial.println("Response: " + response);
  } else {
    Serial.printf("❌ Gagal kirim! Kode: %d\n", httpResponseCode);
    Serial.println("Error: " + http.errorToString(httpResponseCode));
  }
  
  http.end();
}

// ========== FUNGSI SETUP WIFI (UNTUK RECONNECT) ==========
void setupWiFi() {
  Serial.println("Mencoba menghubungkan ke WiFi...");
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 10) {
    delay(1000);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Terhubung Kembali!");
  }
}
