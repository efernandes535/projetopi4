import serial ## COMO CHAMEI PORTA DO ARDUINO ##
import mysql.connector
import time
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns

## Configuração da porta serial (substitua 'COM3' pelo nome correto no seu PC) ##
porta_serial = serial.Serial('COM3', 9600, timeout=1)
time.sleep(2)  ## Aguarde a conexão estabilizar ##

## Conexão com o Banco de Dados Remoto ##
conexao = mysql.connector.connect(
    host="sql10.freesqldatabase.com",
    user="sql10769167",
    password="nxilFR6z9x",
    database="sql10769167",
    port=3306
)

cursor = conexao.cursor()

print("Iniciando a leitura do Arduino e o armazenamento no banco de dados...")

try:
    while True:
        linha = porta_serial.readline().decode().strip()  # Lê os dados do Arduino
        if linha:
            try:
                temperatura, umidade = linha.split(",")  # Separa temperatura e umidade
                temperatura = float(temperatura)
                umidade = float(umidade)

                # Inserindo os dados no MySQL
                sql = "INSERT INTO dados (temperatura, umidade) VALUES (%s, %s)"
                valores = (temperatura, umidade)
                cursor.execute(sql, valores)
                conexao.commit()

                print(f"Salvo no banco: {temperatura}°C, {umidade}%")
            except ValueError:
                print(f"Erro ao processar a linha: {linha}")  # Caso a leitura falhe
except KeyboardInterrupt:
    print("Encerrando a conexão...")
    porta_serial.close()
    cursor.close()
    conexao.close()

### COMEÇANDO NOSSA ANALISE DOS DADOS -
# Recuperar os dados do banco para análise ###

cursor.execute("SELECT temperatura, umidade, timestamp FROM dados")
dados = cursor.fetchall()

### Criando um DataFrame para análise  ###
df = pd.DataFrame(dados, columns=["temperatura", "umidade", "timestamp"])

####  Estatísticas básicas ####
media_temperatura = df["temperatura"].mean()
mediana_temperatura = df["temperatura"].median()

print(f"\nMédia da Temperatura: {media_temperatura:.2f} °C")
print(f"Mediana da Temperatura: {mediana_temperatura:.2f} °C")

### Converter timestamp para formato de data #####
df["timestamp"] = pd.to_datetime(df["timestamp"])

#### Criando gráfico de linha para temperatura e umidade ao longo do tempo ####
plt.figure(figsize=(12, 6))
sns.lineplot(x=df["timestamp"], y=df["temperatura"], label="Temperatura (°C)", color="red")
sns.lineplot(x=df["timestamp"], y=df["umidade"], label="Umidade (%)", color="blue")

plt.xlabel("Tempo")
plt.ylabel("Valores")
plt.title("Evolução da Temperatura e Umidade")
plt.legend()
plt.xticks(rotation=45)
plt.show()