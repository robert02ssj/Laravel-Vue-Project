#!/usr/bin/env bash
set -e

echo ""
echo "========================================="
echo "  Sistema de Reservas - Instalación"
echo "========================================="
echo ""

# Requisitos mínimos
command -v php  >/dev/null 2>&1 || { echo "ERROR: PHP no encontrado."; exit 1; }
command -v node >/dev/null 2>&1 || { echo "ERROR: Node.js no encontrado."; exit 1; }
command -v npm  >/dev/null 2>&1 || { echo "ERROR: npm no encontrado."; exit 1; }

# Verificar extensiones PHP necesarias
php -m | grep -q pdo_sqlite || { echo "ERROR: Extensión PHP pdo_sqlite no activa. Actívala en php.ini"; exit 1; }
php -m | grep -q sqlite3    || { echo "ERROR: Extensión PHP sqlite3 no activa. Actívala en php.ini"; exit 1; }

# Instalar Composer si no está disponible
if ! command -v composer >/dev/null 2>&1; then
    echo "→ Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir="$HOME/.local/bin" --filename=composer
    export PATH="$HOME/.local/bin:$PATH"
fi

echo "→ Instalando dependencias PHP..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "→ Instalando dependencias Node..."
npm install

echo "→ Configurando entorno..."
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
else
    echo "   .env ya existe, omitiendo."
fi

echo "→ Creando base de datos SQLite..."
touch database/database.sqlite

echo "→ Ejecutando migraciones y seeders..."
php artisan migrate:fresh --seed --force

echo "→ Compilando assets..."
npm run build

echo "→ Limpiando caché..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "========================================="
echo "  ✓ Instalación completada"
echo "========================================="
echo ""
echo "  Usuarios de prueba:"
echo "    admin@reservas.es   / password  (Administrador)"
echo "    gestor@reservas.es  / password  (Gestor)"
echo "    usuario@reservas.es / password  (Usuario)"
echo ""
echo "  Para arrancar:"
echo "    php artisan serve      (backend  → http://localhost:8000)"
echo "    npm run dev            (frontend → hot-reload)"
echo ""
