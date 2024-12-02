import machine
import time
import dht
import urequests as requests
import ujson
from secrets import secrets  # Archivo separado para las credenciales Wi-Fi
from Wifi_lib import wifi_init, get_html  # Librerías externas

# Inicializar la conexión Wi-Fi
wifi_init()
# Configuración de los sensores y actuadores
dht_pin = machine.Pin(4)
dht_sensor = dht.DHT22(dht_pin)

flame_pin = machine.ADC(machine.Pin(35))
flame_pin.width(machine.ADC.WIDTH_12BIT)
flame_pin.atten(machine.ADC.ATTN_11DB)

smoke_pin = machine.ADC(machine.Pin(34))
smoke_pin.width(machine.ADC.WIDTH_12BIT)
smoke_pin.atten(machine.ADC.ATTN_11DB)

red_pin = machine.Pin(25, machine.Pin.OUT)
green_pin = machine.Pin(26, machine.Pin.OUT)
blue_pin = machine.Pin(33, machine.Pin.OUT)
buzzer_pin = machine.Pin(22, machine.Pin.OUT)
data_led = machine.Pin(2, machine.Pin.OUT)

# URLs de los scripts PHP
urls = {
    "temperatura": "http://192.168.118.146/PHPTemp.php",
    "humedad": "http://192.168.118.146/PHPHumedad.php",
    "flama": "http://192.168.118.146/PHPFlama.php",
    "humo": "http://192.168.118.146/PHPHumo.php"
}

# Función para configurar el color del LED RGB
def set_rgb_color(red, green, blue):
    red_pin.value(red)
    green_pin.value(green)
    blue_pin.value(blue)

# Función para enviar datos a un script PHP
def enviar_datos_php(url, valor):
    data = {"valor": valor}
    headers = {'Content-Type': 'application/json'}
    try:
        response = requests.post(url, data=ujson.dumps(data), headers=headers)
    except Exception as e:
        print(f"Error al enviar datos a {url}: {e}")
    finally:
        if response:
            response.close()

while True:
    try:
        # Leer datos del DHT22
        data_led.on()
        dht_sensor.measure()
        temperatura = dht_sensor.temperature()
        humedad = dht_sensor.humidity()

        # Leer datos de los sensores analógicos
        valor_flama = flame_pin.read()
        valor_humo = smoke_pin.read()

        # Enviar datos a PHP
        enviar_datos_php(urls["temperatura"], temperatura)
        enviar_datos_php(urls["humedad"], humedad)
        enviar_datos_php(urls["flama"], valor_flama)
        enviar_datos_php(urls["humo"], valor_humo)

        # Encender buzzer y LED según los datos
        if valor_flama < 51 or valor_humo > 2000:
            buzzer_pin.on()
        else:
            buzzer_pin.off()

        if valor_flama > 2000 and valor_humo > 2000:
            set_rgb_color(1, 0, 0)  # Rojo
        elif valor_humo > 2000:
            set_rgb_color(0, 0, 1)  # Azul
        elif valor_flama > 2000:
            set_rgb_color(1, 1, 0)  # Amarillo
        else:
            set_rgb_color(0, 1, 0)  # Verde

        data_led.off()

        # Mostrar valores en consola
        print("\n--- Lectura de sensores ---")
        print(f"Temperatura: {temperatura:.1f} °C")
        print(f"Humedad: {humedad:.1f} %")
        print(f"Flama: {valor_flama}")
        print(f"Humo: {valor_humo}")
        print("---------------------------")

    except Exception as e:
        print(f"Error: {e}")

    # Pausa entre lecturas
    time.sleep(2)