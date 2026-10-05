/**
 * AgriSense - Dashboard: actualización automática vía AJAX
 */
document.addEventListener('DOMContentLoaded', () => {
    const selectCultivo = document.getElementById('select-cultivo');

    // Cambiar cultivo activo
    if (selectCultivo) {
        selectCultivo.addEventListener('change', () => {
            const idCultivo = selectCultivo.value;
            if (!idCultivo) return;

            const datos = new FormData();
            datos.append('id_cultivo', idCultivo);

            fetch('ajax/actualizar_dashboard.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(() => window.location.reload())
                .catch(err => console.error('Error al actualizar cultivo activo:', err));
        });
    }

    // Refrescar métricas cada 8 segundos
    function refrescarDashboard() {
        fetch('ajax/obtener_datos.php')
            .then(res => res.json())
            .then(data => {
                if (data.error || data.sin_cultivo || data.sin_medicion) return;

                const elTemp = document.getElementById('valor-temperatura');
                const elAire = document.getElementById('valor-humedad-aire');
                const elSuelo = document.getElementById('valor-humedad-suelo');
                const elBadge = document.getElementById('badge-estado');
                const elLista = document.getElementById('recomendaciones-lista');
                const elFecha = document.getElementById('ultima-actualizacion');

                if (elTemp) elTemp.textContent = data.temperatura + '°C';
                if (elAire) elAire.textContent = data.humedad_aire + '%';
                if (elSuelo) elSuelo.textContent = data.humedad_suelo;
                if (elFecha) elFecha.textContent = data.fecha;

                if (elBadge) {
                    elBadge.textContent = data.estado;
                    elBadge.className = 'badge-estado ' +
                        (data.estado === 'Óptimo' ? 'badge-optimo' : (data.estado === 'Atención' ? 'badge-atencion' : 'badge-critico'));
                }

                if (elLista) {
                    elLista.innerHTML = '';
                    data.mensajes.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        elLista.appendChild(li);
                    });
                }
            })
            .catch(err => console.error('Error al refrescar dashboard:', err));
    }

    if (document.getElementById('valor-temperatura')) {
        setInterval(refrescarDashboard, 8000);
    }
});
