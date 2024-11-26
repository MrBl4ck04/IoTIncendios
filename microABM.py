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
microcontroladores_win = None
agregar_micro_win = None
modificar_micro_win = None

# Función para mostrar microcontroladores
def mostrar_microcontroladores():
    global microcontroladores_win
    if microcontroladores_win is not None:
        microcontroladores_win.destroy()

    microcontroladores_win = tk.Toplevel()
    microcontroladores_win.title("Gestión de Microcontroladores")
    microcontroladores_win.geometry("600x400")
    microcontroladores_win.configure(bg="#2d2d2d")

    # Crear tabla para mostrar microcontroladores
    cols = ("ID", "Nombre", "Ubicación")
    tree = ttk.Treeview(microcontroladores_win, columns=cols, show='headings')
    for col in cols:
        tree.heading(col, text=col)

    tree.pack(expand=True, fill='both', padx=10, pady=10)
    scrollbar = ttk.Scrollbar(microcontroladores_win, orient="vertical", command=tree.yview)
    scrollbar.pack(side=tk.RIGHT, fill='y')
    tree.configure(yscroll=scrollbar.set)

    # Obtener y mostrar datos de microcontroladores
    cursor.execute("""
        SELECT m.microcontroladores_id, m.nombre, u.descripcion 
        FROM microcontroladores m 
        JOIN ubicaciones u ON m.ubicaciones_id = u.ubicaciones_id
    """)
    datos = cursor.fetchall()
    for dato in datos:
        tree.insert("", "end", values=dato)

    # Menú contextual para modificar y eliminar
    menu_contextual = tk.Menu(microcontroladores_win, tearoff=0)
    menu_contextual.add_command(label="Modificar", command=modificar_microcontrolador)
    menu_contextual.add_command(label="Eliminar", command=eliminar_microcontrolador)

    def mostrar_menu(event):
        item = tree.identify_row(event.y)
        if item:
            global selected_id
            selected_id = tree.item(item, "values")[0]
            menu_contextual.post(event.x_root, event.y_root)

    tree.bind("<Button-3>", mostrar_menu)

    # Botón para agregar un nuevo microcontrolador
    btn_agregar_micro = tk.Button(microcontroladores_win, text="Agregar Microcontrolador", command=agregar_microcontrolador, bg="#2082AA", fg="white", font=('Arial', 10, 'bold'))
    btn_agregar_micro.pack(pady=10)

    # Botón para agregar una nueva ubicación
    btn_agregar_ubicacion = tk.Button(microcontroladores_win, text="Agregar Ubicación", command=lambda: agregar_ubicacion(None), bg="#2082AA", fg="white", font=('Arial', 10, 'bold'))
    btn_agregar_ubicacion.pack(pady=10)

