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

# Configuración inicial
NTaylor = 1
RGB = 1
angle = 0  # Ángulo en grados
url = "http://192.168.208.146//PHPCO2.php"  # Reemplaza con la URL correcta
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

# Función para calcular la exponencial usando la serie de Taylor
def calculate_exp_taylor(x, n):
    result = 1  # e^0 = 1
    factorial = 1
    power = 1  # x^0
    for i in range(1, n):
        factorial *= i  # Calcula i! para el siguiente término
        power *= x  # Aumenta el exponente
        result += power / factorial  # Suma el siguiente término de la serie
    return result

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

        # Calcular valores de exponencial
        exp_taylor_value = calculate_exp_taylor(angle_rad, NTaylor)
        exp_trig_value = math.exp(angle_rad)  # Usar math.exp para el valor exponencial
        error_value = abs(exp_taylor_value - exp_trig_value)

        # Calcular RGB basado en NTaylor
        RGB = calculate_rgb(NTaylor)

        # Crear el JSON para enviar
        data = {
            "Exp_Taylor": exp_taylor_value,
            "Exp_Trig": exp_trig_value,
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
