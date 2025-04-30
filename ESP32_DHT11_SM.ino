#include <WiFi.h>
#include <WebServer.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_GFX.h>
#include <DHT.h>

// Configurações do DHT11
#define DHTPIN 4
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// Configurações do OLED
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Configurações de WiFi
const char* ssid = "Cecilia";
const char* password = "19102017535";

// Configurações do banco de dados
const char* serverUrl = "http://192.168.100.112/api.php";
const char* apiKey = "MinhaChaveSuperSecreta@2025!";

// Variáveis globais
WebServer server(80);
float temperature = 0;
float humidity = 0;
unsigned long previousMillis = 0;
const long sensorInterval = 2000;
const long dbInterval = 30000;
unsigned long lastDbSend = 0;

// Declarações antecipadas de funções
void handle_OnConnect();
void handle_Data();
void handle_Reset();
void handle_NotFound();
void updateDisplay();
void sendToDatabase(float temp, float hum);
float readDHTTemperature();
float readDHTHumidity();

void setup() {
  Serial.begin(115200);
  
  // Inicialização do OLED
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("Falha na inicialização do OLED"));
    for(;;);
  }
  display.clearDisplay();
  
  // Inicializa o sensor DHT
  dht.begin();
  
  // Conecta ao WiFi
  WiFi.begin(ssid, password);
  Serial.println("Conectando ao WiFi...");
  
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);
  display.setCursor(0,0);
  display.println("Conectando WiFi...");
  display.display();
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    display.print(".");
    display.display();
  }
  
  Serial.println("");
  Serial.println("WiFi conectado");
  Serial.print("Endereço IP: ");
  Serial.println(WiFi.localIP());
  
  display.clearDisplay();
  display.setCursor(0,0);
  display.println("WiFi conectado!");
  display.print("IP: ");
  display.println(WiFi.localIP());
  display.display();
  delay(1000);
  
  // Configura as rotas do servidor web
  server.on("/", handle_OnConnect);
  server.on("/data", handle_Data);
  server.on("/reset", HTTP_POST, handle_Reset);
  server.onNotFound(handle_NotFound);
  
  server.begin();
  Serial.println("Servidor HTTP iniciado");
}

void loop() {
  server.handleClient();
  
  unsigned long currentMillis = millis();
  
  // Leitura dos sensores
  if (currentMillis - previousMillis >= sensorInterval) {
    previousMillis = currentMillis;
    
    float newTemp = readDHTTemperature();
    float newHum = readDHTHumidity();
    
    if (!isnan(newTemp) && !isnan(newHum)) {
      temperature = newTemp;
      humidity = newHum;
      
      updateDisplay();
    } else {
      Serial.println("Falha na leitura do sensor DHT!");
    }
  }
  
  // Envio para o banco de dados
  if (currentMillis - lastDbSend >= dbInterval) {
    lastDbSend = currentMillis;
    sendToDatabase(temperature, humidity);
  }
}

void handle_OnConnect() {
  String html = R"=====(
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Ambiental</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .sensor { margin: 20px 0; }
        .temp { color: #e74c3c; font-size: 24px; font-weight: bold; }
        .humidity { color: #3498db; font-size: 24px; font-weight: bold; }
        h1 { color: #2c3e50; }
        .status { font-size: 0.9em; color: #7f8c8d; margin-bottom: 20px; }
        footer { margin-top: 20px; font-size: 12px; color: #7f8c8d; }
        .btn { background-color: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Monitor Ambiental</h1>
        <div class="status">
            Conectado ao ESP32 | IP: )=====";
  html += WiFi.localIP().toString();
  html += R"=====( | Última atualização: <span id="lastUpdate"></span>
        </div>
        
        <div class="sensor">
            <p>Temperatura: <span class="temp" id="temperature">)=====";
  html += String(temperature, 1);
  html += R"=====( °C</span></p>
        </div>
        
        <div class="sensor">
            <p>Umidade: <span class="humidity" id="humidity">)=====";
  html += String(humidity, 1);
  html += R"=====( %</span></p>
        </div>
        
        <div>
            <button class="btn" onclick="fetchData()">Atualizar</button>
            <button class="btn" onclick="resetESP()">Reiniciar ESP</button>
        </div>
        
        <footer>
            Sistema de monitoramento com ESP32 | DHT11 | MySQL
        </footer>
    </div>

    <script>
        function updateData(data) {
            document.getElementById('temperature').textContent = data.temperature.toFixed(1) + ' °C';
            document.getElementById('humidity').textContent = data.humidity.toFixed(1) + ' %';
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
        }
        
        function fetchData() {
            fetch('/data')
                .then(response => response.json())
                .then(data => updateData(data))
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('lastUpdate').textContent = 'Erro na atualização';
                });
        }
        
        function resetESP() {
            if(confirm('Tem certeza que deseja reiniciar o ESP32?')) {
                fetch('/reset', { method: 'POST' })
                    .then(() => alert('ESP32 será reiniciado'))
                    .catch(err => alert('Erro: ' + err));
            }
        }
        
        // Atualiza a cada 5 segundos
        setInterval(fetchData, 5000);
        // Carrega os dados inicialmente
        fetchData();
    </script>
</body>
</html>
)=====";

  server.send(200, "text/html", html);
}

