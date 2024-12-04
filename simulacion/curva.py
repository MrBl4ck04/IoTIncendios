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

# Generadores de datos para cada tabla
def generar_datos(fecha_inicio, inicio, fin, direccion="ascendente"):
    valor = inicio
    incremento = (fin - inicio) / 100 if direccion == "ascendente" else (inicio - fin) / 100
    while True:
        yield fecha_inicio.date(), fecha_inicio.time(), round(valor, 3), 3  # microcontroladores_id = 3
        if direccion == "ascendente":
            valor += incremento
            if valor > fin:
                valor = inicio  # Reinicia el valor si llega al límite
        else:
            valor -= incremento
            if valor < fin:
                valor = inicio  # Reinicia el valor si llega al límite

# Generadores para cada tabla
fecha_inicio = datetime.datetime.now()
generadores = {
    "temperatura": generar_datos(fecha_inicio, 18, 50, "ascendente"),
    "humo": generar_datos(fecha_inicio, 100, 5000, "ascendente"),
    "humedad": generar_datos(fecha_inicio, 60, 30, "descendente"),
    "flama": generar_datos(fecha_inicio, 4095, 0, "descendente"),
}

# Función para insertar datos en la tabla
def insertar_datos(tabla, datos):
    sql = f"INSERT INTO {tabla} (fecha, hora, valor, microcontroladores_id) VALUES (%s, %s, %s, %s)"
    cursor.execute(sql, datos)
    conexion.commit()
    print(f"Insertado en {tabla}: {datos}")

# Bucle infinito para insertar datos simultáneamente
try:
    print("Iniciando inserciones. Presiona Ctrl+C para detener.")
    while True:
        # Inserta un dato en cada tabla en cada iteración
        for tabla, generador in generadores.items():
            dato = next(generador)  # Genera el siguiente dato
            insertar_datos(tabla, dato)
        time.sleep(1)  # Pausa para simular datos en tiempo real

except KeyboardInterrupt:
    print("\nPrograma detenido por el usuario.")
finally:
    conexion.close()
    print("Conexión cerrada.")