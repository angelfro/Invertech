#include "SensorLib.h"
#include <Arduino.h>

SensorLib::SensorLib(int tempPin1, int tempPin2, int moistPin1, int moistPin2)
    : oneWire1(tempPin1), oneWire2(tempPin2),
      sensors1(&oneWire1), sensors2(&oneWire2),
      moistPin1(moistPin1), moistPin2(moistPin2) {}

void SensorLib::begin() {
    sensors1.begin();
    sensors2.begin();
    pinMode(moistPin1, INPUT);
    pinMode(moistPin2, INPUT);
}

float SensorLib::getTemperature(int sensorNum) {
    if (sensorNum == 1) {
        sensors1.requestTemperatures();
        return sensors1.getTempCByIndex(0);
    } else if (sensorNum == 2) {
        sensors2.requestTemperatures();
        return sensors2.getTempCByIndex(0);
    }
    return -127.0; // Valor de error si el sensor no está conectado
}

int SensorLib::getSoilMoisture(int sensorNum) {
    int sensorVal = (sensorNum == 1) ? analogRead(moistPin1) : analogRead(moistPin2);
    
    Serial.print("Sensor de humedad ");
    Serial.print(sensorNum);
    Serial.print(" valor: ");
    Serial.println(sensorVal);

    // Si el sensor devuelve 0, asumimos error o sensor no conectado.
    if(sensorVal == 0) {
        return -1; // Valor de error
    }
    
    // Se mapea el valor a un porcentaje (100% para WET y 0% para DRY)
    int humidity = map(sensorVal, SensorLib::WET, SensorLib::DRY, 100, 0);
    // Se limita el resultado para que esté entre 0 y 100%
    humidity = constrain(humidity, 0, 100);
    return humidity;
}

void SensorLib::connectWiFi(const char* ssid, const char* password) {
    WiFi.begin(ssid, password);
    Serial.print("Conectando a WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi conectado!");
}

bool SensorLib::isWiFiConnected() {
    return WiFi.status() == WL_CONNECTED;
}
