# Simulador SENIAT (Tesis Francisco)

Proyecto de tesis para la simulación del portal del SENIAT.

## Resumen de Cambios Recientes
- **Recuperación de Contraseña**: Se implementó el flujo completo de recuperación de contraseña con envío de correo electrónico.
  - Vistas: `resources/views/auth/password_recovery.php`
  - Controladores: `src/Modules/Auth/Controllers/PasswordRecoveryController.php`
  - Servicios: `src/Modules/Auth/Services/PasswordRecoveryService.php`
  - Modelos: `src/Modules/Auth/Models/PasswordRecoveryModel.php`
- **Registro de Usuarios**: Mejoras en la validación y UI del registro.
- **Simulador**: Correcciones en la navegación y enlaces del simulador.

## Configuración en Entorno de Desarrollo (XAMPP)

1. **Clonar el Repositorio**:
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   cd tesis_francisco
   ```

2. **Configuración de Base de Datos**:
   - Abrir phpMyAdmin (http://localhost/phpmyadmin).
   - Crear una base de datos llamada `spdss`.
   - Importar el archivo `spdss.sql` ubicado en la raíz del proyecto.

3. **Configuración del Servidor Web (Apache)**:
   - Asegurarse de que la carpeta del proyecto esté en `htdocs` (ej: `C:\xampp\htdocs\tesis_francisco`).
   - El proyecto está configurado para funcionar en `http://localhost/tesis_francisco/`.
   - **Nota**: Si usas VirtualHost, ajusta la configuración `base_url()` en `src/Core/Config.php` (si aplica).

4. **Configuración de Correo (SMTP)**:
   - Copiar el archivo `.env.example` a `.env` (si no existe).
   - Configurar las variables `SMTP_*` en el archivo `.env` con tus credenciales (ej: Gmail, Mailtrap).

5. **Dependencias (Composer)**:
   - Si el proyecto usa librerías externas, ejecutar:
     ```bash
     composer install
     ```

6. **Ejecutar**:
   - Abrir el navegador en `http://localhost/tesis_francisco/`.
