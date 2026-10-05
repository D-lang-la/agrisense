<?php
/**
 * AgriSense - AJAX: obtener_datos.php
 * Devuelve en JSON la última medición y recomendaciones del cultivo activo.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/sesiones.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/recomendaciones.php';

if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

$stmt = $pdo->prepare('SELECT cultivo_activo_id FROM usuarios WHERE id_usuario = :id');
$stmt->execute(['id' => $idUsuario]);
$cultivoActivoId = $stmt->fetchColumn();

if (!$cultivoActivoId) {
    echo json_encode(['sin_cultivo' => true]);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM cultivos WHERE id_cultivo = :id AND id_usuario = :uid');
$stmt->execute(['id' => $cultivoActivoId, 'uid' => $idUsuario]);
$cultivo = $stmt->fetch();

if (!$cultivo) {
    echo json_encode(['sin_cultivo' => true]);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM mediciones WHERE id_cultivo = :id ORDER BY fecha DESC LIMIT 1');
$stmt->execute(['id' => $cultivo['id_cultivo']]);
$medicion = $stmt->fetch();

if (!$medicion) {
    echo json_encode(['sin_medicion' => true]);
    exit;
}

$resultado = generarRecomendaciones($medicion, $cultivo);

// Guardar la recomendación generada en el historial (una vez por medición)
$stmt = $pdo->prepare('SELECT id_recomendacion FROM recomendaciones WHERE id_medicion = :id');
$stmt->execute(['id' => $medicion['id_medicion']]);
if (!$stmt->fetch()) {
    $stmtIns = $pdo->prepare(
        'INSERT INTO recomendaciones (id_medicion, estado, mensaje) VALUES (:id, :estado, :mensaje)'
    );
    $stmtIns->execute([
        'id'      => $medicion['id_medicion'],
        'estado'  => $resultado['estado'],
        'mensaje' => implode(' ', $resultado['mensajes']),
    ]);
}

echo json_encode([
    'cultivo'        => $cultivo['nombre'],
    'temperatura'    => number_format((float) $medicion['temperatura'], 1),
    'humedad_aire'   => number_format((float) $medicion['humedad_aire'], 1),
    'humedad_suelo'  => number_format((float) $medicion['humedad_suelo'], 0),
    'estado'         => $resultado['estado'],
    'mensajes'       => $resultado['mensajes'],
    'fecha'          => date('d/m/Y H:i:s', strtotime($medicion['fecha'])),
]);
