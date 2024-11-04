import tkinter as tk
from tkinter import font
from tkinter import ttk, messagebox, filedialog, simpledialog
import mysql.connector
import csv

# Conectar a la base de datos MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="onfireBD"
)
cursor = conn.cursor()

# Variables globales para ventanas abiertas
sensor_win = None
modificar_win = None
agregar_win = None
botones_sensores = {}

# Función para mostrar datos del sensor
def mostrar_datos_sensor(sensor):
    global sensor_win
    if sensor_win is not None:
        sensor_win.destroy()

    sensor_win = tk.Toplevel(root)
    sensor_win.title(f"Datos de {sensor.capitalize()}")
    sensor_win.configure(bg="#2d2d2d")

    frame = tk.Frame(sensor_win, bg="#404040", padx=10, pady=10)
    frame.pack(expand=True, fill='both')

    btn_agregar = tk.Button(sensor_win, text="Agregar Dato", command=lambda: agregar_dato(sensor), bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
    btn_agregar.pack(pady=10)

    # Crear tabla para mostrar datos
    cols = ("ID", "Fecha", "Hora", "Valor")
    tree = ttk.Treeview(frame, columns=cols, show='headings')

    for col in cols:
        tree.heading(col, text=col)

    tree.pack(side=tk.LEFT, expand=True, fill='both')

    scrollbar = ttk.Scrollbar(frame, orient="vertical", command=tree.yview)
    scrollbar.pack(side=tk.RIGHT, fill='y')
    tree.configure(yscroll=scrollbar.set)

    # Mostrar datos del sensor
    cursor.execute(f"SELECT * FROM {sensor}")
    datos = cursor.fetchall()

    for dato in datos:
        tree.insert("", "end", values=dato)

    # Exportar datos a CSV
    def exportar_csv():
        file_path = filedialog.asksaveasfilename(defaultextension=".csv", filetypes=[("CSV files", "*.csv")])
        if file_path:
            with open(file_path, mode='w', newline='') as file:
                writer = csv.writer(file)
                writer.writerow(cols)
                writer.writerows(datos)
            messagebox.showinfo("Exportación exitosa", "Datos exportados a CSV exitosamente.")

    # Botón de exportar
    btn_exportar = tk.Button(sensor_win, text="Exportar CSV", command=exportar_csv, bg="#2082AA", fg="white", font=('Arial', 11, 'bold'))
    btn_exportar.pack(pady=10)

    # Función para eliminar dato
    def eliminar_dato(dato_id):
        if messagebox.askyesno("Confirmar", f"¿Estás seguro de que deseas eliminar este dato? (Id:{dato_id})"):
            cursor.execute(f"DELETE FROM {sensor} WHERE id = %s", (dato_id,))
            conn.commit()
            mostrar_datos_sensor(sensor)
            messagebox.showinfo("Éxito", "Dato eliminado exitosamente.")

    # Función para modificar dato
    def modificar_dato(dato_id):
        global modificar_win
        if modificar_win is not None:
            modificar_win.destroy()

        modificar_win = tk.Toplevel(sensor_win)
        modificar_win.title("Modificar Dato")
        modificar_win.configure(bg="#2d2d2d")

        cursor.execute(f"SELECT * FROM {sensor} WHERE id = %s", (dato_id,))
        dato = cursor.fetchone()

        lbl_valor = tk.Label(modificar_win, text="Valor", font=('Helvetica', 10), fg="white", bg="#2d2d2d")
        lbl_valor.grid(row=0, column=0, padx=10, pady=10)
        entry_valor = tk.Entry(modificar_win, font=('Helvetica', 10), width=20)
        entry_valor.insert(0, dato[3])
        entry_valor.grid(row=0, column=1, padx=10, pady=10)

        def guardar_modificacion():
            nuevo_valor = entry_valor.get()
            if not nuevo_valor:
                messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
                return

            query = f"UPDATE {sensor} SET valor=%s WHERE id=%s"
            cursor.execute(query, (nuevo_valor, dato_id))
            conn.commit()
            messagebox.showinfo("Éxito", "Dato modificado exitosamente.")
            modificar_win.destroy()
            mostrar_datos_sensor(sensor)

        btn_guardar = tk.Button(modificar_win, text="Guardar cambios", command=guardar_modificacion, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
        btn_guardar.grid(row=1, column=0, columnspan=2, pady=10)

    # Crear menú contextual para la modificación y eliminación de datos
    menu_contextual = tk.Menu(sensor_win, tearoff=0)
    menu_contextual.add_command(label="Modificar", command=lambda: modificar_dato(selected_id))
    menu_contextual.add_command(label="Eliminar", command=lambda: eliminar_dato(selected_id))

    def mostrar_menu(event):
        item = tree.identify_row(event.y)
        if item:
            global selected_id
            selected_id = tree.item(item, "values")[0]
            menu_contextual.post(event.x_root, event.y_root)

    tree.bind("<Button-3>", mostrar_menu)

# Función para agregar un nuevo dato
def agregar_dato(sensor):
    global agregar_win
    if agregar_win is not None:
        agregar_win.destroy()

    agregar_win = tk.Toplevel(root)
    agregar_win.title("Agregar Dato")
    agregar_win.configure(bg="#2d2d2d")

    lbl_valor = tk.Label(agregar_win, text="Valor", font=('Helvetica', 10), fg="white", bg="#2d2d2d")
    lbl_valor.grid(row=0, column=0, padx=10, pady=10)
    entry_valor = tk.Entry(agregar_win, font=('Helvetica', 10), width=20)
    entry_valor.grid(row=0, column=1, padx=10, pady=10)

    def guardar_dato():
        valor = entry_valor.get()
        if not valor:
            messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
            return

        query = f"INSERT INTO {sensor} (fecha, hora, valor) VALUES (CURDATE(), CURTIME(), %s)"
        cursor.execute(query, (valor,))
        conn.commit()
        messagebox.showinfo("Éxito", "Dato agregado exitosamente.")
        agregar_win.destroy()
        mostrar_datos_sensor(sensor)

    btn_guardar = tk.Button(agregar_win, text="Guardar Dato", command=guardar_dato, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
    btn_guardar.grid(row=1, column=0, columnspan=2, pady=10)

# Funciones ABM de sensores (agregar, modificar, eliminar)
def crear_sensor():
    sensor_nombre = simpledialog.askstring("Agregar Sensor", "Ingrese el nombre del nuevo sensor:")
    if sensor_nombre:
        try:
            cursor.execute(f"""
                CREATE TABLE {sensor_nombre} (
                    id INT NOT NULL AUTO_INCREMENT,
                    fecha DATE NOT NULL,
                    hora TIME NOT NULL,
                    valor DOUBLE(30,3) NOT NULL,
                    PRIMARY KEY (id)
                )
            """)
            conn.commit()
            agregar_boton_sensor(sensor_nombre)
            messagebox.showinfo("Éxito", f"Sensor '{sensor_nombre}' creado exitosamente.")
        except mysql.connector.Error as e:
            messagebox.showerror("Error", f"No se pudo crear el sensor: {e}")

def modificar_sensor():
    sensor_nombre = simpledialog.askstring("Modificar Sensor", "Ingrese el nombre del sensor a modificar:")
    if sensor_nombre:
        nueva_columna = simpledialog.askstring("Modificar Sensor", "Ingrese el nombre de la nueva columna (opcional):")
        if nueva_columna:
            cursor.execute(f"ALTER TABLE {sensor_nombre} ADD COLUMN {nueva_columna} DOUBLE(30,3)")
            conn.commit()
            messagebox.showinfo("Éxito", f"Columna '{nueva_columna}' añadida al sensor '{sensor_nombre}'.")

def eliminar_sensor():
    sensor_nombre = simpledialog.askstring("Eliminar Sensor", "Ingrese el nombre del sensor a eliminar:")
    if sensor_nombre:
        if messagebox.askyesno("Confirmar", f"¿Estás seguro de que deseas eliminar el sensor '{sensor_nombre}' y todos sus datos?"):
            cursor.execute(f"DROP TABLE {sensor_nombre}")
            conn.commit()
            if sensor_nombre in botones_sensores:
                botones_sensores[sensor_nombre].destroy()
                del botones_sensores[sensor_nombre]
            messagebox.showinfo("Éxito", f"Sensor '{sensor_nombre}' eliminado exitosamente.")

# Agregar botones de sensores a la ventana principal
def agregar_boton_sensor(sensor):
    btn_sensor = tk.Button(frame_sensores, text=sensor.capitalize(), command=lambda: mostrar_datos_sensor(sensor), bg="#5e17eb", fg="white", font=('Arial', 12, 'bold'))
    btn_sensor.pack(pady=5)
    botones_sensores[sensor] = btn_sensor

# Cargar todos los sensores y agregar sus botones, excluyendo la tabla 'usuarios'
def cargar_sensores():
    cursor.execute("SHOW TABLES")
    sensores = [row[0] for row in cursor.fetchall() if row[0] != "usuarios"]
    for sensor in sensores:
        agregar_boton_sensor(sensor)

# Crear interfaz principal
root = tk.Tk()
root.title("Gestión de Sensores y Datos")
root.geometry("600x600")
root.configure(bg="#2d2d2d")

# Título estilizado
title_font = font.Font(family="Helvetica", size=18, weight="bold")
title_label = tk.Label(root, text="🔥 On Fire - Gestión de Datos y Sensores 🔥", font=title_font, fg="#FF5733", bg="#2d2d2d")
title_label.pack(padx=20, pady=20)

frame_principal = tk.Frame(root, bg="#2d2d2d")
frame_principal.pack(pady=10)

btn_crear_sensor = tk.Button(frame_principal, text="Crear Sensor", command=crear_sensor, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
btn_crear_sensor.pack(side=tk.LEFT, padx=5, pady=5)

btn_modificar_sensor = tk.Button(frame_principal, text="Modificar Sensor", command=modificar_sensor, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
btn_modificar_sensor.pack(side=tk.LEFT, padx=5, pady=5)

btn_eliminar_sensor = tk.Button(frame_principal, text="Eliminar Sensor", command=eliminar_sensor, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
btn_eliminar_sensor.pack(side=tk.LEFT, padx=5, pady=5)

frame_sensores = tk.Frame(root, bg="#2d2d2d", padx=10, pady=10)
frame_sensores.pack(expand=True, fill='both')

cargar_sensores()

root.mainloop()
