import tkinter as tk
from tkinter import ttk, messagebox, font
import mysql.connector
import subprocess


# Conectar a la base de datos MySQL
conn = mysql.connector.connect(
    host="localhost", 
    user="root",      
    password="",       
    database="onfireBD"
)
cursor = conn.cursor()

# Variables globales para ventanas abiertas
usuarios_win = None
modificar_win = None
agregar_win = None

# Función para abrir la ventana y agregar un nuevo usuario
def agregar_usuario():
    global agregar_win
    if agregar_win is not None:
        agregar_win.destroy()

    agregar_win = tk.Toplevel(root)
    agregar_win.title("Agregar Usuario")
    agregar_win.configure(bg="#2d2d2d")

   # Crear campos del formulario
    lbl_usuario = tk.Label(agregar_win, text="Usuario", font=('Helvetica', 10), fg="white", bg="#2d2d2d")
    lbl_usuario.grid(row=0, column=0, padx=10, pady=10)
    entry_usuario = tk.Entry(agregar_win, font=('Helvetica', 10), width=20)
    entry_usuario.grid(row=0, column=1, padx=10, pady=10)

    lbl_password = tk.Label(agregar_win, text="Contraseña", font=('Helvetica', 10), fg="white", bg="#2d2d2d")
    lbl_password.grid(row=1, column=0, padx=10, pady=10)
    entry_password = tk.Entry(agregar_win, show="*", font=('Helvetica', 10), width=20)
    entry_password.grid(row=1, column=1, padx=10, pady=10)

    def guardar_usuario():
        usuario = entry_usuario.get()
        password = entry_password.get()

        if not usuario or not password:
            messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
            return

        query = "INSERT INTO usuarios (usuario, password) VALUES (%s, %s)"
        cursor.execute(query, (usuario, password))
        conn.commit()

        messagebox.showinfo("Éxito", "Usuario agregado exitosamente.")
        agregar_win.destroy()
        mostrar_usuarios()

    btn_guardar = tk.Button(agregar_win, text="Guardar Usuario", command=guardar_usuario, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
    btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

# Función para mostrar la tabla de usuarios
def mostrar_usuarios():
    global usuarios_win
    if usuarios_win is not None:
        usuarios_win.destroy()

    usuarios_win = tk.Toplevel(root)
    usuarios_win.title("Usuarios")
    usuarios_win.configure(bg="#2d2d2d")

    frame = tk.Frame(usuarios_win, bg="#404040", padx=10, pady=10)
    frame.pack(expand=True, fill='both')

    btn_agregar = tk.Button(usuarios_win, text="Agregar Usuario", command=agregar_usuario, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
    btn_agregar.pack(pady=10)

    cols = ("ID", "Usuario", "Contraseña")
    tree = ttk.Treeview(frame, columns=cols, show='headings')

    for col in cols:
        tree.heading(col, text=col)

    tree.pack(side=tk.LEFT, expand=True, fill='both')

    scrollbar = ttk.Scrollbar(frame, orient="vertical", command=tree.yview)
    scrollbar.pack(side=tk.RIGHT, fill='y')
    tree.configure(yscroll=scrollbar.set)

    cursor.execute("SELECT * FROM usuarios")
    usuarios = cursor.fetchall()

    def eliminar_usuario(usuario_id):
        if messagebox.askyesno("Confirmar", f"¿Estás seguro de que deseas eliminar este usuario? (Id:{usuario_id})"):
            cursor.execute("DELETE FROM usuarios WHERE id = %s", (usuario_id,))
            conn.commit()
            mostrar_usuarios()
            messagebox.showinfo("Éxito", "Usuario eliminado exitosamente.")

    def modificar_usuario(usuario_id):
        global modificar_win
        if modificar_win is not None:
            modificar_win.destroy()

        modificar_win = tk.Toplevel(root)
        modificar_win.title("Modificar Usuario")
        modificar_win.configure(bg="#2d2d2d")

        cursor.execute("SELECT * FROM usuarios WHERE id = %s", (usuario_id,))
        usuario = cursor.fetchone()

        lbl_usuario = tk.Label(modificar_win, text="Usuario", font=('Helvetica', 10), fg="white", bg="#2d2d2d")
        lbl_usuario.grid(row=0, column=0, padx=10, pady=10)
        entry_usuario = tk.Entry(modificar_win, font=('Helvetica', 10), width=20)
        entry_usuario.insert(0, usuario[1])
        entry_usuario.grid(row=0, column=1, padx=10, pady=10)

        lbl_password = tk.Label(modificar_win, text="Contraseña", font=('Helvetica', 10), fg="white", bg="#2d2d2d")
        lbl_password.grid(row=1, column=0, padx=10, pady=10)
        entry_password = tk.Entry(modificar_win, show="*", font=('Helvetica', 10), width=20)
        entry_password.insert(0, usuario[2])
        entry_password.grid(row=1, column=1, padx=10, pady=10)

        def guardar_modificacion():
            nuevo_usuario = entry_usuario.get()
            nueva_password = entry_password.get()

            if not nuevo_usuario or not nueva_password:
                messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
                return

            query = "UPDATE usuarios SET usuario=%s, password=%s WHERE id=%s"
            cursor.execute(query, (nuevo_usuario, nueva_password, usuario_id))
            conn.commit()

            messagebox.showinfo("Éxito", "Usuario modificado exitosamente.")
            modificar_win.destroy()
            mostrar_usuarios()

        btn_guardar = tk.Button(modificar_win, text="Guardar cambios", command=guardar_modificacion, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
        btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

    for usuario in usuarios:
        tree.insert("", "end", values=(usuario[0], usuario[1], usuario[2]))

        botones_frame = tk.Frame(frame,  bg="#404040")
        botones_frame.pack(fill='x')

        btn_modificar = tk.Button(botones_frame, text="Modificar", command=lambda id=usuario[0]: modificar_usuario(id), bg="#2082AA", fg="white", font=('Arial', 6, 'bold'))
        btn_modificar.pack(side=tk.LEFT, padx=5, pady=5)

        btn_eliminar = tk.Button(botones_frame, text="Eliminar", command=lambda id=usuario[0]: eliminar_usuario(id), bg="#FF5733", fg="white", font=('Arial', 6, 'bold'))
        btn_eliminar.pack(side=tk.LEFT, padx=5, pady=5)
# Función para abrir la ventana de "Dashboard"
def abrir_dashboardCEO2():
    try:
        subprocess.Popen(["python", "DashCEO2.py"])
    except Exception as e:
        messagebox.showerror("Error", f"No se pudo abrir la ventana de datos: {e}")

def abrir_dashboardHumedad():
    try:
        subprocess.Popen(["python", "DashHumedad.py"])
    except Exception as e:
        messagebox.showerror("Error", f"No se pudo abrir la ventana de datos: {e}")


def abrir_dashboardHumo():
    try:
        subprocess.Popen(["python", "DashHumo.py"])
    except Exception as e:
        messagebox.showerror("Error", f"No se pudo abrir la ventana de datos: {e}")

def abrir_dashboardTemp():
    try:
        subprocess.Popen(["python", "DashTemp.py"])
    except Exception as e:
        messagebox.showerror("Error", f"No se pudo abrir la ventana de datos: {e}")

# Función para abrir la ventana de "Datos"
def abrir_datos():
    try:
        subprocess.Popen(["python", "datosABM.py"])
    except Exception as e:
        messagebox.showerror("Error", f"No se pudo abrir la ventana de datos: {e}")


# Ventana principal
root = tk.Tk()
root.title("On Fire - Gestión de Usuarios")
root.configure(bg="#2d2d2d")

# Título estilizado
title_font = font.Font(family="Helvetica", size=24, weight="bold")
title_label = tk.Label(root, text="🔥 On Fire - Gestión 🔥", font=title_font, fg="#FF5733", bg="#2d2d2d")
title_label.pack(padx=20, pady=20)

# Botones de navegación
btn_dashboard = tk.Button(
    root, text="Dashboard CO2", command=abrir_dashboardCEO2, bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039"
)
btn_dashboard.pack(pady=10)

btn_dashboard = tk.Button(
    root, text="Dashboard Humedad", command=abrir_dashboardHumedad, bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039"
)
btn_dashboard.pack(pady=10)

btn_dashboard = tk.Button(
    root, text="Dashboard Humo", command=abrir_dashboardHumo, bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039"
)
btn_dashboard.pack(pady=10)

btn_dashboard = tk.Button(
    root, text="Dashboard Temperatura", command=abrir_dashboardTemp, bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039"
)
btn_dashboard.pack(pady=10)

btn_datos = tk.Button(
    root, text="Datos", command=abrir_datos, bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039"
)
btn_datos.pack(pady=10)

btn_usuarios = tk.Button(
    root, text="Usuarios", command=mostrar_usuarios, bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat",
    activebackground="#C70039"
)
btn_usuarios.pack(pady=20)

root.mainloop()
