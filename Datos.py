import sys
import os
import network
import ubinascii
import machine
from machine import Pin
import urequests as requests
import ujson
import time
import utime
import math
from secrets import secrets  # Archivo separado para las credenciales Wi-Fi
from Wifi_lib import wifi_init, get_html  # Librerías externas

# Inicializar la conexión Wi-Fi
wifi_init()

# Detectar el tipo de tarjeta
class Board:
    class BoardType:
        PICO_W = 'Raspberry Pi Pico W'
        PICO = 'Raspberry Pi Pico'
        RP2040 = 'RP2040'
        ESP8266 = 'ESP8266'
        ESP32 = 'ESP32'
        UNKNOWN = 'Unknown'

    def __init__(self):
        self.type = self.detect_board_type()

    def detect_board_type(self):
        sysname = os.uname().sysname.lower()
        machine_name = os.uname().machine.lower()
        if sysname == 'rp2' and 'pico w' in machine_name:
            return self.BoardType.PICO_W
        elif sysname == 'rp2' and 'pico' in machine_name:
            return self.BoardType.PICO
        elif sysname == 'rp2' and 'rp2040' in machine_name:
            return self.BoardType.RP2040
        elif sysname == 'esp8266':
            return self.BoardType.ESP8266
        elif sysname == 'esp32' and 'esp32' in machine_name:
            return self.BoardType.ESP32
        else:
            return self.BoardType.UNKNOWN

# Detectar tipo de placa
BOARD_TYPE = Board().type
print("Tarjeta Detectada: " + BOARD_TYPE)

# Configuración de los pines según la tarjeta detectada
if BOARD_TYPE == Board.BoardType.ESP32:
    led = Pin(2, Pin.OUT)  # GPIO 2 para el ESP32 (LED integrado)
    button_increment = Pin(26, Pin.IN, Pin.PULL_UP)
    button_decrement = Pin(27, Pin.IN, Pin.PULL_UP)
    button_reset1 = Pin(14, Pin.IN, Pin.PULL_UP)
    button_reset10 = Pin(12, Pin.IN, Pin.PULL_UP)
    led_red = Pin(4, Pin.OUT)  # GPIO para el LED Rojo
    led_green = Pin(2, Pin.OUT)  # GPIO para el LED Verde
    led_blue = Pin(15, Pin.OUT)  # GPIO para el LED Azul
else:
    print("Placa desconocida o no soportada para este ejemplo.")
    sys.exit()

# URLs de los archivos PHP para cada tabla
urls = {
    "co2": "http://192.168.208.146/PHPCO2.php",
    "humedad": "http://192.168.208.146/PHPHumedad.php",
    "humo": "http://192.168.208.146/PHPHumo.php",
    "temperatura": "http://192.168.208.146/PHPTemp.php"
}

# Configuración inicial
NTaylor = 1
angle = 0  # Ángulo en grados
last_button_press = utime.ticks_ms()

# Función para calcular el valor de RGB basado en NTaylor
def calculate_rgb(N):
    if N <= 4:
        led_red.value(1)
        led_green.value(0)
        led_blue.value(0)
        return 1
    elif N <= 7:
        led_red.value(0)
        led_green.value(1)
        led_blue.value(0)
        return 2
    else:
        led_red.value(0)
        led_green.value(0)
        led_blue.value(1)
        return 3

# Función para calcular la tangente usando la serie de Taylor
def calculate_tangent_taylor(x, n):
    result = 0
    power = x
    factorial = 1
    for i in range(1, n + 1):
        factorial *= (2 * i) * (2 * i + 1)  # Factorial para la tangente
        result += power / factorial
        power *= x * x  # Potencia de x
    return result

# Función para obtener la fecha y la hora actuales en MicroPython
def get_current_datetime():
    now = time.localtime()  # Obtiene el tiempo actual en forma de tupla
    fecha = f"{now[0]:04d}-{now[1]:02d}-{now[2]:02d}"  # Formato YYYY-MM-DD
    hora = f"{now[3]:02d}:{now[4]:02d}:{now[5]:02d}"  # Formato HH:MM:SS
    return fecha, hora

# Función para enviar datos a todas las tablas
def send_data():
    global angle, NTaylor

    for i in range(10):  # Enviar 10 valores diferentes a cada tabla
        # Convertir ángulo a radianes para el cálculo
        angle_rad = math.radians(angle)

        tangent_value = calculate_tangent_taylor(10 * (i + 1), NTaylor)

        # Calcular RGB basado en NTaylor
        RGB = calculate_rgb(NTaylor)

        # Obtener la fecha y la hora actuales
        fecha, hora = get_current_datetime()

        # Crear y enviar datos a todas las tablas
        for table in urls.keys():
            data = {
                "valor": tangent_value,
                "fecha": fecha,
                "hora": hora
            }
            headers = {'Content-Type': 'application/json'}
            response = None  # Inicializa response fuera del try

            try:
                response = requests.post(urls[table], data=ujson.dumps(data), headers=headers)
                print(f"Datos enviados a {table}: {response.text}")
            except Exception as e:
                print(f"Error de conexión con {table}: {str(e)}")
            finally:
                # Solo cerramos la respuesta si ha sido creada
                if response:
                    response.close()

        # Incrementar ángulo de 0 a 360
        angle = (angle + 10) % 360
        time.sleep(0.1)


# Función para manejar la pulsación de botones
def handle_buttons():
    global NTaylor, last_button_press

    current_time = utime.ticks_ms()
    if utime.ticks_diff(current_time, last_button_press) > 200:
        if not button_increment.value():
            NTaylor += 1
            print("EJECUTANDO ORDEN: Incremento de NTaylor")
            last_button_press = current_time
        elif not button_decrement.value():
            NTaylor = max(1, NTaylor - 1)
            print("EJECUTANDO ORDEN: Decremento de NTaylor")
            last_button_press = current_time
        elif not button_reset1.value():
            NTaylor = 1
            print("EJECUTANDO ORDEN: Reset a NTaylor = 1")
            last_button_press = current_time
        elif not button_reset10.value():
            NTaylor = 10
            print("EJECUTANDO ORDEN: Reset a NTaylor = 10")
            last_button_press = current_time

# Bucle principal
while True:
    # Manejar pulsaciones de botones
    handle_buttons()

    # Enviar datos a todas las tablas de forma concurrente
    send_data()

