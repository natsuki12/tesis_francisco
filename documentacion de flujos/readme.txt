ESTRUCTURA DEL PROYECTO - SISTEMA SIMULADOR SENIAT
==============================================================================
Arquitectura: MVC Modular orientado a dominios (separación por módulos de negocio).
Tecnologías: PHP Nativo (sin frameworks pesados), CSS3, JavaScript, MySQL.
Enfoque: Separación de responsabilidades + organización por dominios (Auth, Student, Simulator).
Seguridad estructural: solo /public es accesible desde el navegador; el código y vistas quedan fuera del DocumentRoot.
==============================================================================

FLUJO GENERAL DE EJECUCIÓN
Request → public/index.php (Front Controller) → Core/App (bootstrap) → Core/Router (resuelve rutas definidas en routes/web.php)
→ Controller@action → Render (layout + view en resources/views) → Response
Los recursos estáticos (CSS/JS/IMG) se sirven únicamente desde public/assets.

RAÍZ DEL PROYECTO (SISTEMA/)
├── .env
│   # Variables de entorno (credenciales DB, SMTP, URL base, etc.).
├── .htaccess
│   # Reescritura y reglas de acceso: dirige el tráfico al Front Controller (public/index.php).
├── composer.json
│   # Dependencias y autoload (Composer / PSR-4).
├── public/                     # ÚNICA carpeta accesible desde el navegador (Document Root).
│   ├── .htaccess               # URLs limpias y reglas de enrutamiento frontal.
│   ├── index.php               # Front Controller: inicializa la app y delega al Router.
│   └── assets/                 # Recursos estáticos (minúsculas; Linux-proof).
│       ├── css/
│       │   ├── global/         # Estilos base (reset/variables/componentes).
│       │   ├── partials/       # Estilos de layout compartidos (guest vs logged).
│       │   ├── auth/           # Estilos del auth del sistema (login/registro).
│       │   ├── student/        # Estilos del área estudiante (dashboard).
│       │   └── simulator/      # Estilos del simulador (herramienta fiscal).
│       │       ├── steps/      # Estilos por paso del wizard (step_01_..., step_02_...).
│       │       └── legacy/     # Estilos antiguos aislados (referencia/compatibilidad).
│       ├── js/
│       │   ├── global/         # Lógica compartida (UI del layout, helpers frontend).
│       │   ├── auth/           # Scripts del auth del sistema.
│       │   └── simulator/
│       │       ├── steps/      # Scripts por paso del simulador.
│       │       └── auth/       # Auth interno del simulador (independiente del auth del sistema).
│       └── img/                # Imágenes, logos y banners (SENIAT/UNIMAR/landing).

├── src/                        # BACKEND: lógica de negocio (no público; autoload por Composer).
│   ├── Core/                   # Infraestructura compartida (“mini-framework”).
│   │   ├── App.php             # Bootstrap de la aplicación.
│   │   ├── Router.php          # Enrutamiento: interpreta routes/web.php y despacha controladores.
│   │   ├── DB.php              # Conexión a BD (PDO / Singleton).
│   │   ├── Controller.php      # Controlador base: render y redirecciones.
│   │   ├── Csrf.php            # Seguridad CSRF.
│   │   ├── helpers.php         # Utilidades generales.
│   │   └── Services/           # Servicios globales transversales.
│   └── Modules/                # Módulos por dominio.
│       ├── Auth/               # Auth del sistema.
│       │   ├── Controllers/
│       │   ├── Models/
│       │   └── Services/
│       ├── Student/            # Área del estudiante.
│       │   ├── Controllers/
│       │   ├── Models/
│       │   └── Services/
│       ├── Simulator/          # Simulador SENIAT.
│       │   ├── Controllers/
│       │   ├── Models/
│       │   └── Services/       # Cálculos/validaciones (UT, cuotas, etc.).
│       ├── Professor/          # Reservado para extensión futura.
│       └── Admin/              # Reservado para extensión futura.

├── resources/                  # CAPA DE PRESENTACIÓN (no pública).
│   ├── content/
│   ├── lang/
│   └── views/
│       ├── layouts/            # Plantillas maestras (guest / interno).
│       ├── partials/           # Fragmentos reutilizables (guest / logged).
│       ├── landing/
│       ├── auth/               # Vistas del auth del sistema.
│       ├── student/            # Vistas del área estudiante.
│       └── simulator/          # Vistas del simulador.
│           ├── steps/          # Vistas por paso.
│           ├── auth/           # Auth interno del simulador.
│           └── legacy/         # Vistas antiguas aisladas.

├── routes/
│   └── web.php                 # Definición declarativa de URLs válidas.

├── storage/
│   ├── logs/                   # Registros para diagnóstico/auditoría (app_errors.log).
│   └── uploads/                # Archivos del usuario (idealmente fuera de /public).

└── tests/
    └── test.php
==============================================================================
NOTA DE CONVENCIONES (LINUX-PROOF)
- En /public/assets y /resources se usa nomenclatura en minúsculas (case-sensitive en Linux).
- En /src se mantiene nomenclatura consistente y autoload por Composer (PSR-4 friendly).
==============================================================================
