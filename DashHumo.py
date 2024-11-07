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

# Consulta SQL para leer todos los datos de la tabla humo
query = "SELECT fecha, hora, valor FROM humo"
data = pd.read_sql(query, conn)

# Cerrar la conexión
conn.close()

# Convertir fecha y hora en un solo campo de tipo datetime especificando el formato
data['fecha'] = pd.to_datetime(data['fecha'], format='%Y-%m-%d', errors='coerce')
data['hora'] = pd.to_timedelta(data['hora'])
data['fecha_hora'] = data['fecha'] + data['hora']

# Eliminar filas con fechas que no pudieron ser convertidas
data = data.dropna(subset=['fecha_hora'])

# Ordenar los datos por fecha y hora
data = data.sort_values(by='fecha_hora')

# Graficar los datos de humo
plt.figure(figsize=(12, 8))
plt.plot(data['fecha_hora'], data['valor'], label='Concentración de Humo', color='purple')

# Configuración del gráfico
plt.title('Evolución de la Concentración de Humo a lo largo del Tiempo')
plt.xlabel('Fecha y Hora')
plt.ylabel('Valor de Concentración de Humo')
plt.legend()
plt.grid(True)

# Ajustar el formato de la fecha y hora en el eje x
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%Y-%m-%d %H:%M'))
plt.gcf().autofmt_xdate()  # Rotar las etiquetas de fecha para mejor visualización

# Mostrar el gráfico
plt.show()
