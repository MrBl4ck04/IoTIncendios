import tkinter as tk
from tkinter import messagebox
from tkinter import font
import mysql.connector
import subprocess 

# Conexión a la base de datos MySQL
conn = mysql.connector.connect(
    host="localhost",  
    user="root",       
    password="",       
    database="onfireBD" 
)
cursor = conn.cursor()

# Función para verificar el inicio de sesión
def login():
    username = entry_username.get()
    password = entry_password.get()

    if not username or not password:
        messagebox.showwarning("Campos incompletos", "Por favor complete todos los campos.")
        return

    query = "SELECT * FROM usuarios WHERE usuario = %s AND password = %s"
    cursor.execute(query, (username, password))
    result = cursor.fetchone()

    if result:
        messagebox.showinfo("Inicio de sesión exitoso", f"Bienvenido, {username}!")
        subprocess.Popen(["python", "usersABM.py"])  # Abre el archivo userABM.py
        root.destroy()
    else:
        messagebox.showerror("Error de inicio de sesión", "Usuario o contraseña incorrectos.")

# Configuración de la ventana de inicio de sesión
root = tk.Tk()
root.title("On Fire - Inicio de Sesión")
root.geometry("400x400")
root.configure(bg="#2d2d2d")  # Color de fondo oscuro para un estilo moderno

# Título estilizado
title_font = font.Font(family="Helvetica", size=24, weight="bold")
title_label = tk.Label(root, text="🔥 On Fire 🔥", font=title_font, fg="#FF5733", bg="#2d2d2d")
title_label.pack(pady=20)

# Marco contenedor de los campos
frame = tk.Frame(root, bg="#404040", padx=20, pady=20, relief="ridge", bd=2)
frame.pack(pady=20)

# Estilo de etiquetas y campos de entrada
lbl_font = font.Font(family="Helvetica", size=12)
entry_font = font.Font(family="Helvetica", size=10)

lbl_username = tk.Label(frame, text="Usuario:", font=lbl_font, fg="white", bg="#404040")
lbl_username.grid(row=0, column=0, padx=10, pady=10)
entry_username = tk.Entry(frame, font=entry_font, width=20)
entry_username.grid(row=0, column=1, padx=10, pady=10)

lbl_password = tk.Label(frame, text="Contraseña:", font=lbl_font, fg="white", bg="#404040")
lbl_password.grid(row=1, column=0, padx=10, pady=10)
entry_password = tk.Entry(frame, show="*", font=entry_font, width=20)
entry_password.grid(row=1, column=1, padx=10, pady=10)

# Botón para iniciar sesión estilizado
btn_login = tk.Button(
    frame, text="Iniciar sesión", font=lbl_font, fg="white", bg="#FF5733", 
    width=15, height=1, command=login, activebackground="#C70039", relief="flat"
)
btn_login.grid(row=2, column=0, columnspan=2, pady=20)

root.mainloop()
