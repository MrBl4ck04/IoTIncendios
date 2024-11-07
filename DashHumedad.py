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

# Consulta SQL para leer todos los datos de la tabla humedad
query = "SELECT fecha, hora, valor FROM humedad"
data = pd.read_sql(query, conn)

# Cerrar la conexión
conn.close()

# Convertir fecha y hora en un solo campo de tipo datetime especificando el formato
data['fecha'] = pd.to_datetime(data['fecha'], format='%Y-%m-%d', errors='coerce')
data['hora'] = pd.to_timedelta(data['hora'])
data['fecha_hora'] = data['fecha'] + data['hora']

# Eliminar filas con fechas que no pudieron ser convertidas
data = data.dropna(subset=['fecha_hora'])

# Ordenar los datos por fecha y hora en caso de que estén desordenados
data = data.sort_values(by='fecha_hora')

# Graficar los datos de humedad
plt.figure(figsize=(12, 8))
plt.plot(data['fecha_hora'], data['valor'], label='Humedad', color='green')

# Configuración del gráfico
plt.title('Evolución de la Humedad a lo largo del Tiempo')
plt.xlabel('Fecha y Hora')
plt.ylabel('Valor de Humedad (%)')
plt.legend()
plt.grid(True)

# Ajustar el formato de la fecha y hora en el eje x
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m-%d %H:%M'))
plt.gcf().autofmt_xdate()  # Rotar las etiquetas de fecha para mejor visualización

# Mostrar el gráfico
plt.show()
