# Guía del proyecto — Sistema de Reservas

---

## 1. Qué es esto y cómo funciona en general

Es una aplicación web de dos partes que se comunican entre sí:

```
NAVEGADOR (Vue 3)  ←──────────────────→  SERVIDOR (Laravel)  ←──→  BASE DE DATOS (MySQL)
  Muestra la web                           Gestiona los datos          Guarda todo
  Llama a la API                           Devuelve JSON
```

- **Vue** es el frontend: lo que ve el usuario en el navegador. Está en `resources/js/`.
- **Laravel** es el backend: la API que recibe peticiones y devuelve datos en JSON. Está en `app/`.
- **MySQL** guarda los datos. Arranca automáticamente con Docker (Sail).

---

## 2. Cómo arrancar el proyecto

```bash
# 1. Levantar Docker (PHP + MySQL)
./vendor/bin/sail up -d

# 2. Crear tablas y meter datos de prueba
./vendor/bin/sail artisan migrate:fresh --seed

# 3. Compilar el frontend (en otra terminal)
npm run dev
```

Abrir el navegador en **http://localhost**

```bash
# Para parar:
./vendor/bin/sail down
```

---

## 3. Usuarios de prueba

| Email | Contraseña | Qué puede hacer |
|---|---|---|
| admin@reservas.es | password | Todo |
| gestor@reservas.es | password | Gestionar recursos y reservas |
| usuario@reservas.es | password | Ver recursos y hacer reservas |

---

## 4. La base de datos — tablas y para qué sirven

```
users               → Usuarios registrados (nombre, email, contraseña, rol)
roles               → Roles disponibles: admin, manager, user  (los crea Spatie)
model_has_roles     → Tabla puente: qué rol tiene cada usuario

categories          → Categorías de recursos (Salas, Equipamiento, Instalaciones...)
resources           → Recursos que se pueden reservar (Sala A, Portátil 1, Pista de Pádel...)
reservations        → Reservas: quién reserva qué recurso y cuándo
reservation_logs    → Historial de cambios de cada reserva
site_settings       → Configuración del sitio (nombre, colores, etc.)
```

### Relaciones entre tablas

```
categories ──< resources ──< reservations >── users
                                   │
                                   └──< reservation_logs >── users
```

- Una **categoría** tiene muchos **recursos**
- Un **recurso** tiene muchas **reservas**
- Una **reserva** pertenece a un **usuario** y a un **recurso**
- Cada **reserva** puede tener muchos **logs** (historial)

---

## 5. Los archivos más importantes y qué hace cada uno

### 5.1 Modelos (`app/Models/`) — representan las tablas

| Archivo | Tabla | Para qué sirve |
|---|---|---|
| `User.php` | users | Usuarios. Tiene roles (Spatie) y tokens (Sanctum) |
| `Category.php` | categories | Categorías de recursos |
| `Resource.php` | resources | Recursos reservables. Calcula disponibilidad |
| `Reservation.php` | reservations | Reservas. Guarda historial de cambios |
| `ReservationLog.php` | reservation_logs | Historial de cada cambio en una reserva |
| `SiteSetting.php` | site_settings | Configuración del sitio (clave → valor) |

**Método más importante:** `Resource::isAvailableAt($inicio, $fin)`
→ Comprueba si un recurso está libre en ese horario mirando si hay reservas que se solapen.

---

### 5.2 Controladores (`app/Http/Controllers/Api/`) — responden las peticiones

| Archivo | Qué gestiona |
|---|---|
| `AuthController.php` | Registro, login y logout |
| `CategoryController.php` | CRUD de categorías |
| `ResourceController.php` | CRUD de recursos + disponibilidad por franjas |
| `ReservationController.php` | CRUD de reservas + vista de calendario |
| `AdminController.php` | Dashboard, gestión de usuarios |
| `SiteSettingController.php` | Leer y guardar configuración del sitio |

---

### 5.3 Rutas (`routes/api.php`) — las URLs de la API

