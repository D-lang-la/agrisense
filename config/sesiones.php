<?php
/**
 * AgriSense - Manejo de sesiones
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario ha iniciado sesión
 */
function estaLogueado(): bool
{
    return isset($_SESSION['id_usuario']);
}

/**
 * Obliga a tener sesión iniciada, si no redirige al login
 */
function requerirLogin(): void
{
    if (!estaLogueado()) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Obliga a tener sesión iniciada para archivos en la raíz del proyecto
 */
function requerirLoginRaiz(): void
{
    if (!estaLogueado()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Genera / valida un token CSRF simple
 */
function tokenCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCSRF(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}
