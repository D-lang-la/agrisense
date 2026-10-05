<?php
/**
 * AgriSense - Motor de recomendaciones
 * Compara la última medición contra los valores ideales del cultivo
 * y genera un estado + mensajes utilizando condiciones IF.
 * NO utiliza inteligencia artificial.
 */

function generarRecomendaciones(array $medicion, array $cultivo): array
{
    $mensajes = [];
    $critico  = false;
    $atencion = false;

    $temperatura   = (float) $medicion['temperatura'];
    $humedadAire   = (float) $medicion['humedad_aire'];
    $humedadSuelo  = (float) $medicion['humedad_suelo'];

    $tempMin  = (float) $cultivo['temperatura_min'];
    $tempMax  = (float) $cultivo['temperatura_max'];
    $haireMin = (float) $cultivo['humedad_aire_min'];
    $haireMax = (float) $cultivo['humedad_aire_max'];
    $hsueloMin = (float) $cultivo['humedad_suelo_min'];
    $hsueloMax = (float) $cultivo['humedad_suelo_max'];

    // --- Temperatura ---
    if ($temperatura > $tempMax) {
        $diferencia = $temperatura - $tempMax;
        $mensajes[] = 'La temperatura es superior a la recomendada.';
        $mensajes[] = ($diferencia >= 5)
            ? 'Se recomienda colocar una malla sombra de inmediato.'
            : 'Se recomienda colocar una malla sombra.';
        $atencion = true;
        if ($diferencia >= 5) $critico = true;
    } elseif ($temperatura < $tempMin) {
        $mensajes[] = 'La temperatura es inferior a la recomendada.';
        $mensajes[] = 'Se recomienda proteger el cultivo del frío (cobertor o invernadero).';
        $atencion = true;
    }

    // --- Humedad del suelo ---
    if ($humedadSuelo < $hsueloMin) {
        $mensajes[] = 'La humedad del suelo es baja.';
        $mensajes[] = 'Se recomienda regar la planta.';
        $atencion = true;
        if ($humedadSuelo < ($hsueloMin * 0.7)) $critico = true;
    } elseif ($humedadSuelo > $hsueloMax) {
        $mensajes[] = 'La humedad del suelo es alta.';
        $mensajes[] = 'Se recomienda suspender el riego para evitar encharcamiento.';
        $atencion = true;
    }

    // --- Humedad del aire ---
    if ($humedadAire < $haireMin) {
        $mensajes[] = 'La humedad del aire es baja.';
        $mensajes[] = 'Supervisar el cultivo durante las próximas horas.';
        $atencion = true;
    } elseif ($humedadAire > $haireMax) {
        $mensajes[] = 'La humedad del aire es alta.';
        $mensajes[] = 'Verificar ventilación para prevenir hongos.';
        $atencion = true;
    }

    // --- Estado final ---
    if ($critico) {
        $estado = 'Crítico';
    } elseif ($atencion) {
        $estado = 'Atención';
    } else {
        $estado = 'Óptimo';
        $mensajes[] = 'Todos los valores se encuentran dentro del rango ideal para este cultivo.';
    }

    return [
        'estado'   => $estado,
        'mensajes' => $mensajes,
    ];
}