**Públicas** (sin login):
```
POST /api/register              → Registrar usuario nuevo
POST /api/login                 → Iniciar sesión → devuelve token
GET  /api/categories            → Ver todas las categorías
GET  /api/resources             → Ver todos los recursos
GET  /api/resources/{id}/availability?date=YYYY-MM-DD  → Ver huecos libres
GET  /api/settings/{group}      → Ver configuración del sitio
```

**Privadas** (necesitan token en la cabecera: `Authorization: Bearer <token>`):
```
POST   /api/logout              → Cerrar sesión
GET    /api/me                  → Ver mis datos
GET    /api/reservations        → Ver mis reservas (admin ve todas)
POST   /api/reservations        → Crear reserva
PUT    /api/reservations/{id}   → Cambiar estado de una reserva
DELETE /api/reservations/{id}   → Cancelar una reserva
GET    /api/reservations/calendar?start=...&end=...  → Eventos para el calendario
```

**Solo admin/gestor:**
```
POST/PUT/DELETE /api/categories/{id}    → Gestionar categorías
POST/PUT/DELETE /api/resources/{id}     → Gestionar recursos
GET  /api/admin/dashboard               → Estadísticas
GET  /api/admin/users                   → Listar usuarios
PUT  /api/admin/users/{id}              → Editar usuario
DELETE /api/admin/users/{id}            → Borrar usuario
```

---

### 5.4 Frontend Vue (`resources/js/`)

**Páginas** (`pages/`):

| Ruta del navegador | Archivo Vue | Qué muestra |
|---|---|---|
| `/` | `Home.vue` | Portada con categorías |
| `/login` | `Login.vue` | Formulario de login |
| `/register` | `Register.vue` | Formulario de registro |
| `/recursos` | `Resources.vue` | Lista de recursos con botón Reservar |
| `/calendario` | `Calendar.vue` | Calendario con las reservas |
| `/mis-reservas` | `MyReservations.vue` | Mis reservas |
| `/admin` | `admin/Dashboard.vue` | Panel admin — estadísticas |
| `/admin/recursos` | `admin/Resources.vue` | Gestionar recursos |
| `/admin/categorias` | `admin/Categories.vue` | Gestionar categorías |
| `/admin/usuarios` | `admin/Users.vue` | Gestionar usuarios |
| `/admin/reservas` | `admin/Reservations.vue` | Gestionar reservas |
| `/admin/ajustes` | `admin/Settings.vue` | Configuración del sitio |

**Stores Pinia** (`stores/`) — estado global de la app:
- `auth.js` → guarda el usuario y el token. Hace login/logout llamando a la API.
- `settings.js` → guarda la configuración del sitio (nombre, colores...).

---

## 6. Cómo funciona el login paso a paso

```
1. Usuario rellena el formulario en /login (Login.vue)
2. Vue llama a POST /api/login con email y contraseña
3. Laravel comprueba las credenciales (AuthController@login)
4. Laravel devuelve el usuario y un TOKEN
5. Vue guarda el token en localStorage
6. A partir de ahora, todas las peticiones llevan el token en la cabecera
7. Laravel reconoce al usuario por ese token (Sanctum)
```

---

## 7. Cómo funciona una reserva paso a paso

```
1. Usuario va a /recursos y hace clic en "Reservar" (Resources.vue)
2. Aparece un modal con un selector de fecha y hora (ReservationModal.vue)
3. Vue llama a GET /api/resources/{id}/availability?date=... para ver huecos libres
4. Usuario elige un hueco y confirma
5. Vue llama a POST /api/reservations con resource_id, start_time, end_time
6. Laravel abre una transacción de BD (para evitar doble reserva)
7. Comprueba que el recurso sigue libre (Resource::isAvailableAt)
8. Calcula el precio: (minutos / slot_duration) × precio_por_slot
9. Crea la reserva con estado "pending"
10. Guarda un log: "created"
11. Devuelve la reserva creada
```

---

