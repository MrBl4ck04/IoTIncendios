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

# Conectar a la base de datos MySQL
conn = mysql.connector.connect(**db_config)

# Consulta SQL para unir lectura con sensores y obtener el tipo de sensor
query = """
SELECT lectura.sensores_id, sensores.tipo, lectura.fecha, lectura.hora, lectura.valor
FROM lectura
JOIN sensores ON lectura.sensores_id = sensores.id
"""
data = pd.read_sql(query, conn)

# Cerrar la conexión
conn.close()

# Convertir fecha y hora en un solo campo de tipo datetime
data['fecha'] = pd.to_datetime(data['fecha'], format='%Y-%m-%d', errors='coerce')
data['hora'] = pd.to_timedelta(data['hora'])
data['fecha_hora'] = data['fecha'] + data['hora']

# Eliminar filas con fechas que no pudieron ser convertidas
data = data.dropna(subset=['fecha_hora'])

# Ordenar los datos por fecha y hora en caso de que estén desordenados
data = data.sort_values(by='fecha_hora')

# Crear el gráfico
plt.figure(figsize=(12, 8))

# Graficar una línea por cada tipo de sensor
tipos = data['tipo'].unique()
for tipo in tipos:
    tipo_data = data[data['tipo'] == tipo]
    plt.plot(tipo_data['fecha_hora'], tipo_data['valor'], label=f'Sensor {tipo}')

# Configuración del gráfico
plt.title('Evolución de Valores por Sensor a lo largo del Tiempo')
plt.xlabel('Fecha y Hora')
plt.ylabel('Valor')
plt.legend(title="Sensores")
plt.grid(True)

# Ajustar el formato de la fecha y hora en el eje x
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m-%d %H:%M'))
plt.gcf().autofmt_xdate()  # Rotar las etiquetas de fecha para mejor visualización

# Mostrar el gráfico
plt.show()
