<?php
/**
 * AgriSense - AJAX: actualizar_dashboard.php
 * Actualiza el cultivo activo del usuario en sesión.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/sesiones.php';
require_once __DIR__ . '/../config/conexion.php';

if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idUsuario  = $_SESSION['id_usuario'];
$idCultivo  = $_POST['id_cultivo'] ?? null;

if (!$idCultivo) {
    echo json_encode(['error' => 'Cultivo no especificado']);
    exit;
}

// Verificar que el cultivo pertenece al usuario
$stmt = $pdo->prepare('SELECT id_cultivo FROM cultivos WHERE id_cultivo = :id AND id_usuario = :uid');
$stmt->execute(['id' => $idCultivo, 'uid' => $idUsuario]);

if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Ese cultivo no te pertenece']);
    exit;
}

$stmt = $pdo->prepare('UPDATE usuarios SET cultivo_activo_id = :id WHERE id_usuario = :uid');
$stmt->execute(['id' => $idCultivo, 'uid' => $idUsuario]);

echo json_encode(['ok' => true]);