# Función para agregar un nuevo microcontrolador
def agregar_microcontrolador():
    global agregar_micro_win
    if agregar_micro_win is not None:
        agregar_micro_win.destroy()

    agregar_micro_win = tk.Toplevel()
    agregar_micro_win.title("Agregar Microcontrolador")
    agregar_micro_win.configure(bg="#2d2d2d")

    tk.Label(agregar_micro_win, text="Nombre", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=0, column=0, padx=10, pady=10)
    entry_nombre = tk.Entry(agregar_micro_win, font=('Helvetica', 10), width=20)
    entry_nombre.grid(row=0, column=1, padx=10, pady=10)

    tk.Label(agregar_micro_win, text="Ubicación", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=1, column=0, padx=10, pady=10)
    ubicacion_combobox = ttk.Combobox(agregar_micro_win, font=('Helvetica', 10), width=18)
    actualizar_ubicaciones(ubicacion_combobox)
    ubicacion_combobox.grid(row=1, column=1, padx=10, pady=10)

    def guardar_microcontrolador():
        nombre = entry_nombre.get()
        ubicacion = ubicacion_combobox.get()
        ubicacion_id = ubicaciones.get(ubicacion)

        if not nombre or ubicacion_id is None:
            messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
            return

        cursor.execute("INSERT INTO microcontroladores (nombre, ubicaciones_id) VALUES (%s, %s)", (nombre, ubicacion_id))
        conn.commit()
        agregar_micro_win.destroy()
        mostrar_microcontroladores()

    btn_guardar = tk.Button(agregar_micro_win, text="Guardar", command=guardar_microcontrolador, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
    btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

# Función para cargar ubicaciones en el Combobox
ubicaciones = {}
def actualizar_ubicaciones(combobox):
    cursor.execute("SELECT ubicaciones_id, descripcion FROM ubicaciones")
    global ubicaciones
    ubicaciones = {desc: uid for uid, desc in cursor.fetchall()}
    combobox["values"] = list(ubicaciones.keys())

# Función para agregar una nueva ubicación
def agregar_ubicacion(combobox=None):
    nueva_ubicacion = simpledialog.askstring("Nueva Ubicación", "Ingrese la descripción de la nueva ubicación:")
    if nueva_ubicacion:
        cursor.execute("INSERT INTO ubicaciones (descripcion) VALUES (%s)", (nueva_ubicacion,))
        conn.commit()
        if combobox:
            actualizar_ubicaciones(combobox)
            combobox.set(nueva_ubicacion)
        messagebox.showinfo("Éxito", "Ubicación agregada exitosamente.")

# Función para eliminar microcontrolador
def eliminar_microcontrolador():
    confirmacion = messagebox.askyesno("Confirmar eliminación", "¿Está seguro de que desea eliminar este microcontrolador?")
    if confirmacion:
        cursor.execute("DELETE FROM microcontroladores WHERE microcontroladores_id = %s", (selected_id,))
        conn.commit()
        mostrar_microcontroladores()

# Función para modificar microcontrolador
def modificar_microcontrolador():
    global modificar_micro_win
    if modificar_micro_win is not None:
        modificar_micro_win.destroy()

    modificar_micro_win = tk.Toplevel()
    modificar_micro_win.title("Modificar Microcontrolador")
    modificar_micro_win.configure(bg="#2d2d2d")

    cursor.execute("SELECT nombre, ubicaciones_id FROM microcontroladores WHERE microcontroladores_id = %s", (selected_id,))
    nombre_actual, ubicacion_actual_id = cursor.fetchone()

    tk.Label(modificar_micro_win, text="Nombre", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=0, column=0, padx=10, pady=10)
    entry_nombre = tk.Entry(modificar_micro_win, font=('Helvetica', 10), width=20)
    entry_nombre.insert(0, nombre_actual)
    entry_nombre.grid(row=0, column=1, padx=10, pady=10)

    tk.Label(modificar_micro_win, text="Ubicación", font=('Helvetica', 10), fg="white", bg="#2d2d2d").grid(row=1, column=0, padx=10, pady=10)
    ubicacion_combobox = ttk.Combobox(modificar_micro_win, font=('Helvetica', 10), width=18)
    actualizar_ubicaciones(ubicacion_combobox)
    ubicacion_actual = [desc for desc, uid in ubicaciones.items() if uid == ubicacion_actual_id][0]
    ubicacion_combobox.set(ubicacion_actual)
    ubicacion_combobox.grid(row=1, column=1, padx=10, pady=10)

    def guardar_cambios():
        nombre = entry_nombre.get()
        ubicacion = ubicacion_combobox.get()
        ubicacion_id = ubicaciones.get(ubicacion)

        if not nombre or ubicacion_id is None:
            messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
            return

        cursor.execute("UPDATE microcontroladores SET nombre = %s, ubicaciones_id = %s WHERE microcontroladores_id = %s", (nombre, ubicacion_id, selected_id))
        conn.commit()
        modificar_micro_win.destroy()
        mostrar_microcontroladores()

    btn_guardar = tk.Button(modificar_micro_win, text="Guardar Cambios", command=guardar_cambios, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'))
    btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

#mostrar_microcontroladores()
