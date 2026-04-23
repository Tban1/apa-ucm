#!/bin/bash
# =============================================================================
# setup.sh — Instalación inicial del Sistema APA UCM
# Ejecutar UNA sola vez después de clonar el repositorio
# =============================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Sistema APA UCM — Setup inicial      ${NC}"
echo -e "${GREEN}========================================${NC}"

# 1. Verificar que Docker esté corriendo
if ! docker info > /dev/null 2>&1; then
  echo -e "${RED}❌ Docker no está corriendo. Inícialo primero.${NC}"
  exit 1
fi

# 2. Crear proyecto Laravel en ./src si no existe
if [ ! -f "./src/composer.json" ]; then
  echo -e "${YELLOW}📦 Creando proyecto Laravel...${NC}"
  docker run --rm \
    -v "$(pwd)/src:/app" \
    composer:latest \
    composer create-project laravel/laravel . --prefer-dist --no-interaction
  echo -e "${GREEN}✅ Laravel instalado${NC}"
else
  echo -e "${YELLOW}⏭  Laravel ya existe, saltando...${NC}"
fi

# 3. Copiar .env
if [ ! -f "./src/.env" ]; then
  echo -e "${YELLOW}📄 Configurando .env...${NC}"
  cp .env.example ./src/.env
  echo -e "${GREEN}✅ .env creado${NC}"
fi

# 4. Build de la imagen Docker
echo -e "${YELLOW}🐳 Construyendo imagen Docker...${NC}"
docker compose build --no-cache

# 5. Levantar contenedores
echo -e "${YELLOW}🚀 Levantando contenedores...${NC}"
docker compose up -d

# 6. Esperar a que la base de datos esté lista
echo -e "${YELLOW}⏳ Esperando a PostgreSQL...${NC}"
sleep 5

# 7. Instalar dependencias PHP
echo -e "${YELLOW}📦 Instalando dependencias PHP (Composer)...${NC}"
docker compose exec app composer install

# 8. Generar APP_KEY
echo -e "${YELLOW}🔑 Generando APP_KEY...${NC}"
docker compose exec app php artisan key:generate

# 9. Instalar Inertia + React + Tailwind
echo -e "${YELLOW}📦 Instalando Inertia.js + React + Tailwind...${NC}"
docker compose exec app composer require inertiajs/inertia-laravel
docker compose exec app npm install
docker compose exec app npm install @inertiajs/react react react-dom
docker compose exec app npm install -D @vitejs/plugin-react tailwindcss @tailwindcss/forms autoprefixer

# 10. Crear storage link
echo -e "${YELLOW}🔗 Creando storage link...${NC}"
docker compose exec app php artisan storage:link

# 11. Ejecutar migraciones base
echo -e "${YELLOW}🗄️  Ejecutando migraciones...${NC}"
docker compose exec app php artisan migrate

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ ¡Instalación completada!             ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "  🌐 App:    http://localhost:8080"
echo -e "  🗄️  DB:     localhost:5432 (apa_ucm)"
echo -e "  📦 Redis:  localhost:6379"
echo ""
echo -e "${YELLOW}Próximo paso: configurar Inertia y los modelos APA${NC}"
echo -e "  → Corre: ${GREEN}docker compose exec app php artisan tinker${NC}"
