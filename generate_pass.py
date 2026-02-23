import bcrypt

password = "Superadmin123$"

# Generar una "sal" y el hash (automáticamente maneja la seguridad)
# Nota: PHP usa prefijo $2y$, Python moderno usa $2b$. 
# PHP entiende ambos perfectamente.
hashed = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt())

print(hashed.decode('utf-8'))