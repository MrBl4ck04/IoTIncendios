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

# Almacenar lecturas para el promedio móvil
sensor_data = {
    "co2": [],
    "humedad": [],
    "humo": [],
    "temperatura": []
}
average_window = 5  # Número de muestras para el promedio móvil

# Función para calcular el valor de RGB basado en NTaylor
def calculate_rgb(N):
    if N <= 4:
        led_red.value(1)
        led_green.value(0)
        led_blue.value(0)
    elif N <= 7:
        led_red.value(0)
        led_green.value(1)
        led_blue.value(0)
    else:
        led_red.value(0)
        led_green.value(0)
        led_blue.value(1)

# Función para calcular la tangente usando la serie de Taylor
def calculate_tangent_taylor(x, n):
    result = 0
    power = x
    factorial = 1
    for i in range(1, n + 1):
        factorial *= (2 * i) * (2 * i + 1)
        result += power / factorial
        power *= x * x
    return result

# Función para calcular el seno usando la serie de Taylor
def calculate_sine_taylor(x, n):
    result = 0
    power = x
    factorial = 1
    sign = 1
    for i in range(1, n + 1):
        factorial *= (2 * i - 1) * (2 * i) if i > 1 else 1
        result += sign * power / factorial
        power *= x * x
        sign *= -1
    return result

# Función para calcular el coseno usando la serie de Taylor
def calculate_cosine_taylor(x, n):
    result = 0
    power = 1
    factorial = 1
    sign = 1
    for i in range(n):
        result += sign * power / factorial
        power *= x * x
        factorial *= (2 * i + 1) * (2 * i + 2) if i > 0 else 1
        sign *= -1
    return result

# Función para calcular la exponencial usando la serie de Taylor
def calculate_exponential_taylor(x, n):
    result = 1
    power = x
    factorial = 1
    for i in range(1, n + 1):
        result += power / factorial
        power *= x
        factorial *= i
    return result

# Función para obtener la fecha y la hora actuales en MicroPython
def get_current_datetime():
    now = time.localtime()
    fecha = f"{now[0]:04d}-{now[1]:02d}-{now[2]:02d}"
    hora = f"{now[3]:02d}:{now[4]:02d}:{now[5]:02d}"
    return fecha, hora

# Función para calcular el promedio móvil de las lecturas
def moving_average(sensor, value):
    sensor_data[sensor].append(value)
    if len(sensor_data[sensor]) > average_window:
        sensor_data[sensor].pop(0)
    return sum(sensor_data[sensor]) / len(sensor_data[sensor])

# Función para enviar datos a todas las tablas
def send_data():
    global angle, NTaylor

    for i in range(10):  # Enviar 10 valores diferentes a cada tabla
        angle_rad = math.radians(angle)
        functions = {
            "co2": calculate_tangent_taylor,
            "humedad": calculate_sine_taylor,
            "humo": calculate_cosine_taylor,
            "temperatura": calculate_exponential_taylor
        }
        calculate_rgb(NTaylor)

        fecha, hora = get_current_datetime()

        for table, func in functions.items():
            value = func(angle_rad, NTaylor)
            smoothed_value = moving_average(table, value)  # Aplicar promedio móvil
            data = {
                "valor": smoothed_value,
                "fecha": fecha,
                "hora": hora
            }
            headers = {'Content-Type': 'application/json'}
            response = None

            try:
                response = requests.post(urls[table], data=ujson.dumps(data), headers=headers)
                print(f"Datos enviados a {table}: {response.text}")
            except Exception as e:
                print(f"Error de conexión con {table}: {str(e)}")
            finally:
                if response:
                    response.close()

        # Parpadeo del LED por cada envío
        led.value(1)
        time.sleep(0.1)
        led.value(0)
        time.sleep(0.1)

        # Incrementar el ángulo
        angle = (angle + 10) % 360
        time.sleep(0.1)

# Función para manejar la pulsación de botones
def handle_buttons():
    global NTaylor, last_button_press

    current_time = utime.ticks_ms()
    if utime.ticks_diff(current_time, last_button_press) > 200:
        if not button_increment.value():
            NTaylor += 1
            print("Incremento de NTaylor")
            last_button_press = current_time
        elif not button_decrement.value():
            NTaylor = max(1, NTaylor - 1)
            print("Decremento de NTaylor")
            last_button_press = current_time
        elif not button_reset1.value():
            NTaylor = 1
            print("Reset NTaylor a 1")
            last_button_press = current_time
        elif not button_reset10.value():
            NTaylor = 10
            print("Reset NTaylor a 10")
            last_button_press = current_time

# Bucle principal
while True:
    # Enviar 10 datos y pausar
    send_data()

    # Esperar pulsación de botón para ajustar NTaylor
    while True:
        handle_buttons()
        # Esperar hasta detectar un cambio en NTaylor antes de volver a enviar datos
        if not button_increment.value() or not button_decrement.value() or not button_reset1.value() or not button_reset10.value():
            break
