import tkinter as tk
from tkinter import ttk, messagebox, font
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
usuarios_win = None
modificar_win = None
agregar_win = None
selected_id = None

# Función para abrir la ventana y agregar un nuevo usuario
def agregar_usuario():
    global agregar_win
    if agregar_win is not None:
        agregar_win.destroy()

    agregar_win = tk.Toplevel()
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

    usuarios_win = tk.Toplevel()
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

    # Función para eliminar usuario
    def eliminar_usuario():
        if messagebox.askyesno("Confirmar", f"¿Estás seguro de que deseas eliminar este usuario? (Id:{selected_id})"):
            cursor.execute("DELETE FROM usuarios WHERE id = %s", (selected_id,))
            conn.commit()
            mostrar_usuarios()
            messagebox.showinfo("Éxito", "Usuario eliminado exitosamente.")

    # Función para modificar usuario
    def modificar_usuario():
        global modificar_win
        if modificar_win is not None:
            modificar_win.destroy()

        modificar_win = tk.Toplevel()
        modificar_win.title("Modificar Usuario")
        modificar_win.configure(bg="#2d2d2d")

        cursor.execute("SELECT * FROM usuarios WHERE id = %s", (selected_id,))
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
            cursor.execute(query, (nuevo_usuario, nueva_password, selected_id))
            conn.commit()

            messagebox.showinfo("Éxito", "Usuario modificado exitosamente.")
            modificar_win.destroy()
            mostrar_usuarios()

        btn_guardar = tk.Button(modificar_win, text="Guardar cambios", command=guardar_modificacion, bg="#FF5733", fg="white", font=('Arial', 11, 'bold'), relief="raised", bd=5)
        btn_guardar.grid(row=2, column=0, columnspan=2, pady=10)

    # Menú contextual para modificar y eliminar
    menu_contextual = tk.Menu(usuarios_win, tearoff=0)
    menu_contextual.add_command(label="Modificar", command=modificar_usuario)
    menu_contextual.add_command(label="Eliminar", command=eliminar_usuario)

    def mostrar_menu(event):
        item = tree.identify_row(event.y)
        if item:
            global selected_id
            selected_id = tree.item(item, "values")[0]
            menu_contextual.post(event.x_root, event.y_root)

    tree.bind("<Button-3>", mostrar_menu)

    for usuario in usuarios:
        tree.insert("", "end", values=(usuario[0], usuario[1], usuario[2]))

# Mostrar la ventana de usuarios al inicio
#mostrar_usuarios()

