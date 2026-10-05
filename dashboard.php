<?php
require_once __DIR__ . '/config/sesiones.php';
requerirLoginRaiz();
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/recomendaciones.php';

$idUsuario = $_SESSION['id_usuario'];

// Lista de cultivos del usuario (para el selector)
$stmt = $pdo->prepare('SELECT * FROM cultivos WHERE id_usuario = :id ORDER BY nombre');
$stmt->execute(['id' => $idUsuario]);
$cultivos = $stmt->fetchAll();

// Cultivo activo del usuario
$stmt = $pdo->prepare('SELECT cultivo_activo_id FROM usuarios WHERE id_usuario = :id');
$stmt->execute(['id' => $idUsuario]);
$cultivoActivoId = $stmt->fetchColumn();

$cultivoActivo = null;
if ($cultivoActivoId) {
    $stmt = $pdo->prepare('SELECT * FROM cultivos WHERE id_cultivo = :id AND id_usuario = :uid');
    $stmt->execute(['id' => $cultivoActivoId, 'uid' => $idUsuario]);
    $cultivoActivo = $stmt->fetch() ?: null;
}

// Última medición del cultivo activo
$ultimaMedicion = null;
$resultado = null;
$sensorConectado = false;

if ($cultivoActivo) {
    $stmt = $pdo->prepare(
        'SELECT * FROM mediciones WHERE id_cultivo = :id ORDER BY fecha DESC LIMIT 1'
    );
    $stmt->execute(['id' => $cultivoActivo['id_cultivo']]);
    $ultimaMedicion = $stmt->fetch() ?: null;

    if ($ultimaMedicion) {
        $resultado = generarRecomendaciones($ultimaMedicion, $cultivoActivo);
        
        // Verificamos si la medición es reciente (menor a 10 minutos para considerarlo "En vivo")
        $tiempoUltima = strtotime($ultimaMedicion['fecha']);
        if ((time() - $tiempoUltima) < 600) { 
            $sensorConectado = true;
        }
    }
}

// Función auxiliar para detectar si un valor está fuera de rango y aplicar diseño de alerta
function obtenerClaseAlertaTarjeta($valor, $min, $max) {
    if ($valor === null) return '';
    if ($valor < $min || $valor > $max) {
        return 'border border-warning bg-warning bg-opacity-10'; 
    }
    return '';
}

