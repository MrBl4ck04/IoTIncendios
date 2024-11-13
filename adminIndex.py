import tkinter as tk
from tkinter import ttk, messagebox, font
import mysql.connector
import sensoresABM as admin_sensores  # Este es tu archivo para gestión de sensores
import microABM as admin_microcontroladores  # Este es tu archivo para gestión de microcontroladores
import usersABM as admin_usuarios  # Este es tu archivo para gestión de usuarios
import subprocess

# Conectar a la base de datos MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="onfireBD"
)
cursor = conn.cursor()

# Función para mostrar la ventana de gestión de sensores
def gestionar_sensores():
    admin_sensores.mostrar_sensores()  # Esto solo se ejecutará cuando se haga clic en el botón

# Función para mostrar la ventana de gestión de sensores
def gestionar_usuarios():
    admin_usuarios.mostrar_usuarios()  # Esto solo se ejecutará cuando se haga clic en el botón

# Función para mostrar la ventana de gestión de microcontroladores
def gestionar_microcontroladores():
    admin_microcontroladores.mostrar_microcontroladores()  # Esto solo se ejecutará cuando se haga clic en el botón

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




# Crear la ventana principal (root) del admin
root = tk.Tk()
root.title("🔥 On Fire - Gestión 🔥")
root.configure(bg="#2d2d2d")

# Título estilizado
title_font = font.Font(family="Helvetica", size=24, weight="bold")
title_label = tk.Label(root, text="🔥 On Fire - Gestión 🔥", font=title_font, fg="#FF5733", bg="#2d2d2d")
title_label.pack(padx=20, pady=20)

# Botones de navegación

btn_microcontroladores = tk.Button(root, text="Gestionar Microcontroladores", command=gestionar_microcontroladores,  bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039")
btn_microcontroladores.pack(pady=10)

btn_usuarios = tk.Button(root, text="Gestionar Usuarios", command=gestionar_usuarios,  bg="#FF5733", fg="white", font=('Helvetica', 16, 'bold'), relief="flat", 
    activebackground="#C70039")
btn_usuarios.pack(pady=10)


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


# Ejecutar la aplicación
root.mainloop()
