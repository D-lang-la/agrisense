<?php
require_once __DIR__ . '/config/sesiones.php';

if (estaLogueado()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
