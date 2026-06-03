#!/usr/bin/env bash
set -e

echo ""
echo "========================================="
echo "  Sistema de Reservas - Instalación"
echo "========================================="
echo ""

# Comprobar dependencias mínimas
command -v docker   >/dev/null 2>&1 || { echo "ERROR: Docker no instalado. https://docs.docker.com/get-docker/"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "ERROR: Composer no instalado."; exit 1; }
command -v npm      >/dev/null 2>&1 || { echo "ERROR: npm no instalado."; exit 1; }

echo "→ Instalando dependencias PHP..."
composer install --no-interaction --prefer-dist

echo "→ Configurando .env..."
if [ ! -f .env ]; then
    cp .env.example .env
else
    echo "   .env ya existe, se conserva."
fi

echo "→ Levantando contenedores Sail (MySQL + App)..."
./vendor/bin/sail up -d

echo "→ Esperando a que MySQL esté listo (15 s)..."
sleep 15

echo "→ Generando clave de la aplicación..."
./vendor/bin/sail artisan key:generate --force

echo "→ Migraciones y datos de prueba..."
./vendor/bin/sail artisan migrate:fresh --seed --force

echo "→ Instalando dependencias JavaScript..."
npm install

echo "→ Compilando assets frontend..."
npm run build

echo "→ Limpiando caché..."
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear

echo ""
echo "========================================="
echo "  Listo. Abre http://localhost"
echo "========================================="
echo ""
echo "  Usuarios de prueba:"
echo "    admin@reservas.es   / password  (Administrador)"
echo "    gestor@reservas.es  / password  (Gestor)"
echo "    usuario@reservas.es / password  (Usuario)"
echo ""
echo "  Comandos útiles:"
echo "    ./vendor/bin/sail up -d     # arrancar"
echo "    ./vendor/bin/sail down      # parar"
echo "    ./vendor/bin/sail artisan migrate:fresh --seed"
echo ""
