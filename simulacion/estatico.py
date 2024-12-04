import mysql.connector
import datetime
import time

# Configuración de conexión a la base de datos
conexion = mysql.connector.connect(
    host="192.168.118.146",
    user="Dam",
    passwd="",
    database="onfirebd"
)
cursor = conexion.cursor()

# Función para insertar datos en la tabla
def insertar_datos(tabla, datos):
    sql = f"INSERT INTO {tabla} (fecha, hora, valor, microcontroladores_id) VALUES (%s, %s, %s, %s)"
    cursor.execute(sql, datos)
    conexion.commit()
    print(f"Insertado en {tabla}: {datos}")

# Función para simular el microcontrolador 4
def dispositivo_4():
    fecha_inicio = datetime.datetime.now()
    datos_constantes = {
        "temperatura": 21,
        "humo": 50,
        "humedad": 65,
        "flama": 4095,
    }

    try:
        print("Iniciando inserciones para el microcontrolador 4. Presiona Ctrl+C para detener.")
        while True:
            for tabla, valor in datos_constantes.items():
                dato = (fecha_inicio.date(), fecha_inicio.time(), valor, 4)  # microcontroladores_id = 4
                insertar_datos(tabla, dato)
            fecha_inicio += datetime.timedelta(seconds=1)  # Incrementa el tiempo para el siguiente lote
            time.sleep(1)  # Pausa para simular datos en tiempo real
    except KeyboardInterrupt:
        print("\nPrograma detenido por el usuario.")
    finally:
        conexion.close()
        print("Conexión cerrada.")

# Ejecutar simulación
dispositivo_4()