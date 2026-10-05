#include <DHT.h>

// -------------------------
// Configuración del DHT11
// -------------------------
#define DHTPIN 2
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);

// -------------------------
// Sensor de humedad del suelo
// -------------------------
const int sensorSuelo = A0;

void setup() {

  Serial.begin(9600);

  dht.begin();

  Serial.println("=== AGRISENSE INICIADO ===");
}

void loop() {

  // Leer DHT11
  float temperatura = dht.readTemperature();
  float humedadAire = dht.readHumidity();

  // Leer humedad del suelo
  int humedadSuelo = analogRead(sensorSuelo);

  // Verificar lectura del DHT11
  if (isnan(temperatura) || isnan(humedadAire)) {
    Serial.println("ERROR,DHT11");
    delay(3000);
    return;
  }

  // Enviar datos en formato CSV
  // temperatura,humedadAire,humedadSuelo
  Serial.print(temperatura);
  Serial.print(",");
  Serial.print(humedadAire);
  Serial.print(",");
  Serial.println(humedadSuelo);

  delay(3000);
}
