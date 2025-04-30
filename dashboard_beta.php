<?php
// Conexão com o banco (mesmas credenciais do api.php)
$conn = new mysqli("localhost", "root", "", "monitor_ambiente");

// Consulta os últimos 100 registros
$result = $conn->query("SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT 100");
$data = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard de Monitoramento</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container { width: 80%; margin: 20px auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <div class="chart-container">
        <canvas id="tempChart"></canvas>
    </div>
    
    <div class="chart-container">
        <canvas id="humChart"></canvas>
    </div>

    <script>
        const data = <?php echo json_encode($data); ?>;
        
        // Gráfico de Temperatura
        new Chart(document.getElementById('tempChart'), {
            type: 'line',
            data: {
                labels: data.map(item => item.reading_time),
                datasets: [{
                    label: 'Temperatura °C',
                    data: data.map(item => item.temperature),
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            }
        });

        // Gráfico de Umidade
        new Chart(document.getElementById('humChart'), {
            type: 'line',
            data: {
                labels: data.map(item => item.reading_time),
                datasets: [{
                    label: 'Umidade %',
                    data: data.map(item => item.humidity),
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                }]
            }
        });
    </script>
</body>
</html>