$tituloPagina = 'Panel de Control';
$paginaActual = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="app-shell">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1 class="mb-0">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?> 🌱</h1>
                    
                    <!-- Indicador dinámico de estado de los sensores -->
                    <?php if ($sensorConectado): ?>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.8rem;">
                            <span class="spinner-grow spinner-grow-sm text-success me-1" role="status"></span> 
                            Sensores Conectados (En vivo)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.8rem;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                            Esperando conexión de hardware...
                        </span>
                    <?php endif; ?>
                </div>
                <div class="fecha-hora text-muted small mt-1" id="fecha-hora-actual"></div>
            </div>

            <form id="form-cultivo-activo" class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0 fw-semibold">Cultivo activo:</label>
                <select name="id_cultivo" id="select-cultivo" class="form-select form-select-sm shadow-sm" style="min-width:180px;">
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($cultivos as $c): ?>
                        <option value="<?= $c['id_cultivo'] ?>" <?= ($cultivoActivo && $cultivoActivo['id_cultivo'] == $c['id_cultivo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (empty($cultivos)): ?>
            <div class="alert alert-warning shadow-sm border-0">
                <i class="bi bi-info-circle me-1"></i> Todavía no tienes cultivos registrados.
                <a href="cultivos.php" class="fw-semibold text-success">Agrega tu primer cultivo aquí.</a>
            </div>
        <?php elseif (!$cultivoActivo): ?>
            <div class="alert alert-info shadow-sm border-0">
                <i class="bi bi-info-circle me-1"></i> Selecciona un cultivo activo arriba para comenzar a ver sus mediciones.
            </div>
        <?php elseif (!$ultimaMedicion): ?>
            <div class="alert alert-info shadow-sm border-0">
                <i class="bi bi-cpu me-1"></i> Aún no hay mediciones registradas para <strong><?= htmlspecialchars($cultivoActivo['nombre']) ?></strong>. 
                <span class="d-block small text-muted mt-1">El indicador cambiará a verde en cuanto tu hardware comience a enviar datos.</span>
            </div>
        <?php else: ?>

            <?php 
                $claseTemp = obtenerClaseAlertaTarjeta($ultimaMedicion['temperatura'], $cultivoActivo['temperatura_min'], $cultivoActivo['temperatura_max']);
                $claseAire = obtenerClaseAlertaTarjeta($ultimaMedicion['humedad_aire'], $cultivoActivo['humedad_aire_min'], $cultivoActivo['humedad_aire_max']);
                $claseSuelo = obtenerClaseAlertaTarjeta($ultimaMedicion['humedad_suelo'], $cultivoActivo['humedad_suelo_min'], $cultivoActivo['humedad_suelo_max']);
            ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card-agrisense metric-card shadow-sm <?= $claseTemp ?: 'border-0' ?>">
                        <div class="icon-circle icon-temp"><i class="bi bi-thermometer-half"></i></div>
                        <div class="valor" id="valor-temperatura"><?= number_format($ultimaMedicion['temperatura'], 1) ?>°C</div>
                        <div class="etiqueta">Temperatura</div>
                        <?php if ($claseTemp): ?>
                            <div class="text-warning small fw-bold mt-1"><i class="bi bi-exclamation-circle"></i> Fuera de rango</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-agrisense metric-card shadow-sm <?= $claseAire ?: 'border-0' ?>">
                        <div class="icon-circle icon-air"><i class="bi bi-wind"></i></div>
                        <div class="valor" id="valor-humedad-aire"><?= number_format($ultimaMedicion['humedad_aire'], 1) ?>%</div>
                        <div class="etiqueta">Humedad del aire</div>
                        <?php if ($claseAire): ?>
                            <div class="text-warning small fw-bold mt-1"><i class="bi bi-exclamation-circle"></i> Fuera de rango</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-agrisense metric-card shadow-sm <?= $claseSuelo ?: 'border-0' ?>">
                        <div class="icon-circle icon-soil"><i class="bi bi-moisture"></i></div>
                        <div class="valor" id="valor-humedad-suelo"><?= number_format($ultimaMedicion['humedad_suelo'], 0) ?></div>
                        <div class="etiqueta">Humedad del suelo</div>
                        <?php if ($claseSuelo): ?>
                            <div class="text-warning small fw-bold mt-1"><i class="bi bi-exclamation-circle"></i> Fuera de rango</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="card-agrisense shadow-sm border-0 h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">
                                    <i class="bi bi-shield-check text-success me-1"></i> Estado del cultivo: <span class="text-success"><?= htmlspecialchars($cultivoActivo['nombre']) ?></span>
                                </h5>
                            </div>
                            
                            <div>
                                <?php 
                                    $estadoActual = $resultado['estado'] ?? 'Óptimo';
                                    $claseBadge = 'badge-optimo';
                                    $iconoEstado = 'bi-check-circle-fill';
                                    
                                    if ($estadoActual === 'Atención') {
                                        $claseBadge = 'badge-atencion';
                                        $iconoEstado = 'bi-exclamation-triangle-fill';
                                    } elseif ($estadoActual === 'Crítico') {
                                        $claseBadge = 'badge-critico';
                                        $iconoEstado = 'bi-x-octagon-fill';
                                    }
                                ?>
                                <span id="badge-estado" class="badge-estado px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-1 <?= $claseBadge ?>">
                                    <i class="bi <?= $iconoEstado ?>"></i> 
                                    <span><?= htmlspecialchars($estadoActual) ?></span>
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <span class="text-muted small fw-bold text-uppercase">
                                <i class="bi bi-robot me-1 text-primary"></i> Recomendaciones del sistema:
                            </span>
                        </div>
                        
                        <ul id="recomendaciones-lista" class="list-unstyled mb-0 mt-2">
                            <?php foreach ($resultado['mensajes'] as $m): ?>
                                <li class="d-flex align-items-start mb-2 bg-light p-2 rounded-3 border-start border-4 border-success small">
                                    <i class="bi bi-info-circle-fill text-success me-2 mt-0.5"></i>
                                    <span class="text-secondary"><?= htmlspecialchars($m) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card-agrisense shadow-sm border-0 h-100 p-4">
                        <h5 class="mb-3 fw-bold text-dark"><i class="bi bi-sliders text-success me-1"></i> Valores ideales</h5>
                        <table class="table table-sm mb-0 align-middle">
                            <tbody>
                                <tr><td class="text-muted">Temperatura</td><td class="text-end fw-semibold"><?= $cultivoActivo['temperatura_min'] ?>°C – <?= $cultivoActivo['temperatura_max'] ?>°C</td></tr>
                                <tr><td class="text-muted">Humedad del aire</td><td class="text-end fw-semibold"><?= $cultivoActivo['humedad_aire_min'] ?>% – <?= $cultivoActivo['humedad_aire_max'] ?>%</td></tr>
                                <tr><td class="text-muted">Humedad del suelo</td><td class="text-end fw-semibold"><?= $cultivoActivo['humedad_suelo_min'] ?> – <?= $cultivoActivo['humedad_suelo_max'] ?></td></tr>
                            </tbody>
                        </table>
                        <hr class="text-muted opacity-25 my-3">
                        <p class="small text-muted mb-0">
                            <i class="bi bi-clock-history me-1"></i> Última actualización: <span id="ultima-actualizacion" class="fw-semibold text-dark"><?= date('d/m/Y H:i:s', strtotime($ultimaMedicion['fecha'])) ?></span>
                        </p>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/dashboard.js"></script>