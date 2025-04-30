<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "monitor_ambiente");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Consulta os dados
$result = $conn->query("SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT 100");
$data = $result->fetch_all(MYSQLI_ASSOC);

// Calcula estatísticas
$temp_values = array_column($data, 'temperature');
$hum_values = array_column($data, 'humidity');
$stats = [
    'temp_avg' => round(array_sum($temp_values) / count($temp_values), 1),
    'temp_max' => round(max($temp_values), 1),
    'temp_min' => round(min($temp_values), 1),
    'hum_avg' => round(array_sum($hum_values) / count($hum_values), 1),
    'hum_max' => round(max($hum_values), 1),
    'hum_min' => round(min($hum_values), 1),
    'last_update' => $data[0]['reading_time']
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ambiental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
    <style>
        :root {
            --primary: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-temp {
            border-left: 4px solid var(--danger);
        }
        
        .card-humidity {
            border-left: 4px solid var(--primary);
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .data-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .last-update {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-temperature-low"></i> Dashboard Ambiental</h1>
                    <p class="mb-0">Monitoramento em tempo real de temperatura e umidade</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="last-update">
                        <i class="fas fa-sync-alt"></i> Atualizado: <?= date('d/m/Y H:i', strtotime($stats['last_update'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Cards de Estatísticas -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-temp">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title text-muted mb-2">Temperatura Média</h5>
                                <h2 class="stat-value text-danger"><?= $stats['temp_avg'] ?> <small>°C</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-thermometer-half stat-icon text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title text-muted mb-2">Temperatura Máxima</h5>
                                <h2 class="stat-value text-warning"><?= $stats['temp_max'] ?> <small>°C</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-temperature-high stat-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title text-muted mb-2">Temperatura Mínima</h5>
                                <h2 class="stat-value text-primary"><?= $stats['temp_min'] ?> <small>°C</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-temperature-low stat-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card card-humidity">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title text-muted mb-2">Umidade Média</h5>
                                <h2 class="stat-value text-primary"><?= $stats['hum_avg'] ?> <small>%</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-tint stat-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title text-muted mb-2">Umidade Máxima</h5>
                                <h2 class="stat-value text-success"><?= $stats['hum_max'] ?> <small>%</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-humidity stat-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title text-muted mb-2">Umidade Mínima</h5>
                                <h2 class="stat-value text-info"><?= $stats['hum_min'] ?> <small>%</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-humidity stat-icon text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="chart-container">
                    <h4><i class="fas fa-chart-line"></i> Variação de Temperatura</h4>
                    <canvas id="tempChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container">
                    <h4><i class="fas fa-chart-line"></i> Variação de Umidade</h4>
                    <canvas id="humChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabela de Dados -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="data-table">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-table"></i> Últimas Leituras</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data/Hora</th>
                                            <th>Dispositivo</th>
                                            <th>Temperatura (°C)</th>
                                            <th>Umidade (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach(array_slice($data, 0, 10) as $row): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($row['reading_time'])) ?></td>
                                            <td><?= $row['device_id'] ?></td>
                                            <td class="<?= $row['temperature'] > 30 ? 'text-danger fw-bold' : '' ?>">
                                                <?= $row['temperature'] ?>
                                            </td>
                                            <td class="<?= $row['humidity'] < 40 ? 'text-warning fw-bold' : '' ?>">
                                                <?= $row['humidity'] ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0 text-muted">
                Sistema de Monitoramento Ambiental &copy; <?= date('Y') ?> -
                Desenvolvido para seu ESP32
            </p>
        </div>
    </footer>

    <script>
        // Configuração dos gráficos
        const data = <?= json_encode($data) ?>;
        const timeFormat = 'DD/MM/YYYY HH:mm';
        
        // Gráfico de Temperatura
        new Chart(document.getElementById('tempChart'), {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Temperatura °C',
                    data: data.map(item => ({
                        x: moment(item.reading_time),
                        y: item.temperature
                    })),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            parser: timeFormat,
                            tooltipFormat: 'DD/MM/YY HH:mm',
                            displayFormats: {
                                hour: 'HH:mm'
                            }
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Temperatura (°C)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}°C`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Umidade
        new Chart(document.getElementById('humChart'), {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Umidade %',
                    data: data.map(item => ({
                        x: moment(item.reading_time),
                        y: item.humidity
                    })),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            parser: timeFormat,
                            tooltipFormat: 'DD/MM/YY HH:mm',
                            displayFormats: {
                                hour: 'HH:mm'
                            }
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Umidade (%)'
                        },
                        min: 0,
                        max: 100
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}%`;
                            }
                        }
                    }
                }
            }
        });

        // Atualização automática a cada 1 minuto
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>