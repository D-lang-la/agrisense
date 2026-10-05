<?php
/**
 * AgriSense - AJAX: cultivos_crud.php
 * Maneja crear, editar y eliminar cultivos del usuario en sesión.
 * Acción esperada en $_POST['accion']: 'guardar' | 'eliminar'
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/sesiones.php';
require_once __DIR__ . '/../config/conexion.php';

if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$accion    = $_POST['accion'] ?? '';

if (!validarCSRF($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['error' => 'Token de seguridad inválido. Recarga la página.']);
    exit;
}

function numeroValido($valor): bool
{
    return $valor !== '' && $valor !== null && is_numeric($valor);
}

if ($accion === 'guardar') {
    $idCultivo = $_POST['id_cultivo'] ?? '';
    $nombre    = trim($_POST['nombre'] ?? '');
    
    // 1. CAPTURAR Y VALIDAR LA CATEGORÍA (Por defecto 'sol' si viene vacía o inválida)
    $categoria = $_POST['categoria'] ?? 'sol';
    if (!in_array($categoria, ['sol', 'agua'])) {
        $categoria = 'sol';
    }

    $tMin  = $_POST['temperatura_min'] ?? '';
    $tMax  = $_POST['temperatura_max'] ?? '';
    $haMin = $_POST['humedad_aire_min'] ?? '';
    $haMax = $_POST['humedad_aire_max'] ?? '';
    $hsMin = $_POST['humedad_suelo_min'] ?? '';
    $hsMax = $_POST['humedad_suelo_max'] ?? '';

    $errores = [];
    if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
    foreach (['temperatura_min' => $tMin, 'temperatura_max' => $tMax, 'humedad_aire_min' => $haMin,
              'humedad_aire_max' => $haMax, 'humedad_suelo_min' => $hsMin, 'humedad_suelo_max' => $hsMax] as $campo => $valor) {
        if (!numeroValido($valor)) $errores[] = "El campo {$campo} debe ser numérico.";
    }

    if (empty($errores)) {
        if ((float) $tMin >= (float) $tMax) $errores[] = 'La temperatura mínima debe ser menor que la máxima.';
        if ((float) $haMin >= (float) $haMax) $errores[] = 'La humedad de aire mínima debe ser menor que la máxima.';
        if ((float) $hsMin >= (float) $hsMax) $errores[] = 'La humedad de suelo mínima debe ser menor que la máxima.';
    }

    if (!empty($errores)) {
        echo json_encode(['error' => implode(' ', $errores)]);
        exit;
    }

    if ($idCultivo) {
        // Verificar pertenencia
        $stmt = $pdo->prepare('SELECT id_cultivo FROM cultivos WHERE id_cultivo = :id AND id_usuario = :uid');
        $stmt->execute(['id' => $idCultivo, 'uid' => $idUsuario]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso sobre ese cultivo.']);
            exit;
        }

        // 2. ACTUALIZAR INCLUYENDO LA CATEGORÍA
        $stmt = $pdo->prepare(
            'UPDATE cultivos SET nombre = :nombre, categoria = :categoria, temperatura_min = :tmin, temperatura_max = :tmax,
             humedad_aire_min = :hamin, humedad_aire_max = :hamax,
             humedad_suelo_min = :hsmin, humedad_suelo_max = :hsmax
             WHERE id_cultivo = :id AND id_usuario = :uid'
        );
        $stmt->execute([
            'nombre' => $nombre, 'categoria' => $categoria, 'tmin' => $tMin, 'tmax' => $tMax,
            'hamin' => $haMin, 'hamax' => $haMax, 'hsmin' => $hsMin, 'hsmax' => $hsMax,
            'id' => $idCultivo, 'uid' => $idUsuario,
        ]);
        echo json_encode(['ok' => true, 'mensaje' => 'Cultivo actualizado correctamente.']);
    } else {
        // 3. INSERTAR INCLUYENDO LA CATEGORÍA
        $stmt = $pdo->prepare(
            'INSERT INTO cultivos (id_usuario, nombre, categoria, temperatura_min, temperatura_max,
             humedad_aire_min, humedad_aire_max, humedad_suelo_min, humedad_suelo_max)
             VALUES (:uid, :nombre, :categoria, :tmin, :tmax, :hamin, :hamax, :hsmin, :hsmax)'
        );
        $stmt->execute([
            'uid' => $idUsuario, 'nombre' => $nombre, 'categoria' => $categoria, 'tmin' => $tMin, 'tmax' => $tMax,
            'hamin' => $haMin, 'hamax' => $haMax, 'hsmin' => $hsMin, 'hsmax' => $hsMax,
        ]);
        echo json_encode(['ok' => true, 'mensaje' => 'Cultivo agregado correctamente.']);
    }
    exit;
}

if ($accion === 'eliminar') {
    $idCultivo = $_POST['id_cultivo']--; // Corrección leve de sintaxis asegurando conservar lo original o tu lógica limpia
    // Nota: Mantenemos tu consulta original de eliminar:
    $idCultivo = $_POST['id_cultivo'] ?? '';

    $stmt = $pdo->prepare('SELECT id_cultivo FROM cultivos WHERE id_cultivo = :id AND id_usuario = :uid');
    $stmt->execute(['id' => $idCultivo, 'uid' => $idUsuario]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso sobre ese cultivo.']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM cultivos WHERE id_cultivo = :id AND id_usuario = :uid');
    $stmt->execute(['id' => $idCultivo, 'uid' => $idUsuario]);

    echo json_encode(['ok' => true, 'mensaje' => 'Cultivo eliminado.']);
    exit;
}

echo json_encode(['error' => 'Acción no reconocida.']);
