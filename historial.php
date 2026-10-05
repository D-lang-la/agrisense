<?php
// historial.php - Sección de Historial y Análisis con Gráficas
$paginaActual = 'historial';
include 'config/conexion.php';
// Aquí puedes mantener tu lógica de sesión si la usas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriSense - Historial y Análisis</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Chart.js para las gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Tus estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="d-flex">
        <!-- Incluir tu sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Contenido Principal -->
        <div class="flex-grow-1 p-4" style="background-color: #f4f6f9; min-height: 100vh;">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="fw-bold text-success"><i class="bi bi-clock-history"></i> Historial y Análisis del Cultivo</h2>
                        <p class="text-muted">Visualiza el comportamiento histórico de los sensores en tiempo real.</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fecha:</label>
                                <input type="date" name="fecha" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cultivo:</label>
                                <select name="cultivo" class="form-select">
                                    <option value="4">Chile (Activo)</option>
                                    <option value="tomate">Tomate</option>
                                    <option value="lechuga">Lechuga</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success me-2"><i class="bi bi-search"></i> Filtrar</button>
                                <a href="historial.php" class="btn btn-outline-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contenedor de Gráficas de Líneas -->
                <div class="row">
                    <!-- Gráfica de Temperatura -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold text-danger">
                                <i class="bi bi-thermometer-half"></i> Temperatura (°C)
                            </div>
                            <div class="card-body">
                                <canvas id="graficaTemperatura"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfica de Humedad del Aire -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold text-info">
                                <i class="bi bi-cloud-rain"></i> Humedad del Aire (%)
                            </div>
                            <div class="card-body">
                                <canvas id="graficaHumedadAire"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfica de Humedad del Suelo -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white fw-bold text-warning">
                                <i class="bi bi-water"></i> Humedad del Suelo
                            </div>
                            <div class="card-body">
                                <canvas id="graficaHumedadSuelo"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

   <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Petición AJAX al nuevo archivo que consulta las mediciones en la BD
        fetch('ajax/obtener_historial.php')
            .then(response => response.json())
            .then(response => {
                if (response.success && response.data.length > 0) {
                    
                    // Extraemos los datos reales enviados por PHP
                    const labels = response.data.map(item => item.fecha);
                    const temperaturas = response.data.map(item => item.temperatura);
                    const humedadAire = response.data.map(item => item.humedad_aire);
                    const humedadSuelo = response.data.map(item => item.humedad_suelo);

                    // 1. Gráfica de Temperatura
                    new Chart(document.getElementById('graficaTemperatura'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Temperatura (°C)',
                                data: temperaturas,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3
                            }]
                        }
                    });

                    // 2. Gráfica de Humedad del Aire
                    new Chart(document.getElementById('graficaHumedadAire'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Humedad Aire (%)',
                                data: humedadAire,
                                borderColor: '#0dcaf0',
                                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3
                            }]
                        }
                    });

                    // 3. Gráfica de Humedad del Suelo
                    new Chart(document.getElementById('graficaHumedadSuelo'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Humedad Suelo',
                                data: humedadSuelo,
                                borderColor: '#ffc107',
                                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3
                            }]
                        }
                    });

                } else {
                    console.log("No hay suficientes mediciones registradas para este cultivo todavía.");
                }
            })
            .catch(error => console.error('Error al obtener el historial:', error));
    });
</script>
</body>
</html>