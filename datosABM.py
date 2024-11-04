import tkinter as tk
from tkinter import font
from tkinter import ttk, messagebox, filedialog
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

# Función para seleccionar sensor y mostrar sus datos
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

    # Crear tabla
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

    # Agregar botones de modificar y eliminar
    for item in tree.get_children():
        item_values = tree.item(item, 'values')
        dato_id = item_values[0]
        
        btn_modificar = tk.Button(frame, text="Modificar", command=lambda id=dato_id: modificar_dato(id), bg="#2082AA", fg="white", font=('Arial', 6, 'bold'))
        btn_modificar.pack(side=tk.LEFT, padx=5, pady=5)

        btn_eliminar = tk.Button(frame, text="Eliminar", command=lambda id=dato_id: eliminar_dato(id), bg="#FF5733", fg="white", font=('Arial', 6, 'bold'))
        btn_eliminar.pack(side=tk.LEFT, padx=5, pady=5)

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

# Ventana principal
root = tk.Tk()
root.title("On Fire - Gestión de Sensores")
root.configure(bg="#2d2d2d")

# Título estilizado
title_font = font.Font(family="Helvetica", size=24, weight="bold")
title_label = tk.Label(root, text="🔥 On Fire - Gestión de Sensores 🔥", font=title_font, fg="#FF5733", bg="#2d2d2d")
title_label.pack(padx=20, pady=20)

# Botones para cada sensor
for sensor in ["co2", "humedad", "humo", "temperatura"]:
    btn_sensor = tk.Button(
        root, text=f"Datos de {sensor.capitalize()}", command=lambda s=sensor: mostrar_datos_sensor(s),
        bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", activebackground="#C70039"
    )
    btn_sensor.pack(pady=10)

root.mainloop()
