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

# Función para leer datos de cada tabla y procesar fechas y horas
def get_sensor_data(sensor_table):
    conn = mysql.connector.connect(**db_config)
    query = f"SELECT fecha, hora, valor FROM {sensor_table}"
    data = pd.read_sql(query, conn)
    conn.close()

    # Convertir fecha y hora en un solo campo de tipo datetime
    data['fecha'] = pd.to_datetime(data['fecha'], format='%Y-%m-%d', errors='coerce')
    data['hora'] = pd.to_timedelta(data['hora'])
    data['fecha_hora'] = data['fecha'] + data['hora']

    # Eliminar filas con fechas inválidas
    data = data.dropna(subset=['fecha_hora'])

    # Ordenar los datos por fecha y hora
    data = data.sort_values(by='fecha_hora')
    return data

# Leer los datos de los cuatro sensores
sensores = ['co2', 'humedad', 'humo', 'temperatura']
data_sensores = {sensor: get_sensor_data(sensor) for sensor in sensores}

# Graficar los datos
plt.figure(figsize=(14, 8))

for sensor, data in data_sensores.items():
    plt.plot(data['fecha_hora'], data['valor'], label=sensor.capitalize())

# Configuración del gráfico
plt.title('Evolución de los Sensores a lo largo del Tiempo')
plt.xlabel('Fecha y Hora')
plt.ylabel('Valores')
plt.legend()
plt.grid(True)

# Ajustar el formato de la fecha y hora en el eje x
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m-%d %H:%M'))
plt.gcf().autofmt_xdate()  # Rotar las etiquetas de fecha

# Mostrar el gráfico
plt.tight_layout()
plt.show()
