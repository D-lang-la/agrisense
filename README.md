# AgriSense 🌱

Sistema Web Inteligente para Monitoreo de Cultivos mediante Arduino.

> "Cultivos más saludables, cosechas más inteligentes."

## Arquitectura

```
Sensores → Arduino Uno → Python (pyserial) → MySQL (XAMPP) → Sistema Web PHP → Usuario
```

- El **Arduino** únicamente lee los sensores (DHT11 + humedad de suelo).
- **Python** lee el puerto serial y guarda las mediciones en MySQL.
- **PHP** solo consulta la base de datos, muestra la información y contiene
  toda la lógica del sistema (recomendaciones, historial, etc.).

## Requisitos

- XAMPP (Apache + MySQL + phpMyAdmin) con PHP 8.
- Python 3.9+ con `pyserial` y `mysql-connector-python`.
- Arduino Uno + sensor DHT11 + sensor de humedad de suelo.

## 1. Base de datos

1. Inicia Apache y MySQL desde XAMPP.
2. Abre phpMyAdmin (`http://localhost/phpmyadmin`).
3. Ve a la pestaña **Importar** y selecciona el archivo `database/agrisense.sql`.
   Esto creará la base de datos `agrisense` con todas sus tablas y un
   usuario de ejemplo:
   - **Usuario:** `admin`
   - **Contraseña:** `admin1234`

## 2. Sitio web (PHP)

1. Copia la carpeta `AgriSense/` dentro de `htdocs` de tu instalación de XAMPP
   (ej. `C:\xampp\htdocs\AgriSense`).
2. Verifica los datos de conexión en `config/conexion.php` si tu MySQL
   usa usuario/contraseña distintos a los de XAMPP por defecto.
3. Abre `http://localhost/AgriSense` en tu navegador.
4. Inicia sesión con el usuario de ejemplo o crea una cuenta nueva.

## 3. Arduino

1. Conecta el DHT11 al pin digital `2` y el sensor de humedad de suelo al
   pin analógico `A0` (puedes ajustar esto en `arduino/arduino_sensores.ino`).
2. Instala la librería **DHT sensor library** (de Adafruit) desde el
   Gestor de Librerías del IDE de Arduino.
3. Carga el sketch `arduino/arduino_sensores.ino` en tu Arduino Uno.
4. El Arduino comenzará a enviar líneas por el puerto serial cada 5 segundos
   con el formato: `temperatura,humedad_aire,humedad_suelo`.

## 4. Script de Python

1. Instala las dependencias:
   ```bash
   pip install -r python/requirements.txt
   ```
2. Abre `python/arduino_manager.py` y ajusta:
   - `PUERTO_SERIAL` (ej. `COM3` en Windows, `/dev/ttyUSB0` en Linux).
   - Los datos de `DB_CONFIG` si es necesario.
3. Ejecuta el script:
   ```bash
   python python/arduino_manager.py
   ```
4. El script leerá el Arduino y guardará cada medición en la tabla
   `mediciones`, asociada al cultivo activo que el usuario haya
   seleccionado en el dashboard web.

## Estructura del proyecto

```
AgriSense/
├── index.php
├── login.php
├── registro.php
├── dashboard.php
├── cultivos.php
├── historial.php
├── perfil.php
├── logout.php
├── config/
│   ├── conexion.php
│   └── sesiones.php
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── recomendaciones.php
├── assets/
│   ├── css/style.css
│   ├── js/ (main.js, dashboard.js, cultivos.js)
│   └── img/logo.png
├── ajax/
│   ├── obtener_datos.php
│   ├── actualizar_dashboard.php
│   └── cultivos_crud.php
├── database/
│   └── agrisense.sql
├── python/
│   ├── arduino_manager.py
│   └── requirements.txt
└── arduino/
    └── arduino_sensores.ino
```

## Notas de seguridad implementadas

- Contraseñas cifradas con `password_hash()` / `password_verify()`.
- Consultas con **PDO y sentencias preparadas** (previene inyección SQL).
- Protección CSRF en formularios con token de sesión.
- Verificación de pertenencia de cultivos por usuario en cada operación AJAX.
