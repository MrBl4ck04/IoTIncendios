import tkinter as tk
from tkinter import ttk, messagebox, simpledialog
import mysql.connector

# Conectar a la base de datos MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="onfireBD"
)
cursor = conn.cursor()

# Variables globales para ventanas abiertas
sensores_win = None
agregar_sensor_win = None
modificar_sensor_win = None

# Función para mostrar sensores
def mostrar_sensores():
    global sensores_win
    if sensores_win is not None:
        sensores_win.destroy()

    sensores_win = tk.Toplevel(root)
    sensores_win.title("Gestión de Sensores")
    sensores_win.geometry("600x400")
    sensores_win.configure(bg="#2d2d2d")

    # Crear tabla para mostrar sensores
    cols = ("ID", "Tipo", "Microcontrolador")
    tree = ttk.Treeview(sensores_win, columns=cols, show='headings')
    for col in cols:
        tree.heading(col, text=col)

    tree.pack(expand=True, fill='both', padx=10, pady=10)
    scrollbar = ttk.Scrollbar(sensores_win, orient="vertical", command=tree.yview)
    scrollbar.pack(side=tk.RIGHT, fill='y')
    tree.configure(yscroll=scrollbar.set)

    # Obtener y mostrar datos de sensores
    cursor.execute("""
        SELECT s.id, s.tipo, m.nombre 
        FROM sensores s 
        JOIN microcontroladores m ON s.microcontroladores_id = m.id
    """)
    datos = cursor.fetchall()
    for dato in datos:
        tree.insert("", "end", values=dato)

    # Menú contextual para modificar y eliminar
    menu_contextual = tk.Menu(sensores_win, tearoff=0)
    menu_contextual.add_command(label="Modificar", command=modificar_sensor)
    menu_contextual.add_command(label="Eliminar", command=eliminar_sensor)

    def mostrar_menu(event):
        item = tree.identify_row(event.y)
        if item:
            global selected_id
            selected_id = tree.item(item, "values")[0]
            menu_contextual.post(event.x_root, event.y_root)

    tree.bind("<Button-3>", mostrar_menu)

    # Botón para agregar un nuevo sensor
    btn_agregar_sensor = tk.Button(sensores_win, text="Agregar Sensor", command=agregar_sensor, bg="#2082AA", fg="white", font=('Arial', 10, 'bold'))
    btn_agregar_sensor.pack(pady=10)

# Función para agregar un nuevo sensor
def agregar_sensor():
    global agregar_sensor_win
    if agregar_sensor_win is not None:
        agregar_sensor_win.destroy()

    agregar_sensor_win = tk.Toplevel(root)
    agregar_sensor_win.title("Agregar Sensor")
    agregar_sensor_win.configure(bg="#2d2d2d")

    tk.Label(agregar_sensor_win, text="Tipo", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=0, column=0, padx=10, pady=10)
    
    # Cambiar el Entry a un Combobox con opciones predefinidas
    tipo_combobox = ttk.Combobox(agregar_sensor_win, font=('Helvetica', 10), width=20, values=["CO2", "Humedad", "Humo", "Temperatura"])
    tipo_combobox.grid(row=0, column=1, padx=10, pady=10)

    tk.Label(agregar_sensor_win, text="Microcontrolador", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=1, column=0, padx=10, pady=10)
    microcontrolador_combobox = ttk.Combobox(agregar_sensor_win, font=('Helvetica', 10), width=18)
    actualizar_microcontroladores(microcontrolador_combobox)
    microcontrolador_combobox.grid(row=1, column=1, padx=10, pady=10)

    def guardar_sensor():
        tipo = tipo_combobox.get()
        microcontrolador = microcontrolador_combobox.get()
        microcontrolador_id = microcontroladores.get(microcontrolador)

        if not tipo or microcontrolador_id is None:
            messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
            return

        cursor.execute("INSERT INTO sensores (tipo, microcontroladores_id) VALUES (%s, %s)", (tipo, microcontrolador_id))
        conn.commit()
        agregar_sensor_win.destroy()
        mostrar_sensores()

    btn_guardar = tk.Button(agregar_sensor_win, text="Guardar", command=guardar_sensor, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
    btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

# Función para cargar microcontroladores en el Combobox
microcontroladores = {}
def actualizar_microcontroladores(combobox):
    cursor.execute("SELECT id, nombre FROM microcontroladores")
    global microcontroladores
    microcontroladores = {nombre: mid for mid, nombre in cursor.fetchall()}
    combobox["values"] = list(microcontroladores.keys())

# Función para eliminar sensor
def eliminar_sensor():
    confirmacion = messagebox.askyesno("Confirmar eliminación", "¿Está seguro de que desea eliminar este sensor?")
    if confirmacion:
        cursor.execute("DELETE FROM sensores WHERE id = %s", (selected_id,))
        conn.commit()
        mostrar_sensores()

# Función para modificar sensor
def modificar_sensor():
    global modificar_sensor_win
    if modificar_sensor_win is not None:
        modificar_sensor_win.destroy()

    modificar_sensor_win = tk.Toplevel(root)
    modificar_sensor_win.title("Modificar Sensor")
    modificar_sensor_win.configure(bg="#2d2d2d")

    cursor.execute("SELECT tipo, microcontroladores_id FROM sensores WHERE id = %s", (selected_id,))
    tipo_actual, microcontrolador_actual_id = cursor.fetchone()

    tk.Label(modificar_sensor_win, text="Tipo", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=0, column=0, padx=10, pady=10)
    
    # Mostrar el tipo actual en el combobox
    tipo_combobox = ttk.Combobox(modificar_sensor_win, font=('Helvetica', 10), width=20, values=["CO2", "Humedad", "Humo", "Temperatura"])
    tipo_combobox.set(tipo_actual)
    tipo_combobox.grid(row=0, column=1, padx=10, pady=10)

    tk.Label(modificar_sensor_win, text="Microcontrolador", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=1, column=0, padx=10, pady=10)
    microcontrolador_combobox = ttk.Combobox(modificar_sensor_win, font=('Helvetica', 10), width=18)
    actualizar_microcontroladores(microcontrolador_combobox)
    microcontrolador_actual = [nombre for nombre, mid in microcontroladores.items() if mid == microcontrolador_actual_id][0]
    microcontrolador_combobox.set(microcontrolador_actual)
    microcontrolador_combobox.grid(row=1, column=1, padx=10, pady=10)

    def guardar_cambios():
        tipo = tipo_combobox.get()
        microcontrolador = microcontrolador_combobox.get()
        microcontrolador_id = microcontroladores.get(microcontrolador)

        if not tipo or microcontrolador_id is None:
            messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
            return

        cursor.execute("UPDATE sensores SET tipo = %s, microcontroladores_id = %s WHERE id = %s", (tipo, microcontrolador_id, selected_id))
        conn.commit()
        modificar_sensor_win.destroy()
        mostrar_sensores()

    btn_guardar = tk.Button(modificar_sensor_win, text="Guardar Cambios", command=guardar_cambios, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
    btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

# Ejecutar la aplicación
root = tk.Tk()
root.title("Gestión de Sensores")
root.geometry("600x400")
root.configure(bg="#2d2d2d")
mostrar_sensores()
root.mainloop()
