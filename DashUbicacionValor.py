import pandas as pd
import mysql.connector
import matplotlib.pyplot as plt
import numpy as np

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

# Preparar los datos por ubicación para el gráfico
ubicaciones = []
co2_values = []
humedad_values = []
humo_values = []
temperatura_values = []

for sensor, data in data_sensores.items():
    if not data.empty:
        # Agrupar los datos por ubicación
        for ubicacion, group in data.groupby('descripcion'):
            if ubicacion not in ubicaciones:
                ubicaciones.append(ubicacion)
            
            # Asegurarse de que los valores de cada sensor estén agregados por ubicación
            if sensor == "co2":
                co2_values.append(group['valor'].mean())  # Usamos el valor medio por ubicación
            elif sensor == "humedad":
                humedad_values.append(group['valor'].mean())
            elif sensor == "humo":
                humo_values.append(group['valor'].mean())
            elif sensor == "temperatura":
                temperatura_values.append(group['valor'].mean())

# Organizar los valores para el gráfico
sensor_labels = ['CO2', 'Humedad', 'Humo', 'Temperatura']
values_matrix = np.array([co2_values, humedad_values, humo_values, temperatura_values])

# Crear un gráfico de barras agrupadas
bar_width = 0.2  # Ancho de las barras
index = np.arange(len(ubicaciones))  # Índices para las ubicaciones

# Graficar cada sensor
plt.bar(index - 1.5*bar_width, co2_values, bar_width, label='CO2', color='b')
plt.bar(index - 0.5*bar_width, humedad_values, bar_width, label='Humedad', color='g')
plt.bar(index + 0.5*bar_width, humo_values, bar_width, label='Humo', color='r')
plt.bar(index + 1.5*bar_width, temperatura_values, bar_width, label='Temperatura', color='c')

# Configuración del gráfico
plt.title('Promedio de Valores de Sensores por Ubicación')
plt.xlabel('Ubicación')
plt.ylabel('Valores Promedio')
plt.xticks(index, ubicaciones, rotation=45, ha='right')
plt.legend(title="Sensores")
plt.grid(True)

# Mostrar la gráfica
plt.tight_layout()
plt.show()
