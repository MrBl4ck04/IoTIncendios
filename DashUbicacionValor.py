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

# Consultas SQL para los sensores y ubicaciones
queries = {
    "co2": "SELECT fecha, hora, valor, id_ubicacion FROM co2",
    "humedad": "SELECT fecha, hora, valor, id_ubicacion FROM humedad",
    "humo": "SELECT fecha, hora, valor, id_ubicacion FROM humo",
    "temperatura": "SELECT fecha, hora, valor, id_ubicacion FROM temperatura"
}
ubicaciones_query = "SELECT id, descripcion FROM ubicaciones"

# Función para obtener datos de sensores
def get_sensor_data(query):
    try:
        conn = mysql.connector.connect(**db_config)
        data = pd.read_sql(query, conn)
        conn.close()

        # Convertir fecha y hora a datetime
        data['fecha'] = pd.to_datetime(data['fecha'], format='%Y-%m-%d', errors='coerce')
        data['hora'] = pd.to_timedelta(data['hora'])
        data['fecha_hora'] = data['fecha'] + data['hora']

        # Eliminar filas con fechas inválidas
        data = data.dropna(subset=['fecha_hora'])
        return data.sort_values(by='fecha_hora')
    except Exception as e:
        print(f"Error al procesar los datos: {e}")
        return pd.DataFrame()  # Retorna un DataFrame vacío si falla

# Función para obtener ubicaciones
def get_locations():
    try:
        conn = mysql.connector.connect(**db_config)
        locations = pd.read_sql(ubicaciones_query, conn)
        conn.close()
        return locations
    except Exception as e:
        print(f"Error al obtener las ubicaciones: {e}")
        return pd.DataFrame()

# Obtener datos de sensores y ubicaciones
data_sensores = {sensor: get_sensor_data(query) for sensor, query in queries.items()}
ubicaciones = get_locations()

if ubicaciones.empty:
    print("No se encontraron ubicaciones. Saliendo...")
    exit()

# Crear la figura para graficar
plt.figure(figsize=(14, 8))

# Graficar cada sensor con la etiqueta de ubicación
for sensor, data in data_sensores.items():
    if not data.empty:
        merged_data = data.merge(ubicaciones, left_on='id_ubicacion', right_on='id', how='left')
        for ubicacion, group in merged_data.groupby('descripcion'):
            plt.plot(group['fecha_hora'], group['valor'], label=f"{sensor.capitalize()} - {ubicacion}")

# Configuración del gráfico
plt.title("Evolución de Sensores por Ubicación")
plt.xlabel("Fecha y Hora")
plt.ylabel("Valor de los Sensores")
plt.legend(loc="upper left", bbox_to_anchor=(1, 1), title="Sensores y Ubicaciones")
plt.grid(True)

# Formatear el eje X con fechas y horas
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m-%d %H:%M'))
plt.gcf().autofmt_xdate()  # Rotar etiquetas de fecha

# Ajustar diseño para espacio adicional
plt.tight_layout()

# Mostrar la gráfica
plt.show()
