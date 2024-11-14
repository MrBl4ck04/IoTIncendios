import pandas as pd
import mysql.connector
import matplotlib.pyplot as plt
import matplotlib.dates as mdates


# Configuración de la conexión a la base de datos
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'onfirebd'
}

# Conectar a la base de datos
try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()

    # Consultas para los cuatro sensores
    queries = {
        "co2": "SELECT fecha, hora, valor FROM co2 ORDER BY fecha, hora",
        "humedad": "SELECT fecha, hora, valor FROM humedad ORDER BY fecha, hora",
        "humo": "SELECT fecha, hora, valor FROM humo ORDER BY fecha, hora",
        "temperatura": "SELECT fecha, hora, valor FROM temperatura ORDER BY fecha, hora"
    }

    data = {}
    for sensor, query in queries.items():
        cursor.execute(query)
        results = cursor.fetchall()
        df = pd.DataFrame(results, columns=["fecha", "hora", "valor"])
        df["datetime"] = pd.to_datetime(df["fecha"].astype(str) + " " + df["hora"].astype(str))
        data[sensor] = df

    # Cerrar la conexión
    cursor.close()
    conn.close()

    # Graficar los datos
    plt.figure(figsize=(12, 6))

    for sensor, df in data.items():
        plt.plot(df["datetime"], df["valor"], label=sensor.capitalize())

    # Configuración de la gráfica
    plt.title("Datos de Sensores")
    plt.xlabel("Fecha y Hora")
    plt.ylabel("Valor")
    plt.legend()
    plt.grid(True)
    plt.xticks(rotation=45)
    plt.tight_layout()

    # Mostrar la gráfica
    plt.show()

except mysql.connector.Error as err:
    print(f"Error: {err}")