void handle_Data() {
  String json = "{";
  json += "\"temperature\":" + String(temperature, 1) + ",";
  json += "\"humidity\":" + String(humidity, 1);
  json += "}";
  
  server.send(200, "application/json", json);
}

void handle_Reset() {
  server.send(200, "text/plain", "ESP32 reiniciando...");
  delay(1000);
  ESP.restart();
}

void handle_NotFound() {
  server.send(404, "text/plain", "Página não encontrada");
}

void updateDisplay() {
  display.clearDisplay();
  
  // Linha 1: Cabeçalho
  display.setTextSize(1);
  display.setCursor(0,0);
  display.print("Monitor Ambiental");
  
  // Linha 2: Temperatura (com símbolo de grau corrigido)
  display.setCursor(0,15);
  display.print("Temp: ");
  display.print(temperature, 1);
  display.print(" ");
  display.cp437(true);  // Usa tabela de caracteres CP437
  display.write(248);   // Código 248 para o símbolo de grau
  display.print("C");
  
  // Linha 3: Umidade
  display.setCursor(0,30);
  display.print("Umidade: ");
  display.print(humidity, 1);
  display.print("%");
  
  // Linha 4: Status do WiFi
  display.setCursor(0,45);
  display.print("WiFi: ");
  display.print(WiFi.RSSI());
  display.print("dBm");
  
  // Linha 5: Status do banco de dados e IP
  display.setTextSize(1);
  display.setCursor(0,55);
  display.print("IP: ");
  display.print(WiFi.localIP());
  
  display.display();
}

void sendToDatabase(float temp, float hum) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi não conectado, não é possível enviar para o banco de dados");
    return;
  }

  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  // Cria o objeto JSON com os dados
  DynamicJsonDocument doc(1024);
  doc["api_key"] = apiKey;
  doc["temperature"] = temp;
  doc["humidity"] = hum;
  doc["device_id"] = "ESP32_" + String(WiFi.macAddress());
  
  String requestBody;
  serializeJson(doc, requestBody);
  
  Serial.println("Enviando dados para o servidor:");
  Serial.println(requestBody);
  
  int httpResponseCode = http.POST(requestBody);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Resposta do servidor: ");
    Serial.println(httpResponseCode);
    Serial.println(response);
    
    // Atualiza o display com status do envio
    display.fillRect(0, 55, 128, 9, BLACK);
    display.setCursor(0,55);
    display.print("DB:OK ");
    display.print(millis() / 1000);
    display.print("s");
    display.display();
  } else {
    Serial.print("Erro no envio: ");
    Serial.println(httpResponseCode);
    
    display.fillRect(0, 55, 128, 9, BLACK);
    display.setCursor(0,55);
    display.print("DB Erro:");
    display.print(httpResponseCode);
    display.display();
  }
  
  http.end();
}

float readDHTTemperature() {
  float t = dht.readTemperature();
  if (isnan(t)) {    
    Serial.println("Failed to read from DHT sensor!");
    return -1;
  }
  return t;
}

float readDHTHumidity() {
  float h = dht.readHumidity();
  if (isnan(h)) {
    Serial.println("Failed to read from DHT sensor!");
    return -1;
  }
  return h;
}