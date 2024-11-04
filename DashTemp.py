import pandas as pd
import mysql.connector
import matplotlib.pyplot as plt

# Configuración de la base de datos
db_config = {
    'host': 'localhost',   # Cambia si tu servidor MySQL está en otra dirección
    'user': 'root',        # Cambia esto por tu usuario MySQL
    'password': '',        # Cambia esto por tu contraseña MySQL
    'database': 'onfirebd'  # Cambia esto por el nombre de tu base de datos
}

# Conectar a la base de datos MySQL
conn = mysql.connector.connect(**db_config)

# Consulta SQL para leer todos los datos de la tabla temperatura
query = "SELECT fecha, hora, valor FROM temperatura"
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

# Graficar los datos de temperatura
plt.figure(figsize=(12, 8))
plt.plot(data['fecha_hora'], data['valor'], label='Temperatura', color='blue')

# Configuración del gráfico
plt.title('Evolución de la Temperatura a lo largo del Tiempo')
plt.xlabel('Fecha y Hora')
plt.ylabel('Valor de Temperatura')
plt.legend()
plt.grid(True)

# Mostrar el gráfico
plt.show()