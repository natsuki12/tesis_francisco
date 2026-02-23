# SPDSS — Sistema de Práctica de Declaración Sucesoral SENIAT

Proyecto de tesis: simulador interactivo del portal SENIAT para la práctica de declaraciones sucesorales. Desarrollado con PHP 8.2, MariaDB y arquitectura MVC personalizada.

---

## Actualizaciones Recientes

### Dashboards por Rol
- **Home Estudiante** (`/home`) — Dashboard con estadísticas, acciones rápidas, guía paso a paso y marco legal.
- **Home Profesor** (`/home`) — Dashboard con estadísticas de estudiantes, tarjetas de acción y paneles de actividad.
- **Home Administrador** (`/home`) — Dashboard con estado del sistema, bitácora de actividad y gestión de usuarios.
- La ruta `/home` renderiza automáticamente el dashboard correcto según el `role_id` del usuario.

### Sistema de Bitácora de Accesos
- Modelo `BitacoraModel` (`src/Core/BitacoraModel.php`) integrado en login y logout.
- Registra 4 eventos: `login_success`, `login_failed`, `user_blocked`, `logout`.
- Captura automáticamente: IP, User-Agent y timestamp.
- Protegido con `try/catch` para no interrumpir el flujo de autenticación.

### Layout Unificado
- Todos los dashboards usan `logged_layout.php` con sidebar y header compartidos.
- El header muestra el nombre del usuario logueado dinámicamente.
- Sidebar con navegación por rol: Admin (Gestión de Usuarios, Profesores Autorizados, Reportes), Profesor (Declaraciones, Historial, Estudiantes, Calificaciones, Marco Legal), Estudiante (Simulador, Perfil, Historial).

### Protección de Rutas
- Middleware `$requireAuth` para rutas protegidas (requiere sesión activa).
- Middleware `$requireRole` para rutas exclusivas por rol.
- Redirección automática a `/login` si no hay sesión.

### Recuperación de Contraseña
- Flujo completo con envío de correo electrónico (SMTP).
- Vistas, controladores, servicios y modelos dedicados.

---

## Requisitos Previos

| Software | Versión Recomendada |
|----------|-------------------|
| XAMPP | 8.2.x o superior |
| PHP | 8.2+ |
| MariaDB / MySQL | 10.4+ |
| Composer | 2.x |
| Navegador | Chrome, Firefox o Edge actualizado |

---

## Instalación en XAMPP

### 1. Clonar el repositorio

```bash
cd C:\xampp\htdocs
git clone <URL_DEL_REPOSITORIO> tesis_francisco
cd tesis_francisco
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar la base de datos

1. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
2. Abrir phpMyAdmin en `http://localhost/phpmyadmin`.
3. Crear una base de datos llamada **`spdss`** con cotejamiento `utf8mb4_unicode_ci`.
4. Importar el archivo `spdss.sql` ubicado en la raíz del proyecto.

> La base de datos incluye las tablas: `users`, `roles`, `personas`, `estudiantes`, `profesores`, `profesores_autorizados`, `carreras`, `materias`, `secciones`, `periodos`, `inscripciones`, `password_resets`, `bitacora_accesos` y `tipos_eventos`.

### 4. Configurar el archivo `.env`

Copiar `.env.example` a `.env` y ajustar las variables:

```env
APP_DEBUG=true
APP_URL=http://localhost/tesis_francisco

DB_HOST=127.0.0.1
DB_NAME=spdss
DB_USER=root
DB_PASS=

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu_correo@gmail.com
SMTP_PASS=tu_contraseña_de_aplicacion
SMTP_FROM=tu_correo@gmail.com
SMTP_FROM_NAME="SPDSS - Simulador SENIAT"
```

### 5. Verificar Apache

- Asegurarse de que `mod_rewrite` esté habilitado en Apache.
- La carpeta del proyecto debe estar en `C:\xampp\htdocs\tesis_francisco`.

### 6. Acceder al sistema

Abrir el navegador en:

```
http://localhost/tesis_francisco/
```

---

## Estructura del Proyecto

```
tesis_francisco/
├── public/                  # Punto de entrada y assets públicos
│   ├── index.php            # Front controller
│   ├── .htaccess             # Reescritura de URLs
│   └── assets/
│       ├── css/             # Estilos por módulo (admin, professor, student, etc.)
│       ├── js/              # Scripts por módulo
│       └── img/             # Imágenes y branding
├── resources/views/         # Vistas PHP
│   ├── admin/               # Vistas del administrador
│   ├── professor/           # Vistas del profesor
│   ├── student/             # Vistas del estudiante
│   ├── auth/                # Login, registro, recuperación
│   ├── layouts/             # Layouts (logged_layout, guest_layout)
│   ├── partials/            # Header, sidebar, footer
│   └── simulator/           # Vistas del simulador SENIAT
├── routes/
│   └── web.php              # Definición de rutas
├── src/
│   ├── Core/                # Kernel: App, DB, Router, Config, BitacoraModel
│   └── Modules/Auth/        # Módulo de autenticación (Controllers, Services, Models)
├── storage/logs/            # Logs de errores
├── spdss.sql                # Dump de la base de datos
└── .env                     # Variables de entorno (no se sube al repo)
```

---

## Roles del Sistema

| ID | Rol | Descripción |
|----|-----|------------|
| 1 | Admin | Administrador del sistema y configuración |
| 2 | Profesor | Docente evaluador y supervisor |
| 3 | Estudiante | Estudiante cursante de la materia |
