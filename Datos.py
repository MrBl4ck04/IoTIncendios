import urequests as requests
import ujson
import time
import math
from machine import Pin
import utime

# URLs de los archivos PHP para cada tabla
urls = {
    "co2": "http://192.168.208.146/PHPCO2.php",
    "humedad": "http://192.168.208.146/PHPHumedad.php",
    "humo": "http://192.168.208.146/PHPHumo.php",
    "temperatura": "http://192.168.208.146/PHPTemp.php"
}

# Configuración de los pines según la tarjeta detectada
button_increment = Pin(26, Pin.IN, Pin.PULL_UP)
button_decrement = Pin(27, Pin.IN, Pin.PULL_UP)
button_reset1 = Pin(14, Pin.IN, Pin.PULL_UP)
button_reset10 = Pin(12, Pin.IN, Pin.PULL_UP)

# Inicializar NTaylor
NTaylor = 1
last_button_press = utime.ticks_ms()

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

# Función para enviar los datos
def send_data(table, value):
    fecha, hora = get_current_datetime()
    data = {
        "valor": value,
        "fecha": fecha,
        "hora": hora
    }
    headers = {'Content-Type': 'application/json'}
    
    try:
        response = requests.post(urls[table], data=ujson.dumps(data), headers=headers)
        response_data = ujson.loads(response.text)  # Parseamos la respuesta del servidor
        if "error" in response_data:
            print(f"Error al enviar datos a {table}: {response_data['error']}")
        else:
            print(f"Datos enviados exitosamente a {table}: {response_data['message']}")
    except Exception as e:
        print(f"Error de conexión con {table}: {str(e)}")
    finally:
        response.close()

# Función para manejar la pulsación de botones
def handle_buttons():
    global NTaylor, last_button_press

    current_time = utime.ticks_ms()
    if utime.ticks_diff(current_time, last_button_press) > 200:
        if not button_increment.value():
            NTaylor += 1
            print("NTaylor Incrementado:", NTaylor)
            last_button_press = current_time
        elif not button_decrement.value():
            NTaylor = max(1, NTaylor - 1)
            print("NTaylor Decrementado:", NTaylor)
            last_button_press = current_time
        elif not button_reset1.value():
            NTaylor = 1
            print("NTaylor Reset a 1")
            last_button_press = current_time
        elif not button_reset10.value():
            NTaylor = 10
            print("NTaylor Reset a 10")
            last_button_press = current_time

# Enviar múltiples datos a todas las tablas de forma concurrente
def send_all_data_concurrently():
    for i in range(10):  # Enviar 10 valores diferentes a cada tabla
        handle_buttons()  # Actualizar NTaylor con la pulsación de botones
        value = calculate_tangent_taylor(10 * (i + 1), NTaylor)
        
        for table in urls.keys():
            send_data(table, value)
            time.sleep(0.1)  # Esperar un poco para evitar saturación de red

# Ejecutar la función principal
send_all_data_concurrently()

