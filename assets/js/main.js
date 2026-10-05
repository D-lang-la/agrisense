/**
 * AgriSense - JS general
 */
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function actualizarFechaHora() {
    const el = document.getElementById('fecha-hora-actual');
    if (!el) return;
    const ahora = new Date();
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    el.textContent = ahora.toLocaleDateString('es-ES', opciones);
}

setInterval(actualizarFechaHora, 1000);
document.addEventListener('DOMContentLoaded', actualizarFechaHora);
