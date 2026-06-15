#!/bin/bash
# ──────────────────────────────────────────────────────────────────
#  Reset rápido del sistema APA-UCM para testing
#  Uso: ./reset_test.sh
# ──────────────────────────────────────────────────────────────────

set -e

echo "🔄 Reseteando sistema APA-UCM para testing..."
echo ""

# 1. Verificar que docker está corriendo
if ! docker compose ps > /dev/null 2>&1; then
    echo "❌ Docker no está corriendo. Inicia Docker Desktop primero."
    exit 1
fi

# 2. Levantar contenedores si están detenidos
echo "📦 Verificando contenedores..."
docker compose up -d
sleep 3

# 3. Esperar a que PostgreSQL esté listo
echo "⏳ Esperando que PostgreSQL esté listo..."
until docker compose exec -T db pg_isready -U apa_user -d apa_ucm > /dev/null 2>&1; do
    sleep 1
done

# 4. Limpiar cachés de Laravel
echo "🧹 Limpiando cachés..."
docker compose exec -T app php artisan cache:clear > /dev/null
docker compose exec -T app php artisan config:clear > /dev/null
docker compose exec -T app php artisan view:clear > /dev/null
docker compose exec -T app php artisan route:clear > /dev/null

# 5. Migrar y poblar BD
echo "🗄️  Reseteando base de datos y aplicando seeders..."
docker compose exec -T app php artisan migrate:fresh --seed --force

# 6. Verificar resultado
echo ""
echo "✅ Sistema reseteado correctamente!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📋 Datos cargados:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

docker compose exec -T app php artisan tinker --execute="
echo 'Usuarios: ' . App\Models\User::count() . PHP_EOL;
echo 'Facultades: ' . App\Models\Facultad::count() . PHP_EOL;
echo 'Periodos: ' . App\Models\Periodo::count() . PHP_EOL;
echo 'Semestres Académicos: ' . App\Models\SemestreAcademico::count() . PHP_EOL;
echo 'Nominas: ' . App\Models\Nomina::count() . PHP_EOL;
echo 'Compromisos APA: ' . App\Models\CompromisoApa::count() . PHP_EOL;
echo 'Evidencias: ' . App\Models\Evidencia::count() . PHP_EOL;
" 2>/dev/null

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🌐 Accede al sistema en: http://localhost:8080"
echo "🔑 Password universal: password"
echo ""
echo "📖 Lee la guía completa en: GUIA_TESTING_COMPLETO.md"
echo ""
echo "👥 Usuarios principales:"
echo "  - admin@ucm.cl           → Admin"
echo "  - analista@ucm.cl        → Analista CCDA"
echo "  - secretario@ucm.cl      → Secretario FCI"
echo "  - cca@ucm.cl             → Miembro CCA FCI"
echo "  - jefe@ucm.cl            → Jefe Académico FCI"
echo "  - vicerrectora@ucm.cl    → Vicerrectora"
echo "  - academico@ucm.cl       → Académico (S1+S2 OK)"
echo "  - academico.fcaf@ucm.cl  → Académico FCAF (solo S1)"
echo ""
echo "📊 Para importar nómina extendida:"
echo "  Login como analista@ucm.cl"
echo "  → Períodos → Gestionar"
echo "  → Importar Excel SAPD"
echo "  → /Users/tban/Documents/apa-ucm/nomina_academicos_vigencia_realista.csv"
echo ""
