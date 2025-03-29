#ifndef SENSORLIB_H
#define SENSORLIB_H

#include <Arduino.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFi.h>

class SensorLib {
public:
    // Constructor: pines para DS18B20 y sensores de humedad
    SensorLib(int tempPin1, int tempPin2, int moistPin1, int moistPin2);
    
    // Inicializa los sensores (temperatura y humedad)
    void begin();
    
    // Obtiene la temperatura del sensor indicado (1 o 2)
    float getTemperature(int sensorNum);
    
    // Obtiene la humedad en porcentaje del sensor indicado (1 o 2)
    int getSoilMoisture(int sensorNum);
    
    // Conecta a WiFi
    void connectWiFi(const char* ssid, const char* password);
    
    // Retorna true si se está conectado a WiFi
    bool isWiFiConnected();

private:
    OneWire oneWire1;
    OneWire oneWire2;
    DallasTemperature sensors1;
    DallasTemperature sensors2;
    int moistPin1, moistPin2;
    
    // Valores para calibración del sensor de humedad
    static constexpr int DRY = 2674;
    static constexpr int WET = 980;
};

#endif
