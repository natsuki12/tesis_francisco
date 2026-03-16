# SPDSS — Sistema de Práctica de Declaración Sucesoral SENIAT

Proyecto de tesis: simulador interactivo del portal SENIAT para la práctica de declaraciones sucesorales. Desarrollado con PHP 8.2, MariaDB y arquitectura MVC personalizada.

---

## Requisitos Previos

| Software | Versión Recomendada |
|----------|-------------------|
| XAMPP | 8.2.x o superior |
| PHP | 8.2+ |
| MariaDB / MySQL | 10.4+ |
| Composer | 2.x |
| Navegador | Chrome, Firefox o Edge actualizado |

### Extensiones PHP requeridas

Abrir `C:\xampp\php\php.ini` y verificar que las siguientes extensiones estén habilitadas (sin `;` al inicio):

```ini
extension=gd        ; Requerida por mPDF para generación de PDF
extension=mbstring  ; Requerida para normalización de texto y mPDF
extension=pdo_mysql ; Conexión a base de datos
```

> Reiniciar Apache después de modificar `php.ini`.

---

## Instalación

### 1. Clonar el repositorio

```bash
cd C:\xampp\htdocs
git clone https://github.com/natsuki12/tesis_francisco.git tesis_francisco
cd tesis_francisco
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar la base de datos

1. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
2. Abrir phpMyAdmin en `http://localhost/phpmyadmin`.
3. Crear una base de datos llamada **`spdss`** con cotejamiento `utf8mb4_unicode_ci`.
4. Importar el archivo SQL ubicado en:
   ```
   spdss db info schema/spdss.sql
   ```

> La base de datos incluye las tablas del sistema: `users`, `roles`, `sim_personas`, `sim_caso_estudio`, `sim_caso_bienes_muebles`, `sim_caso_bienes_inmuebles`, `sim_caso_pasivos_deuda`, `sim_caso_exenciones`, `sim_caso_exoneraciones`, catálogos (`sim_cat_*`), `bitacora_accesos`, entre otras.

### 4. Configurar el archivo `.env`

Crear un archivo `.env` en la raíz del proyecto con el siguiente contenido:

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

> ⚠️ El archivo `.env` NO se sube al repositorio (está en `.gitignore`). Cada instalación debe crear el suyo.

### 5. Verificar Apache

- Asegurarse de que `mod_rewrite` esté habilitado en Apache.
- La carpeta del proyecto debe estar en `C:\xampp\htdocs\tesis_francisco`.

### 6. Acceder al sistema

```
http://localhost/tesis_francisco/
```

---

## Estructura del Proyecto

```
tesis_francisco/
├── public/                  # Punto de entrada y assets públicos
│   ├── index.php            # Front controller
│   ├── .htaccess            # Reescritura de URLs
│   └── assets/
│       ├── css/             # Estilos por módulo
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
│       ├── seniat_actual/   # Vistas que replican el portal SENIAT
│       └── pdf/             # Plantillas de reportes PDF
├── routes/
│   └── web.php              # Definición de rutas
├── src/
│   ├── Core/                # Kernel: App, DB, Router, Mailer, Csrf
│   └── Modules/
│       ├── Auth/            # Autenticación (Controllers, Services, Models)
│       ├── Student/         # Área del estudiante
│       ├── Simulator/       # Simulador SENIAT
│       │   ├── Controllers/ # SucesionController, PdfReportController
│       │   └── Services/    # BorradorService, DeclaracionComparador, TributoCalculator
│       ├── Professor/       # Área del profesor
│       └── Admin/           # Área del administrador
├── storage/logs/            # Logs de errores (app_errors.log)
├── spdss db info schema/    # Dump de la base de datos y esquema
│   └── spdss.sql
├── .env                     # Variables de entorno (NO se sube al repo)
├── .gitignore
├── composer.json
└── README.md
```

---

## Dependencias PHP (Composer)

| Paquete | Versión | Propósito |
|---------|---------|----------|
| `phpmailer/phpmailer` | ^7.0 | Envío de correos SMTP (recuperación de contraseña, notificaciones) |
| `mpdf/mpdf` | ^8.3 | Generación de reportes PDF (declaraciones, comparaciones) |

> La carga de variables de entorno la realiza `App::loadEnv()` de forma nativa (sin dependencia de `vlucas/phpdotenv`).

---

## Roles del Sistema

| ID | Rol | Descripción |
|----|-----|------------|
| 1 | Admin | Administrador del sistema y configuración |
| 2 | Profesor | Docente evaluador y supervisor |
| 3 | Estudiante | Estudiante cursante de la materia |

---

## Funcionalidades Principales

### Dashboards por Rol
- **Home Estudiante** (`/home`) — Dashboard con estadísticas, acciones rápidas y guía paso a paso.
- **Home Profesor** (`/home`) — Dashboard con estadísticas de estudiantes y paneles de actividad.
- **Home Administrador** (`/home`) — Dashboard con estado del sistema y gestión de usuarios.
- La ruta `/home` renderiza automáticamente el dashboard según el `role_id` del usuario.

### Simulador SENIAT
- Formularios que replican el portal real del SENIAT para declaraciones sucesorales.
- Secciones: Datos del Causante, Herederos, Bienes Inmuebles, Bienes Muebles (por categoría), Pasivos, Exenciones, Exoneraciones, Tipo de Herencia, Autoliquidación.
- Guardado automático en borrador (JSON en `localStorage`).

### Reporte PDF de Comparación
- Genera un reporte PDF comparando la declaración del estudiante vs. los datos correctos del caso.
- **Matching 2-pass**: emparejamiento por score (descripción normalizada + valor) con fallback ordinal.
- **3 tipos de presentación**:
  - ✓ **Correcto** — Campo coincide con el esperado.
  - ○ **Omitido** — Item del caso no ingresado por el estudiante (muestra todos los campos esperados).
  - △ **De Más** — Item ingresado que no existe en el caso (muestra resumen descriptivo).
- Secciones: Herederos, Tipo de Herencia, Bienes Inmuebles, Bienes Muebles, Pasivos, Exenciones, Exoneraciones, Autoliquidación.
- Score global y resumen por sección con barras de progreso.

### Sistema de Bitácora
- Registra eventos: `login_success`, `login_failed`, `user_blocked`, `logout`.
- Captura: IP, User-Agent y timestamp.

### Protección de Rutas
- Middleware `$requireAuth` para rutas protegidas.
- Middleware `$requireRole` para rutas exclusivas por rol.

### Recuperación de Contraseña
- Flujo completo con envío de correo electrónico (SMTP).
