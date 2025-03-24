# Projeto PI4: Controle de Umidade e Temperatura com ESP32 e DHT11

## Descrição

Este projeto tem como objetivo monitorar a umidade e a temperatura ambiente utilizando um microcontrolador ESP32 e o sensor DHT11. Os dados coletados são exibidos e podem ser utilizados para diversos fins, como:

*   **Monitoramento Ambiental:** Acompanhar as condições climáticas de um ambiente específico.
*   **Automação Residencial:** Integrar com sistemas de automação para controlar umidificadores, aquecedores, etc.
*   **Projetos de IoT (Internet das Coisas):** Enviar os dados para uma plataforma de nuvem para análise e visualização remota.
*   **Alertas:** Enviar notificações quando a temperatura ou umidade ultrapassarem os limites definidos.

## Materiais Necessários

*   1 x ESP32 (NodeMCU, ESP32 DevKit v1, etc.)
*   1 x Sensor DHT11
*   Jumpers (fios)
*   (Opcional) Protoboard para facilitar a montagem
*   (Opcional) Display LCD/OLED para visualização local dos dados
*   (Opcional) Resistores (verifique a necessidade dependendo da configuração do display)

## Diagrama de Ligação

Aqui está um diagrama de ligação básico.  Adapte os pinos conforme necessário e com base na sua configuração específica:


DHT11	ESP32

VCC     |   3.3V
Data    |   GPIO 4 (ou outro pino digital)
GND     |   GND

**Observações:**

*   Consulte o datasheet do DHT11 e do ESP32 para obter informações detalhadas sobre os pinos e as especificações elétricas.
*   Se estiver usando um display, siga as instruções de ligação específicas para o modelo do display que você está utilizando.


### Bibliotecas Necessárias

*   **DHT Sensor Library:**  Essa biblioteca facilita a leitura dos dados do sensor DHT11.  Você pode instalá-la através do Library Manager na IDE do Arduino. (Procure por "DHT sensor library" por Adafruit)
*   (Opcional) **LiquidCrystal_I2C (ou similar):** Se estiver usando um display LCD/OLED, instale a biblioteca correspondente.