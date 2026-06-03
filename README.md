# Sistema de Reservas Multi-Recurso

Aplicación web para la gestión de reservas de recursos compartidos (salas, equipos, instalaciones...).
Desarrollada con **Laravel** en el backend y **Vue 3** en el frontend como SPA.

Proyecto realizado para el módulo de **Desarrollo de Aplicaciones Web**.

---

## Requisitos

- [Docker](https://docs.docker.com/get-docker/) con Docker Compose
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) 18+ y npm

---

## Instalación rápida (con Sail)

```bash
# 1. Clonar el repositorio
git clone https://github.com/robert02ssj/Laravel-Vue-Project.git
cd Laravel-Vue-Project

# 2. Ejecutar el script de instalación (hace todo automáticamente)
bash install.sh
```

El script instala dependencias, levanta Docker, migra la base de datos y compila el frontend.

Abre el navegador en **http://localhost**

---

## Instalación manual paso a paso

```bash
# Dependencias PHP
composer install

# Configurar entorno
cp .env.example .env

# Levantar contenedores (Laravel + MySQL)
./vendor/bin/sail up -d

# Esperar ~15 segundos a que MySQL arranque, luego:
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed

# Compilar frontend
npm install
npm run build
```

---

## Usuarios de prueba

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@reservas.es | password | Administrador |
| gestor@reservas.es | password | Gestor |
| usuario@reservas.es | password | Usuario |

---

## Comandos habituales

```bash
# Arrancar
./vendor/bin/sail up -d

# Parar
./vendor/bin/sail down

# Resetear base de datos
./vendor/bin/sail artisan migrate:fresh --seed

# Consola dentro del contenedor
./vendor/bin/sail shell

# Ver logs
./vendor/bin/sail logs
```

---

## Funcionalidades

- Registro y login con tokens (Laravel Sanctum)
- Tres roles: admin, gestor y usuario
- CRUD completo de categorías y recursos
- Reservas con comprobación de solapamiento de horarios
- Calendario de reservas (FullCalendar)
- Panel de administración con estadísticas
- Personalización del sitio (nombre, colores, logo)
- **CRUD de usuarios en PHP clásico** (sin framework) en `/php-crud/`

---

## Tecnologías

| Backend | Frontend | Base de datos |
|---------|----------|---------------|
| Laravel + Sanctum | Vue 3 + Pinia + Vue Router | MySQL 8 (Docker) |
| Spatie Laravel Permission | Tailwind CSS v4 | — |
| — | FullCalendar v6 | — |
