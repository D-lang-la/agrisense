/**
 * AgriSense - Gestión de cultivos vía AJAX
 */
const modalCultivoEl = document.getElementById('modalCultivo');
const modalCultivo = modalCultivoEl ? new bootstrap.Modal(modalCultivoEl) : null;

function abrirModalNuevo() {
    document.getElementById('tituloModalCultivo').textContent = 'Agregar cultivo';
    document.getElementById('form-cultivo').reset();
    document.getElementById('id_cultivo').value = '';
    document.getElementById('error-cultivo').classList.add('d-none');
}

function abrirModalEditar(cultivo) {
    document.getElementById('tituloModalCultivo').textContent = 'Editar cultivo';
    document.getElementById('error-cultivo').classList.add('d-none');
    document.getElementById('id_cultivo').value = cultivo.id_cultivo;
    document.getElementById('nombre').value = cultivo.nombre;
    document.getElementById('temperatura_min').value = cultivo.temperatura_min;
    document.getElementById('temperatura_max').value = cultivo.temperatura_max;
    document.getElementById('humedad_aire_min').value = cultivo.humedad_aire_min;
    document.getElementById('humedad_aire_max').value = cultivo.humedad_aire_max;
    document.getElementById('humedad_suelo_min').value = cultivo.humedad_suelo_min;
    document.getElementById('humedad_suelo_max').value = cultivo.humedad_suelo_max;
    modalCultivo.show();
}

document.getElementById('form-cultivo')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const datos = new FormData(this);
    datos.append('accion', 'guardar');

    fetch('ajax/cultivos_crud.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                const errEl = document.getElementById('error-cultivo');
                errEl.textContent = data.error;
                errEl.classList.remove('d-none');
                return;
            }
            window.location.reload();
        })
        .catch(err => console.error('Error al guardar cultivo:', err));
});

function eliminarCultivo(id, nombre) {
    if (!confirm(`¿Eliminar el cultivo "${nombre}"? Esta acción no se puede deshacer.`)) return;

    const datos = new FormData();
    datos.append('accion', 'eliminar');
    datos.append('id_cultivo', id);
    datos.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

    fetch('ajax/cultivos_crud.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            window.location.reload();
        })
        .catch(err => console.error('Error al eliminar cultivo:', err));
}

// Buscador en vivo
document.getElementById('buscador-cultivos')?.addEventListener('input', function () {
    const filtro = this.value.trim().toLowerCase();
    document.querySelectorAll('#tabla-cultivos tbody tr[data-nombre]').forEach(fila => {
        fila.style.display = fila.dataset.nombre.includes(filtro) ? '' : 'none';
    });
});
