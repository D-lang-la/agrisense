"""
AgriSense - arduino_manager.py
================================
Lee continuamente el puerto serial donde está conectado el Arduino Uno
(sensor DHT11 + sensor de humedad de suelo) y guarda cada medición en
la base de datos MySQL (tabla `mediciones`).

Este script NO genera recomendaciones ni contiene lógica de negocio:
esa parte vive en PHP. Aquí solo se lee el sensor y se persiste el dato.

Formato esperado desde el Arduino (una línea por lectura, vía Serial.println):
    TEMPERATURA,HUMEDAD_AIRE,HUMEDAD_SUELO
Ejemplo:
    26.4,63.2,540

Requisitos:
    pip install pyserial mysql-connector-python
"""

import sys
import time
import logging
from datetime import datetime

import serial
import mysql.connector
from mysql.connector import Error as MySQLError

# ---------------------------------------------------------------
# CONFIGURACIÓN — ajusta estos valores a tu entorno
# ---------------------------------------------------------------
PUERTO_SERIAL = "COM4"        # En Linux/Mac normalmente "/dev/ttyUSB0" o "/dev/ttyACM0"
BAUDIOS = 9600
TIMEOUT_SERIAL = 2            # segundos

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "agrisense",
}

# Usuario y cultivo activos para asociar las mediciones.
# En un entorno real, este dato podría leerse de la tabla `usuarios`
# (columna cultivo_activo_id) para saber qué cultivo está monitoreando
# el usuario en este momento.
ID_USUARIO_DEFECTO = 3

INTERVALO_REINTENTO = 5       # segundos antes de reintentar una conexión fallida

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)
log = logging.getLogger("arduino_manager")


def conectar_serial():
    """Abre la conexión serial con el Arduino. Reintenta hasta lograrlo."""
    while True:
        try:
            conexion = serial.Serial(PUERTO_SERIAL, BAUDIOS, timeout=TIMEOUT_SERIAL)
            log.info(f"Conectado al Arduino en {PUERTO_SERIAL} @ {BAUDIOS} baudios.")
            time.sleep(2)  # Esperar a que el Arduino reinicie tras abrir el puerto
            return conexion
        except serial.SerialException as e:
            log.error(f"No se pudo abrir el puerto {PUERTO_SERIAL}: {e}")
            log.info(f"Reintentando en {INTERVALO_REINTENTO} segundos...")
            time.sleep(INTERVALO_REINTENTO)


def conectar_mysql():
    """Abre la conexión a MySQL. Reintenta hasta lograrlo."""
    while True:
        try:
            conexion = mysql.connector.connect(**DB_CONFIG)
            log.info("Conectado a la base de datos MySQL (agrisense).")
            return conexion
        except MySQLError as e:
            log.error(f"No se pudo conectar a MySQL: {e}")
            log.info(f"Reintentando en {INTERVALO_REINTENTO} segundos...")
            time.sleep(INTERVALO_REINTENTO)


def obtener_cultivo_activo(conexion_mysql, id_usuario):
    """Consulta cuál es el cultivo activo configurado por el usuario en la web."""
    cursor = conexion_mysql.cursor()
    cursor.execute(
        "SELECT cultivo_activo_id FROM usuarios WHERE id_usuario = %s", (id_usuario,)
    )
    fila = cursor.fetchone()
    cursor.close()
    return fila[0] if fila and fila[0] else None


def parsear_linea(linea: str):
    """
    Convierte una línea recibida del Arduino en (temperatura, humedad_aire, humedad_suelo).
    Devuelve None si la línea no tiene el formato esperado.
    """
    try:
        partes = linea.strip().split(",")
        if len(partes) != 3:
            return None
        temperatura = float(partes[0])
        humedad_aire = float(partes[1])
        humedad_suelo = float(partes[2])
        return temperatura, humedad_aire, humedad_suelo
    except (ValueError, AttributeError):
        return None


def guardar_medicion(conexion_mysql, id_usuario, id_cultivo, temperatura, humedad_aire, humedad_suelo):
    """Inserta una nueva medición en la base de datos."""
    cursor = conexion_mysql.cursor()
    cursor.execute(
        """
        INSERT INTO mediciones (id_usuario, id_cultivo, temperatura, humedad_aire, humedad_suelo, fecha)
        VALUES (%s, %s, %s, %s, %s, %s)
        """,
        (id_usuario, id_cultivo, temperatura, humedad_aire, humedad_suelo, datetime.now()),
    )
    conexion_mysql.commit()
    cursor.close()


def main():
    log.info("Iniciando AgriSense - Arduino Manager")

    conexion_mysql = conectar_mysql()
    conexion_serial = conectar_serial()

    id_usuario = ID_USUARIO_DEFECTO

    try:
        while True:
            try:
                linea_cruda = conexion_serial.readline().decode("utf-8", errors="ignore")
            except serial.SerialException:
                log.warning("Se perdió la conexión serial. Reintentando...")
                conexion_serial.close()
                conexion_serial = conectar_serial()
                continue

            if not linea_cruda:
                continue  # timeout sin datos, seguimos escuchando

            datos = parsear_linea(linea_cruda)
            if datos is None:
                log.warning(f"Línea con formato inválido, se ignora: {linea_cruda.strip()!r}")
                continue

            temperatura, humedad_aire, humedad_suelo = datos

            # Verificar conexión a MySQL antes de escribir
            if not conexion_mysql.is_connected():
                log.warning("Conexión a MySQL perdida. Reconectando...")
                conexion_mysql = conectar_mysql()

            id_cultivo = obtener_cultivo_activo(conexion_mysql, id_usuario)
            if not id_cultivo:
                log.info("El usuario no tiene un cultivo activo seleccionado. Se descarta la lectura.")
                continue

            try:
                guardar_medicion(conexion_mysql, id_usuario, id_cultivo, temperatura, humedad_aire, humedad_suelo)
                log.info(
                    f"Medición guardada -> Temp: {temperatura}°C | Hum. aire: {humedad_aire}% | Hum. suelo: {humedad_suelo}"
                )
            except MySQLError as e:
                log.error(f"Error al guardar en MySQL: {e}")

    except KeyboardInterrupt:
        log.info("Detenido manualmente por el usuario (Ctrl+C).")
    finally:
        if conexion_serial and conexion_serial.is_open:
            conexion_serial.close()
        if conexion_mysql and conexion_mysql.is_connected():
            conexion_mysql.close()
        log.info("Conexiones cerradas. Programa finalizado.")


if __name__ == "__main__":
    main()
