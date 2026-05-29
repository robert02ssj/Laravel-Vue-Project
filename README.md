# Sistema de Reservas Multi-Recurso

Aplicación fullstack para la gestión de recursos compartidos con disponibilidad temporal. Desarrollada con Laravel 13 (backend) y Vue 3 (frontend SPA).

---

## Requisitos

- PHP 8.2+ con extensiones `pdo_sqlite` y `sqlite3`
- Composer
- Node.js 18+ y npm

---

## Instalación

### 1. Entrar al proyecto

```bash
cd ~/Documentos/Proyectos/LARAVEL/reservas
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

El proyecto usa **SQLite** por defecto. No necesitas configurar ningún servidor de base de datos.

### 4. Crear la base de datos y cargar datos de prueba

```bash
php artisan migrate:fresh --seed
```

Esto crea todas las tablas y genera:
- 3 categorías (Salas, Equipamiento, Instalaciones deportivas)
- 4 recursos de ejemplo
- 3 usuarios con distintos roles
- 1 reserva de ejemplo
- Ajustes del sitio por defecto

### 5. Instalar dependencias JavaScript

```bash
npm install
```

---

## Arrancar el proyecto

Necesitas **dos terminales** abiertas en la carpeta del proyecto.

**Terminal 1 — Backend Laravel:**

```bash
php artisan serve
```

Disponible en: `http://localhost:8000`

**Terminal 2 — Frontend Vite (hot-reload):**

```bash
npm run dev
```

Abre el navegador en `http://localhost:8000`.

> Para producción, ejecuta `npm run build` en lugar de `npm run dev`. Los assets compilados se guardan en `public/build/`.

---

## Usuarios de prueba

| Email | Contraseña | Rol |
|---|---|---|
| `admin@reservas.es` | `password` | Administrador |
| `gestor@reservas.es` | `password` | Gestor |
| `usuario@reservas.es` | `password` | Usuario |

---

## Estructura del proyecto

```
reservas/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── AuthController.php         # Registro, login, logout
│   │   ├── CategoryController.php     # CRUD categorías
│   │   ├── ResourceController.php     # CRUD recursos + disponibilidad
│   │   ├── ReservationController.php  # CRUD reservas + calendario
│   │   ├── AdminController.php        # Dashboard, usuarios
│   │   └── SiteSettingController.php  # Ajustes del sitio
│   └── Models/
│       ├── User.php
│       ├── Category.php
│       ├── Resource.php
│       ├── Reservation.php
│       ├── ReservationLog.php
│       └── SiteSetting.php
├── database/
│   ├── migrations/                    # Esquema completo
│   └── seeders/DatabaseSeeder.php     # Datos de prueba
├── routes/
│   ├── api.php                        # Endpoints REST
│   └── web.php                        # Catch-all → SPA
└── resources/
    ├── js/
    │   ├── app.js                     # Punto de entrada Vue
    │   ├── router/index.js            # Vue Router
    │   ├── stores/
    │   │   ├── auth.js                # Pinia: sesión y roles
    │   │   └── settings.js            # Pinia: ajustes del sitio
    │   ├── components/
    │   │   ├── Navbar.vue
    │   │   ├── ReservationModal.vue
    │   │   ├── StatusBadge.vue
    │   │   └── KpiCard.vue
    │   └── pages/
    │       ├── Home.vue
    │       ├── Login.vue
    │       ├── Register.vue
    │       ├── Resources.vue          # Vista de recursos con filtros
    │       ├── Calendar.vue           # FullCalendar
    │       ├── MyReservations.vue
    │       └── admin/
    │           ├── AdminLayout.vue    # Sidebar de administración
    │           ├── Dashboard.vue      # KPIs y últimas reservas
    │           ├── Categories.vue
    │           ├── Resources.vue
    │           ├── Reservations.vue
    │           ├── Users.vue
    │           └── Settings.vue       # Personalización del sitio
    └── views/app.blade.php            # Shell HTML de la SPA
```

---

## Endpoints de la API

### Públicos

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/register` | Registro de usuario |
| `POST` | `/api/login` | Login → devuelve token |
| `GET` | `/api/categories` | Listar categorías |
| `GET` | `/api/resources` | Listar recursos |
| `GET` | `/api/resources/{id}/availability?date=YYYY-MM-DD` | Slots disponibles |
| `GET` | `/api/settings/{group}` | Ajustes del sitio |

### Autenticados (`Authorization: Bearer <token>`)

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/logout` | Cerrar sesión |
| `GET` | `/api/me` | Usuario actual |
| `GET` | `/api/reservations` | Mis reservas (admin ve todas) |
| `POST` | `/api/reservations` | Crear reserva |
| `PUT` | `/api/reservations/{id}` | Actualizar estado |
| `DELETE` | `/api/reservations/{id}` | Cancelar reserva |
| `GET` | `/api/reservations/calendar?start=&end=` | Eventos para FullCalendar |

### Solo admin / gestor

| Método | Ruta | Descripción |
|---|---|---|
| `POST/PUT/DELETE` | `/api/categories/{id}` | Gestión de categorías |
| `POST/PUT/DELETE` | `/api/resources/{id}` | Gestión de recursos |
| `GET` | `/api/admin/dashboard` | Estadísticas globales |
| `GET` | `/api/admin/users` | Listado de usuarios |
| `PUT` | `/api/admin/users/{id}` | Cambiar nombre/email/rol |
| `DELETE` | `/api/admin/users/{id}` | Eliminar usuario |
| `POST` | `/api/settings` | Guardar ajustes del sitio |

---

## Comandos útiles

```bash
# Resetear la base de datos con datos de prueba
php artisan migrate:fresh --seed

# Ver todas las rutas registradas
php artisan route:list --path=api

# Consola interactiva
php artisan tinker

# Limpiar caché
php artisan config:clear && php artisan cache:clear
```

---

## Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| Autenticación | Laravel Sanctum |
| Roles | Spatie Laravel Permission |
| Base de datos | SQLite (dev) / MySQL-MariaDB (prod) |
| Frontend | Vue 3, Vite |
| Estado global | Pinia |
| Enrutamiento cliente | Vue Router 4 |
| Estilos | Tailwind CSS v4 |
| Calendario | FullCalendar v6 |