## 8. Los roles y quién puede hacer qué

Los roles los gestiona **Spatie Laravel Permission** (paquete instalado).

| Rol | Puede |
|---|---|
| `user` | Ver recursos, hacer reservas, ver sus propias reservas |
| `manager` | Todo lo anterior + gestionar recursos, categorías y ver todas las reservas |
| `admin` | Todo lo anterior + gestionar usuarios y configuración del sitio |

En los controladores se comprueba así:
```php
if (!$usuario->hasRole(['admin', 'manager'])) {
    // solo ve sus propias reservas
}
```

En las rutas se protege así:
```php
Route::middleware('role:admin|manager')->group(function () {
    // solo entran admin y manager
});
```

---

## 9. Cambios típicos que pueden pedir en el examen

### Añadir un campo nuevo a una tabla

Ejemplo: añadir `telefono` a los usuarios.

**Paso 1** — Crear la migración:
```bash
./vendor/bin/sail artisan make:migration add_telefono_to_users_table --table=users
```

**Paso 2** — Editar el archivo creado en `database/migrations/`:
```php
$table->string('telefono')->nullable();
```

**Paso 3** — Ejecutar la migración:
```bash
./vendor/bin/sail artisan migrate
```

**Paso 4** — Añadir el campo al `$fillable` del modelo `User.php`:
```php
protected $fillable = ['name', 'email', 'password', 'telefono'];
```

---

### Añadir un endpoint nuevo a la API

Ejemplo: endpoint para buscar recursos por nombre.

**Paso 1** — Añadir el método en el controlador (`ResourceController.php`):
```php
public function buscar(Request $request)
{
    $recursos = Resource::where('name', 'like', '%' . $request->q . '%')->get();
    return response()->json($recursos);
}
```

**Paso 2** — Registrar la ruta en `routes/api.php`:
```php
Route::get('/resources/buscar', [ResourceController::class, 'buscar']);
```

---

### Cambiar quién puede acceder a una ruta

En `routes/api.php`, mover la ruta dentro o fuera de un grupo middleware:

```php
// Solo usuarios logueados:
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mi-ruta', [MiController::class, 'miMetodo']);
});

// Solo admin:
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/mi-ruta', [MiController::class, 'miMetodo']);
});
```

---

## 10. Estructura de carpetas resumida

```
reservas/
├── app/
│   ├── Http/Controllers/Api/    ← Controladores (responden peticiones)
│   └── Models/                  ← Modelos (representan tablas de BD)
├── database/
│   ├── migrations/              ← Crean/modifican las tablas
│   └── seeders/                 ← Meten datos de prueba
├── routes/
│   ├── api.php                  ← URLs de la API (backend)
│   └── web.php                  ← Solo una ruta catch-all → carga Vue
├── resources/js/
│   ├── pages/                   ← Páginas de la web (Vue)
│   ├── components/              ← Componentes reutilizables (Vue)
│   ├── stores/                  ← Estado global: sesión, ajustes (Pinia)
│   └── router/index.js          ← Rutas del navegador (Vue Router)
├── compose.yaml                 ← Configuración de Docker (Sail)
└── .env                         ← Variables de configuración (BD, nombre app...)
```

---

## 11. Comandos útiles

```bash
# Levantar / parar el servidor
./vendor/bin/sail up -d
./vendor/bin/sail down

# Resetear la base de datos con datos de prueba
./vendor/bin/sail artisan migrate:fresh --seed

# Crear una migración nueva
./vendor/bin/sail artisan make:migration nombre_de_la_migracion

# Crear un controlador nuevo
./vendor/bin/sail artisan make:controller Api/NombreController

# Crear un modelo nuevo
./vendor/bin/sail artisan make:model NombreModelo

# Ver todas las rutas registradas
./vendor/bin/sail artisan route:list

# Ver los logs en tiempo real
./vendor/bin/sail artisan pail

# Compilar el frontend
npm run dev       # desarrollo (hot-reload)
npm run build     # producción
```
