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
    button_increment = machine.Pin(35, machine.Pin.IN, machine.Pin.PULL_UP)
    button_decrement = machine.Pin(32, machine.Pin.IN, machine.Pin.PULL_UP)
    button_reset1 = machine.Pin(33, machine.Pin.IN, machine.Pin.PULL_UP)
    button_reset10 = machine.Pin(25, machine.Pin.IN, machine.Pin.PULL_UP)
    led_red = machine.Pin(4, machine.Pin.OUT)  # GPIO para el LED Rojo
    led_green = machine.Pin(2, machine.Pin.OUT)  # GPIO para el LED Verde
    led_blue = machine.Pin(15, machine.Pin.OUT)  # GPIO para el LED Azul
else:
    print("Placa desconocida o no soportada para este ejemplo.")
    sys.exit()

# Configuración inicial
NTaylor = 1
RGB = 1
angle = 0  # Ángulo en grados
url = "http://192.168.56.146//PHPTemp.php"  # Reemplaza con la URL correcta
data_sent = False  # Bandera para verificar si se ha enviado el dato
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
def calculate_tan_taylor(x, n):
    # La tangente es sin(x)/cos(x), así que primero se calcula seno y coseno
    sin_taylor = 0
    cos_taylor = 0
    for i in range(n):
        # Calcular seno usando Taylor: x - x^3/3! + x^5/5! - x^7/7! + ...
        sin_taylor += ((-1) ** i) * (x ** (2 * i + 1)) / math.factorial(2 * i + 1)

        # Calcular coseno usando Taylor: 1 - x^2/2! + x^4/4! - x^6/6! + ...
        cos_taylor += ((-1) ** i) * (x ** (2 * i)) / math.factorial(2 * i)

    if cos_taylor != 0:
        return sin_taylor / cos_taylor
    else:
        return float('inf')  # Si el coseno es 0, la tangente es indefinida

# Función para manejar la pulsación de botones
def handle_buttons():
    global NTaylor, last_button_press, data_sent

    current_time = utime.ticks_ms()
    if utime.ticks_diff(current_time, last_button_press) > 200:
        if not button_increment.value():
            NTaylor += 1
            print("EJECUTANDO ORDEN: Incremento de NTaylor")
            data_sent = False
            last_button_press = current_time
        elif not button_decrement.value():
            NTaylor = max(1, NTaylor - 1)
            print("EJECUTANDO ORDEN: Decremento de NTaylor")
            data_sent = False
            last_button_press = current_time
        elif not button_reset1.value():
            NTaylor = 1
            print("EJECUTANDO ORDEN: Reset a NTaylor = 1")
            data_sent = False
            last_button_press = current_time
        elif not button_reset10.value():
            NTaylor = 10
            print("EJECUTANDO ORDEN: Reset a NTaylor = 10")
            data_sent = False
            last_button_press = current_time

# Función para enviar datos al servidor
def send_data():
    global angle, NTaylor

    for _ in range(10):
        # Convertir ángulo a radianes para el cálculo
        angle_rad = math.radians(angle)

        # Calcular valores de tangente
        tan_taylor_value = calculate_tan_taylor(angle_rad, NTaylor)
        tan_trig_value = math.tan(angle_rad)  # Usar math.tan para el valor tangente
        error_value = abs(tan_taylor_value - tan_trig_value)

        # Calcular RGB basado en NTaylor
        RGB = calculate_rgb(NTaylor)

        # Crear el JSON para enviar
        data = {
            "Tan_Taylor": tan_taylor_value,
            "Tan_Trig": tan_trig_value,
            "Error": error_value,
            "RGB": RGB,
            "NTaylor": NTaylor
        }

        # Convertir el JSON en una cadena y enviarlo al servidor
        headers = {'Content-Type': 'application/json'}
        response = requests.post(url, data=ujson.dumps(data), headers=headers)

        # Verificar la respuesta del servidor
        print("Ángulo:", angle, "Data:", data)
        print("° -> Respuesta del servidor:", response.text)
        response.close()

        # Incrementar ángulo de 0 a 360
        angle = (angle + 10) % 360

        # Pausar entre envíos
        led.value(0)
        utime.sleep(0.05)
        led.value(1)
        utime.sleep(0.05)

# Bucle principal
while True:
    # Manejar pulsaciones de botones
    handle_buttons()

    # Enviar datos solo si ha habido una pulsación y los datos aún no se han enviado
    if not data_sent:
        send_data()
        data_sent = True  # Marcar como enviados para no repetir hasta nueva pulsación
