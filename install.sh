#!/usr/bin/env bash
set -e

echo ""
echo "========================================="
echo "  Sistema de Reservas - Instalación"
echo "========================================="
echo ""

# Comprobar Docker
command -v docker >/dev/null 2>&1 || { echo "ERROR: Docker no encontrado. Instálalo en https://docs.docker.com/get-docker/"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "ERROR: Composer no encontrado."; exit 1; }

echo "→ Instalando dependencias PHP..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "→ Configurando entorno..."
if [ ! -f .env ]; then
    cp .env.example .env
else
    echo "   .env ya existe, omitiendo."
fi

echo "→ Levantando contenedores con Sail (Docker)..."
./vendor/bin/sail up -d

echo "→ Esperando a que MySQL esté listo..."
sleep 10

echo "→ Generando clave de la aplicación..."
./vendor/bin/sail artisan key:generate

echo "→ Ejecutando migraciones y seeders..."
./vendor/bin/sail artisan migrate:fresh --seed --force

echo "→ Instalando dependencias JavaScript..."
npm install

echo "→ Compilando assets..."
npm run build

echo ""
echo "========================================="
echo "  Instalación completada"
echo "========================================="
echo ""
echo "  Usuarios de prueba:"
echo "    admin@reservas.es   / password  (Administrador)"
echo "    gestor@reservas.es  / password  (Gestor)"
echo "    usuario@reservas.es / password  (Usuario)"
echo ""
echo "  Abre el navegador en: http://localhost"
echo ""
echo "  Para parar los contenedores:"
echo "    ./vendor/bin/sail down"
echo ""
