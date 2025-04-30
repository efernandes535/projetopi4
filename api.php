<?php
header('Content-Type: application/json');

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "monitor_ambiente";

// Segurança - valida a chave API (opcional)
$valid_api_key = "MinhaChaveSuperSecreta@2025!"; // Deve corresponder à chave no código do ESP32

// Recebe os dados
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    die(json_encode(['status' => 'error', 'message' => 'Dados inválidos']));
}

// Verifica a chave API (opcional)
if (isset($valid_api_key) && (!isset($data['api_key']) || $data['api_key'] !== $valid_api_key)) {
    die(json_encode(['status' => 'error', 'message' => 'Chave API inválida']));
}

// Valida os dados
if (!isset($data['temperature']) || !isset($data['humidity'])) {
    die(json_encode(['status' => 'error', 'message' => 'Dados incompletos']));
}

// Conexão com o MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Erro de conexão: ' . $conn->connect_error]));
}

// Prepara e executa a query
$stmt = $conn->prepare("INSERT INTO sensor_data (device_id, temperature, humidity) VALUES (?, ?, ?)");
$device_id = isset($data['device_id']) ? $data['device_id'] : 'ESP32';
$stmt->bind_param("sdd", $device_id, $data['temperature'], $data['humidity']);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Dados salvos com sucesso']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar dados: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>