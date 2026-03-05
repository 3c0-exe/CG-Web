/*
 * ClassGuard RFID Attendance System
 * ESP32 + W5500 Ethernet + MFRC522 RFID + HiveMQ MQTT
 *
 * Pin Connections:
 * W5500:   SCK=18, MOSI=23, MISO=19, CS=5,  RST=NC
 * MFRC522: SCK=18, MOSI=23, MISO=19, CS=21, RST=22
 * (W5500 and MFRC522 share SPI bus, different CS pins)
 */

#include <SPI.h>
#include <Ethernet2.h>
#include <PubSubClient.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// ─── Pin Definitions ──────────────────────────────────────────────────────────
#define W5500_CS  5
#define RFID_CS   21
#define RFID_RST  22

// ─── Network Config ───────────────────────────────────────────────────────────
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
// Leave IP as 0 for DHCP, or set static:
// IPAddress ip(192, 168, 1, 100);

// ─── MQTT Config ─────────────────────────────────────────────────────────────
const char* mqttHost     = "34378fb555c240208696d21494809dbf.s1.eu.hivemq.cloud";
const int   mqttPort     = 8883;
const char* mqttUsername = "ClassGuard";
const char* mqttPassword = "Classguardadmin12";
const char* clientId     = "ESP32-ClassGuard";

// ─── MQTT Topics ─────────────────────────────────────────────────────────────
const char* TOPIC_SESSION_START = "attendance/session/start";
const char* TOPIC_SESSION_END   = "attendance/session/end";
const char* TOPIC_SCAN_RAW      = "attendance/scan/raw";

// ─── State ────────────────────────────────────────────────────────────────────
String activeSessionId = "";
bool   sessionActive   = false;

// ─── Objects ─────────────────────────────────────────────────────────────────
EthernetClient ethClient;
PubSubClient   mqtt(ethClient);
MFRC522        rfid(RFID_CS, RFID_RST);

// ─── Forward Declarations ────────────────────────────────────────────────────
void connectMQTT();
void mqttCallback(char* topic, byte* payload, unsigned int length);
void publishCardScan(String uid);
String getUID();

// ─── Setup ───────────────────────────────────────────────────────────────────
void setup() {
    Serial.begin(115200);
    delay(500);
    Serial.println("\n=== ClassGuard RFID System Starting ===");

    // Start SPI
    SPI.begin();

    // Start Ethernet (W5500)
    Ethernet.init(W5500_CS);
    Serial.print("Connecting to network via DHCP...");
    if (Ethernet.begin(mac)) {
        Serial.println(" OK");
        Serial.print("IP Address: ");
        Serial.println(Ethernet.localIP());
    } else {
        Serial.println(" FAILED - check cable");
        while (true); // halt
    }

    delay(1000);

    // Start MQTT
    mqtt.setServer(mqttHost, mqttPort);
    mqtt.setCallback(mqttCallback);
    mqtt.setBufferSize(512);
    connectMQTT();

    // Start RFID
    rfid.PCD_Init();
    Serial.print("RFID Reader: ");
    rfid.PCD_DumpVersionToSerial();

    Serial.println("=== System Ready — Waiting for session... ===");
}

// ─── Loop ────────────────────────────────────────────────────────────────────
void loop() {
    // Maintain MQTT connection
    if (!mqtt.connected()) {
        connectMQTT();
    }
    mqtt.loop();

    // Only scan cards when session is active
    if (!sessionActive) return;

    if (!rfid.PICC_IsNewCardPresent()) return;
    if (!rfid.PICC_ReadCardSerial()) return;

    String uid = getUID();
    Serial.println("Card Detected: " + uid);
    publishCardScan(uid);

    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(1500); // debounce
}

// ─── MQTT Connect ────────────────────────────────────────────────────────────
void connectMQTT() {
    Serial.print("Connecting to HiveMQ...");
    int attempts = 0;

    while (!mqtt.connected() && attempts < 5) {
        if (mqtt.connect(clientId, mqttUsername, mqttPassword)) {
            Serial.println(" Connected!");

            // Subscribe to session control topics
            mqtt.subscribe(TOPIC_SESSION_START);
            mqtt.subscribe(TOPIC_SESSION_END);

            Serial.println("Subscribed to session topics.");
        } else {
            Serial.print(" Failed (rc=");
            Serial.print(mqtt.state());
            Serial.println(") Retrying in 5s...");
            delay(5000);
            attempts++;
        }
    }
}

// ─── MQTT Callback ───────────────────────────────────────────────────────────
void mqttCallback(char* topic, byte* payload, unsigned int length) {
    String topicStr  = String(topic);
    String message   = "";

    for (unsigned int i = 0; i < length; i++) {
        message += (char)payload[i];
    }

    Serial.println("MQTT [" + topicStr + "]: " + message);

    // Parse JSON
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, message);
    if (error) {
        Serial.println("JSON parse error: " + String(error.c_str()));
        return;
    }

    if (topicStr == TOPIC_SESSION_START) {
        activeSessionId = doc["session_id"].as<String>();
        sessionActive   = true;
        Serial.println("✅ Session Started: " + activeSessionId);
        Serial.println("📡 Ready to scan cards!");

    } else if (topicStr == TOPIC_SESSION_END) {
        String endedSession = doc["session_id"].as<String>();
        if (endedSession == activeSessionId) {
            activeSessionId = "";
            sessionActive   = false;
            Serial.println("🔴 Session Ended.");
        }
    }
}

// ─── Publish Card Scan ───────────────────────────────────────────────────────
void publishCardScan(String uid) {
    StaticJsonDocument<128> doc;
    doc["session_id"] = activeSessionId;
    doc["uid"]        = uid;

    char buffer[128];
    serializeJson(doc, buffer);

    if (mqtt.publish(TOPIC_SCAN_RAW, buffer)) {
        Serial.println("📤 Scan published: " + uid);
    } else {
        Serial.println("❌ Publish failed!");
    }
}

// ─── Get UID String ──────────────────────────────────────────────────────────
String getUID() {
    String uid = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) uid += "0";
        uid += String(rfid.uid.uidByte[i], HEX);
        if (i < rfid.uid.size - 1) uid += ":";
    }
    uid.toUpperCase();
    return uid;
}
