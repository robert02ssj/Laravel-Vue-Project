# Sistema de Reservas Multi-Recurso

Aplicación web para la gestión de reservas de recursos compartidos (salas, equipos, instalaciones...).
Desarrollada con **Laravel 13** en el backend y **Vue 3** en el frontend como SPA.

Proyecto realizado para el módulo de **Desarrollo de Aplicaciones Web**.

---

## Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (o Docker Engine en Linux)
- [Composer](https://getcomposer.org/)
- [Node.js 18+](https://nodejs.org/) y npm

> Laravel Sail se encarga de levantar todos los servicios (PHP, MySQL) usando Docker.  
> No hace falta instalar PHP ni MySQL en tu máquina.

---

## Instalación con Laravel Sail

### 1. Clonar el proyecto y entrar a la carpeta

```bash
git clone <url-del-repositorio>
cd reservas
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Copiar el fichero de configuración

```bash
cp .env.example .env
```

### 4. Levantar los contenedores con Sail

```bash
./vendor/bin/sail up -d
```

Esto arranca el servidor PHP y la base de datos MySQL en segundo plano.

> La primera vez tarda un poco porque Docker descarga las imágenes necesarias.

### 5. Generar la clave de la aplicación

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Crear las tablas y cargar los datos de prueba

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Esto genera:
- 3 categorías (Salas de reuniones, Equipamiento, Instalaciones deportivas)
- 4 recursos de ejemplo
- 3 usuarios con distintos roles
- 1 reserva de ejemplo
- Ajustes del sitio por defecto

### 7. Instalar dependencias JavaScript y compilar el frontend

```bash
npm install
npm run dev
```

### 8. Abrir la aplicación

Abre el navegador en: **http://localhost**

---

## Parar los contenedores

```bash
./vendor/bin/sail down
```

---

## Usuarios de prueba

| Email | Contraseña | Rol |
|---|---|---|
| `admin@reservas.es` | `password` | Administrador |
| `gestor@reservas.es` | `password` | Gestor |
| `usuario@reservas.es` | `password` | Usuario normal |

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
│   │   ├── AdminController.php        # Panel de administración
│   │   └── SiteSettingController.php  # Configuración del sitio
│   └── Models/
│       ├── User.php
│       ├── Category.php
│       ├── Resource.php
│       ├── Reservation.php
│       ├── ReservationLog.php
│       └── SiteSetting.php
├── database/
│   ├── migrations/                    # Esquema de la base de datos
│   └── seeders/DatabaseSeeder.php     # Datos de prueba
├── docker-compose.yml                 # Configuración de Sail (Docker)
├── routes/
│   ├── api.php                        # Endpoints de la API REST
│   └── web.php                        # Ruta catch-all para la SPA
└── resources/
    ├── js/
    │   ├── app.js                     # Punto de entrada Vue
    │   ├── router/index.js            # Vue Router (rutas del frontend)
    │   ├── stores/
    │   │   ├── auth.js                # Pinia: sesión y roles del usuario
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
    │       ├── Resources.vue
    │       ├── Calendar.vue
    │       ├── MyReservations.vue
    │       └── admin/
    │           ├── AdminLayout.vue
    │           ├── Dashboard.vue
    │           ├── Categories.vue
    │           ├── Resources.vue
    │           ├── Reservations.vue
    │           ├── Users.vue
    │           └── Settings.vue
    └── views/app.blade.php            # Plantilla HTML base de la SPA
```

---

## Comandos útiles con Sail

```bash
# Ejecutar comandos artisan
./vendor/bin/sail artisan <comando>

# Resetear la base de datos con datos de prueba
./vendor/bin/sail artisan migrate:fresh --seed

# Ver todas las rutas de la API
./vendor/bin/sail artisan route:list --path=api

# Acceder a la consola interactiva de Laravel
./vendor/bin/sail artisan tinker

# Limpiar la caché
./vendor/bin/sail artisan config:clear && ./vendor/bin/sail artisan cache:clear

# Ver los logs en tiempo real
./vendor/bin/sail artisan pail
```

---

## Endpoints de la API

### Públicos (sin autenticación)

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
| `GET` | `/api/me` | Datos del usuario actual |
| `GET` | `/api/reservations` | Mis reservas (admin ve todas) |
| `POST` | `/api/reservations` | Crear reserva |
| `PUT` | `/api/reservations/{id}` | Actualizar estado |
| `DELETE` | `/api/reservations/{id}` | Cancelar reserva |
| `GET` | `/api/reservations/calendar?start=&end=` | Eventos para el calendario |

### Solo administrador / gestor

| Método | Ruta | Descripción |
|---|---|---|
| `POST/PUT/DELETE` | `/api/categories/{id}` | Gestión de categorías |
| `POST/PUT/DELETE` | `/api/resources/{id}` | Gestión de recursos |
| `GET` | `/api/admin/dashboard` | Estadísticas globales |
| `GET` | `/api/admin/users` | Listado de usuarios |
| `PUT` | `/api/admin/users/{id}` | Editar usuario |
| `DELETE` | `/api/admin/users/{id}` | Eliminar usuario |
| `POST` | `/api/settings` | Guardar configuración del sitio |

---

## Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Entorno de desarrollo | Laravel Sail (Docker) |
| Autenticación | Laravel Sanctum |
| Roles y permisos | Spatie Laravel Permission |
| Base de datos | MySQL 8.4 |
| Frontend | Vue 3, Vite |
| Estado global | Pinia |
| Enrutamiento cliente | Vue Router 4 |
| Estilos | Tailwind CSS v4 |
| Calendario | FullCalendar v6 |
