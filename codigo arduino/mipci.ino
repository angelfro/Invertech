#include "SensorLib.h"
#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>

// Se especifican los pines: DS18B20 en pines 21 y 5, y sensores de humedad en pines 32 y 33
SensorLib sensor(21, 5, 32, 33);

// Credenciales WiFi
const char* ssid = "MIPCI";
const char* password = "Mipci06Proj3ct";

// URL del servidor al que se enviarán los datos
const char* serverUrl = "http://192.168.4.10:3000/api/data";

void setup() {
    Serial.begin(115200);

    // Conexión WiFi
    WiFi.begin(ssid, password);
    Serial.print("Conectando a WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nConectado a WiFi");

    sensor.begin();
}

void loop() {
    float temp1 = sensor.getTemperature(1);
    float temp2 = sensor.getTemperature(2);
    int humidity1 = sensor.getSoilMoisture(1);
    int humidity2 = sensor.getSoilMoisture(2);

    Serial.print("Temp1: "); Serial.print(temp1);
    Serial.print(" C, Temp2: "); Serial.print(temp2);
    Serial.print(" C, Humedad1: "); Serial.print(humidity1);
    Serial.print("%, Humedad2: "); Serial.println(humidity2);

    // Enviar datos al servidor usando GET
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;

        // Construir la URL con los parámetros
        String url = String(serverUrl) + "?temp1=" + String(temp1) + "&temp2=" + String(temp2) +
                     "&humidity1=" + String(humidity1) + "&humidity2=" + String(humidity2);

        http.begin(url);

        int httpResponseCode = http.GET();

        if (httpResponseCode > 0) {
            Serial.print("HTTP Response code: ");
            Serial.println(httpResponseCode);
            String payload = http.getString(); // Obtener la respuesta del servidor
            Serial.println("Payload: " + payload); // Imprimir la respuesta del servidor
        } else {
            Serial.print("Error en la solicitud HTTP: ");
            Serial.println(http.errorToString(httpResponseCode).c_str());
        }

        http.end();
    } else {
        Serial.println("WiFi no conectado, no se pueden enviar datos.");
    }

    delay(5000); // Delay aumentado a 5 segundos
}