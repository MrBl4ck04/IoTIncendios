import pandas as pd
import mysql.connector
import matplotlib.pyplot as plt
import matplotlib.dates as mdates

# Configuración de la base de datos
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'onfirebd'
}

# Consultas SQL para los sensores con la descripción de ubicación
queries = {
    "co2": """
        SELECT c.fecha, c.hora, c.valor, u.descripcion 
        FROM co2 c
        JOIN microcontroladores m ON c.microcontroladores_id = m.id
        JOIN ubicaciones u ON m.ubicaciones_id = u.id
        ORDER BY u.descripcion, c.fecha, c.hora
    """,
    "humedad": """
        SELECT h.fecha, h.hora, h.valor, u.descripcion 
        FROM humedad h
        JOIN microcontroladores m ON h.microcontroladores_id = m.id
        JOIN ubicaciones u ON m.ubicaciones_id = u.id
        ORDER BY u.descripcion, h.fecha, h.hora
    """,
    "humo": """
        SELECT h.fecha, h.hora, h.valor, u.descripcion 
        FROM humo h
        JOIN microcontroladores m ON h.microcontroladores_id = m.id
        JOIN ubicaciones u ON m.ubicaciones_id = u.id
        ORDER BY u.descripcion, h.fecha, h.hora
    """,
    "temperatura": """
        SELECT t.fecha, t.hora, t.valor, u.descripcion 
        FROM temperatura t
        JOIN microcontroladores m ON t.microcontroladores_id = m.id
        JOIN ubicaciones u ON m.ubicaciones_id = u.id
        ORDER BY u.descripcion, t.fecha, t.hora
    """
}

# Función para obtener y procesar los datos de una tabla
def get_sensor_data(query):
    try:
        conn = mysql.connector.connect(**db_config)
        data = pd.read_sql(query, conn)
        conn.close()

        # Convertir fecha y hora a datetime
        data['fecha'] = pd.to_datetime(data['fecha'], format='%Y-%m-%d', errors='coerce')
        data['hora'] = pd.to_timedelta(data['hora'])
        data['fecha_hora'] = data['fecha'] + data['hora']

        # Eliminar filas con fechas inválidas y ordenar los datos
        data = data.dropna(subset=['fecha_hora'])
        return data.sort_values(by='fecha_hora')
    except Exception as e:
        print(f"Error al procesar los datos: {e}")
        return pd.DataFrame()  # Retorna un DataFrame vacío si falla

# Leer y procesar los datos de cada sensor
data_sensores = {sensor: get_sensor_data(query) for sensor, query in queries.items()}

# Crear una figura para graficar los datos
plt.figure(figsize=(14, 8))

# Graficar cada sensor
for sensor, data in data_sensores.items():
    if not data.empty:
        # Graficar cada ubicación
        for ubicacion, group in data.groupby('descripcion'):
            plt.plot(group['fecha_hora'], group['valor'], label=f"{sensor.capitalize()} - {ubicacion}")
    else:
        print(f"No se pudieron graficar datos para el sensor: {sensor}")

# Configuración del gráfico
plt.title('Evolución de los Sensores a lo largo del Tiempo por Ubicación')
plt.xlabel('Fecha y Hora')
plt.ylabel('Valores')
plt.legend(loc="upper left", bbox_to_anchor=(1, 1), title="Sensores y Ubicaciones")
plt.grid(True)

# Formatear el eje X con fechas y horas
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m-%d %H:%M'))
plt.gcf().autofmt_xdate()  # Rotar etiquetas de fecha

# Mostrar la gráfica
plt.tight_layout()
plt.show()
