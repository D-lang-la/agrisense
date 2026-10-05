<?php
/**
 * AgriSense - Mis Cultivos
 */
$paginaActual = 'cultivos';
require_once 'config/sesiones.php';
require_once 'config/conexion.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

// Consultar los cultivos del usuario en orden
$stmt = $pdo->prepare('SELECT * FROM cultivos WHERE id_usuario = :id_usuario ORDER BY id_cultivo DESC');
$stmt->execute(['id_usuario' => $idUsuario]);
$todosCultivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separar automáticamente en arreglos de Sol y Agua
$cultivosSol = [];
$cultivosAgua = [];
foreach ($todosCultivos as $cultivo) {
    if (isset($cultivo['categoria']) && $cultivo['categoria'] === 'agua') {
        $cultivosAgua[] = $cultivo;
    } else {
        $cultivosSol[] = $cultivo; // Por defecto o 'sol'
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriSense - Mis Cultivos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Tus estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <div class="d-flex">
        <!-- Incluir tu sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Contenido Principal -->
        <div class="flex-grow-1 p-4 p-md-5" style="min-height: 100vh;">
            <div class="container-fluid px-0">
                
                <!-- Encabezado y Botón Agregar -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h2 class="fw-bold text-success mb-1"><i class="bi bi-flower1"></i> Mis Cultivos</h2>
                        <p class="text-muted mb-0">Parámetros ideales configurados para el monitoreo inteligente.</p>
                    </div>
                    <button class="btn btn-success shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalAgregarCultivo">
                        <i class="bi bi-plus-lg me-1"></i> Agregar cultivo
                    </button>
                </div>

                <!-- Barra de búsqueda -->
                <div class="row mb-4">
                    <div class="col-md-5 col-lg-4">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="buscador-cultivos" class="form-control border-start-0 ps-0" placeholder="Buscar cultivo por nombre...">
                        </div>
                    </div>
                </div>

                <!-- Secciones en dos columnas (Sol vs Agua) -->
                <div class="row g-4" id="contenedor-cultivos">
                    
                    <!-- COLUMNA 1: Mayor Exposición Solar -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 rounded-3 mb-4 border-top border-warning border-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="m-0 fw-bold text-warning"><i class="bi bi-sun-fill me-2"></i> Mayor Exposición Solar</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <?php if (!empty($cultivosSol)): ?>
                                        <?php foreach ($cultivosSol as $cultivo): ?>
                                            <div class="col-12 cultivo-item">
                                                <div class="card shadow-sm border-0 h-100 rounded-3 overflow-hidden border-start border-warning border-3">
                                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-seedling text-warning fs-5 me-2"></i>
                                                                <h5 class="card-title fw-bold text-dark mb-0">
                                                                    <?= htmlspecialchars($cultivo['nombre']) ?>
                                                                </h5>
                                                            </div>
                                                            <div class="bg-light p-2 rounded-2 small">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted"><i class="bi bi-thermometer-half text-danger me-1"></i> Temp:</span>
                                                                    <span class="fw-semibold">
                                                                        <?= $cultivo['temperatura_min'] ?? '0' ?>°C - <?= $cultivo['temperatura_max'] ?? '0' ?>°C
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted"><i class="bi bi-cloud-rain text-info me-1"></i> Hum. Aire:</span>
                                                                    <span class="fw-semibold"><?= $cultivo['humedad_aire_min'] ?? '0' ?>% - <?= $cultivo['humedad_aire_max'] ?? '0' ?>%</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="text-muted"><i class="bi bi-water text-primary me-1"></i> Hum. Suelo:</span>
                                                                    <span class="fw-semibold"><?= $cultivo['humedad_suelo_min'] ?? '0' ?> - <?= $cultivo['humedad_suelo_max'] ?? '0' ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center py-4 text-muted small fst-italic">
                                            No hay cultivos registrados en esta categoría.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: Mayor Demanda de Agua / Riego -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 rounded-3 mb-4 border-top border-primary border-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="m-0 fw-bold text-primary"><i class="bi bi-droplet-fill me-2"></i> Mayor Demanda de Agua</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <?php if (!empty($cultivosAgua)): ?>
                                        <?php foreach ($cultivosAgua as $cultivo): ?>
                                            <div class="col-12 cultivo-item">
                                                <div class="card shadow-sm border-0 h-100 rounded-3 overflow-hidden border-start border-primary border-3">
                                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-seedling text-primary fs-5 me-2"></i>
                                                                <h5 class="card-title fw-bold text-dark mb-0">
                                                                    <?= htmlspecialchars($cultivo['nombre']) ?>
                                                                </h5>
                                                            </div>
                                                            <div class="bg-light p-2 rounded-2 small">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted"><i class="bi bi-thermometer-half text-danger me-1"></i> Temp:</span>
                                                                    <span class="fw-semibold">
                                                                        <?= $cultivo['temperatura_min'] ?? '0' ?>°C - <?= $cultivo['temperatura_max'] ?? '0' ?>°C
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted"><i class="bi bi-cloud-rain text-info me-1"></i> Hum. Aire:</span>
                                                                    <span class="fw-semibold"><?= $cultivo['humedad_aire_min'] ?? '0' ?>% - <?= $cultivo['humedad_aire_max'] ?? '0' ?>%</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="text-muted"><i class="bi bi-water text-primary me-1"></i> Hum. Suelo:</span>
                                                                    <span class="fw-semibold"><?= $cultivo['humedad_suelo_min'] ?? '0' ?> - <?= $cultivo['humedad_suelo_max'] ?? '0' ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center py-4 text-muted small fst-italic">
                                            No hay cultivos registrados en esta categoría.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Script para la búsqueda en tiempo real -->
    <script>
        document.getElementById('buscador-cultivos').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let tarjetas = document.querySelectorAll('.cultivo-item');

            tarjetas.forEach(function(tarjeta) {
                let texto = tarjeta.textContent.toLowerCase();
                if (texto.includes(filtro)) {
                    tarjeta.style.display = '';
                } else {
                    tarjeta.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

   <!-- Modal Agregar Cultivo -->
<div class="modal fade" id="modalAgregarCultivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="form-cultivo" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="accion" value="guardar"> <!-- O prueba con 'agregar' si con 'crear' te da detalle -->

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-flower1 me-1"></i> Agregar nuevo cultivo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <!-- Selector de plantilla rápida de cultivos -->
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-semibold text-success small mb-1">
                            <i class="bi bi-magic me-1"></i> Seleccionar cultivo predefinido (Opcional)
                        </label>
                        <select id="select-plantilla" class="form-select form-select-sm">
                            <option value="">-- Elige un cultivo para autocompletar --</option>
                            <option value="maiz">Maíz</option>
                            <option value="frijol">Frijol</option>
                            <option value="tomate">Tomate</option>
                            <option value="lechuga">Lechuga</option>
                            <option value="cebolla">Cebolla</option>
                            <option value="papa">Papa</option>
                            <option value="chile">Chile</option>
                            <option value="arroz">Arroz</option>
                        </select>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">O llena los datos manualmente aquí abajo si prefieres.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Nombre del cultivo</label>
                        <input type="text" id="input-nombre" name="nombre" class="form-control" placeholder="Ej. Tomate, Maíz..." required>
                    </div>

                    <!-- NUEVO: Selector de Categoría (Sol o Agua) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Categoría Principal (Requerimiento)</label>
                        <select id="select-categoria" name="categoria" class="form-select" required>
                            <option value="sol">☀️ Mayor Exposición Solar</option>
                            <option value="agua">💧 Mayor Demanda de Agua / Riego</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold text-secondary small">Temp. mínima (°C)</label>
                            <input type="number" step="0.1" id="input-temp-min" name="temperatura_min" class="form-control" placeholder="18.0" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold text-secondary small">Temp. máxima (°C)</label>
                            <input type="number" step="0.1" id="input-temp-max" name="temperatura_max" class="form-control" placeholder="25.0" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold text-secondary small">Hum. aire mín. (%)</label>
                            <input type="number" step="0.1" id="input-ham-min" name="humedad_aire_min" class="form-control" placeholder="60" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold text-secondary small">Hum. aire máx. (%)</label>
                            <input type="number" step="0.1" id="input-ham-max" name="humedad_aire_max" class="form-control" placeholder="80" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold text-secondary small">Hum. suelo mín.</label>
                            <input type="number" step="0.1" id="input-hsu-min" name="humedad_suelo_min" class="form-control" placeholder="40" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold text-secondary small">Hum. suelo máx.</label>
                            <input type="number" step="0.1" id="input-hsu-max" name="humedad_suelo_max" class="form-control" placeholder="70" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">Guardar cultivo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para autocompletar con los datos agronómicos y categoría de los cultivos -->
<script>
    const baseCultivos = {
        maiz: { nombre: "Maíz", categoria: "sol", tmin: 21, tmax: 30, hamin: 60, hamax: 80, hsmin: 50, hsmax: 75 },
        frijol: { nombre: "Frijol", categoria: "sol", tmin: 18, tmax: 27, hamin: 65, hamax: 85, hsmin: 55, hsmax: 80 },
        tomate: { nombre: "Tomate", categoria: "sol", tmin: 18, tmax: 26, hamin: 60, hamax: 80, hsmin: 60, hsmax: 85 },
        lechuga: { nombre: "Lechuga", categoria: "agua", tmin: 15, tmax: 22, hamin: 70, hamax: 90, hsmin: 65, hsmax: 85 },
        cebolla: { nombre: "Cebolla", categoria: "agua", tmin: 13, tmax: 24, hamin: 60, hamax: 75, hsmin: 50, hsmax: 70 },
        papa: { nombre: "Papa", categoria: "agua", tmin: 12, tmax: 20, hamin: 70, hamax: 85, hsmin: 60, hsmax: 80 },
        chile: { nombre: "Chile", categoria: "sol", tmin: 20, tmax: 30, hamin: 65, hamax: 85, hsmin: 55, hsmax: 75 },
        arroz: { nombre: "Arroz", categoria: "agua", tmin: 22, tmax: 32, hamin: 70, hamax: 90, hsmin: 70, hsmax: 95 }
    };

    document.getElementById('select-plantilla').addEventListener('change', function() {
        let seleccion = this.value;
        if (baseCultivos[seleccion]) {
            let datos = baseCultivos[seleccion];
            document.getElementById('input-nombre').value = datos.nombre;
            document.getElementById('select-categoria').value = datos.categoria; // Autocompleta sol o agua
            document.getElementById('input-temp-min').value = datos.tmin;
            document.getElementById('input-temp-max').value = datos.tmax;
            document.getElementById('input-ham-min').value = datos.hamin;
            document.getElementById('input-ham-max').value = datos.hamax;
            document.getElementById('input-hsu-min').value = datos.hsmin;
            document.getElementById('input-hsu-max').value = datos.hsmax;
        }
    });
</script>

<!-- Script para la búsqueda en tiempo real -->
<script>
    document.getElementById('buscador-cultivos').addEventListener('keyup', function() {
        let filtro = this.value.toLowerCase();
        let tarjetas = document.querySelectorAll('.cultivo-item');

        tarjetas.forEach(function(tarjeta) {
            let texto = tarjeta.textContent.toLowerCase();
            if (texto.includes(filtro)) {
                tarjeta.style.display = '';
            } else {
                tarjeta.style.display = 'none';
            }
        });
    });
</script>

<script>
    document.getElementById('form-cultivo').addEventListener('submit', function(e) {
        e.preventDefault(); // Evita que la página se vaya a la pantalla negra

        let formData = new FormData(this);

        // Envía los datos por detrás (AJAX)
        fetch('ajax/cultivos_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Si se guardó bien, recarga la página automáticamente para ver el cebollín
                location.reload(); 
            } else {
                alert('Error: ' + (data.error || 'No se pudo guardar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al guardar.');
        });
    });
</script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>