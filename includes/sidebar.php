<?php
/**
 * AgriSense - Sidebar de navegación
 * Requiere sesión iniciada. Usa $paginaActual para resaltar el enlace activo.
 */
$paginaActual = $paginaActual ?? '';

function enlaceActivo(string $pagina, string $actual): string
{
    return $pagina === $actual ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="logo-box">
        <img src="assets/img/logo.png" alt="Logo AgriSense">
        <span>AgriSense</span>
    </div>

    <nav class="flex-grow-1">
        <a href="dashboard.php" class="<?= enlaceActivo('dashboard', $paginaActual) ?>">
            <i class="bi bi-speedometer2"></i> Panel de Control
        </a>
        <a href="cultivos.php" class="<?= enlaceActivo('cultivos', $paginaActual) ?>">
            <i class="bi bi-flower1"></i> Cultivos
        </a>
        <a href="historial.php" class="<?= enlaceActivo('historial', $paginaActual) ?>">
            <i class="bi bi-clock-history"></i> Historial
        </a>
        <a href="perfil.php" class="<?= enlaceActivo('perfil', $paginaActual) ?>">
            <i class="bi bi-person-circle"></i> Perfil
        </a>
    </nav>

    <div class="p-3 mt-auto">
        <a href="logout.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm text-white" style="border-radius: 8px; font-weight: 500; text-decoration: none;" onclick="return confirm('¿Seguro que deseas cerrar sesión?');">
            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
    </div>
</aside>
