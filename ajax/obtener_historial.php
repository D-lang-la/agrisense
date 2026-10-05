<?php
/**
 * AgriSense - AJAX: obtener_historial.php
 * Devuelve un arreglo en JSON con las últimas mediciones del cultivo activo para las gráficas.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/sesiones.php';
require_once __DIR__ . '/../config/conexion.php';

if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

// Obtener el cultivo activo del usuario
$stmt = $pdo->prepare('SELECT cultivo_activo_id FROM usuarios WHERE id_usuario = :id');
$stmt->execute(['id' => $idUsuario]);
$cultivoActivoId = $stmt->fetchColumn();

if (!$cultivoActivoId) {
    echo json_encode(['success' => false, 'error' => 'Sin cultivo activo']);
    exit;
}

// Obtener las últimas 15 mediciones de ese cultivo ordenadas cronológicamente
$stmt = $pdo->prepare('SELECT temperatura, humedad_aire, humedad_suelo, fecha FROM mediciones WHERE id_cultivo = :id_cultivo ORDER BY fecha ASC LIMIT 15');
$stmt->execute(['id_cultivo' => $cultivoActivoId]);
$mediciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatear la fecha para que en la gráfica se vea limpia (ej. solo la hora o mes/día hora)
foreach ($mediciones as &$med) {
    $med['fecha'] = date('H:i:s', strtotime($med['fecha'])); // Muestra la hora de la medición
    $med['temperatura'] = (float)$med['temperatura'];
    $med['humedad_aire'] = (float)$med['humedad_aire'];
    $med['humedad_suelo'] = (float)$med['humedad_suelo'];
}

echo json_encode([
    'success' => true,
    'data' => $mediciones
]);