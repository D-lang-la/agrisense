<?php
/**
 * AgriSense - Header común
 * Espera opcionalmente la variable $tituloPagina definida antes del include.
 */
$tituloPagina = $tituloPagina ?? 'AgriSense';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($tituloPagina) ?> | AgriSense</title>
<link rel="icon" href="assets/img/logo.png" type="image/png">

<!-- Google Fonts: Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- Estilos propios de AgriSense -->
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
