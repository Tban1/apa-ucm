# Sistema APA UCM
### Plataforma web para la Gestión del Proceso de Calificación Académica — Universidad Católica del Maule

**Estudiante:** Esteban Ignacio Rojas Calderón  
**Carrera:** Ingeniería Civil Informática  
**Actividad:** INF613 – Módulo Integrador de Formación Profesional

---

## Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| Frontend | React 18 + Inertia.js |
| Backend | Laravel 11 (PHP 8.3) |
| Base de datos | PostgreSQL 16 |
| Caché / Colas | Redis 7 |
| Servidor web | Nginx 1.25 |
| Contenedores | Docker + Docker Compose |
| Estilos | Tailwind CSS |

---

## Requisitos previos

- Docker Desktop (con WSL2 habilitado en Windows)
- Git
- WSL2 con Ubuntu (recomendado)

---

## Instalación (primera vez)

```bash
# 1. Clonar el repositorio
git clone https://github.com/TU_USUARIO/apa-ucm.git
cd apa-ucm

# 2. Dar permisos al script
chmod +x setup.sh

# 3. Ejecutar instalación completa
./setup.sh
```

La app quedará disponible en **http://localhost:8080**

---

## Comandos del día a día

```bash
# Levantar el entorno
docker compose up -d

# Detener el entorno
docker compose down

# Ver logs
docker compose logs -f app

# Ejecutar comandos Artisan
docker compose exec app php artisan <comando>

# Ejecutar migraciones
docker compose exec app php artisan migrate

# Instalar paquetes PHP
docker compose exec app composer require <paquete>

# Instalar paquetes JS
docker compose exec app npm install <paquete>

# Compilar assets (desarrollo)
docker compose exec app npm run dev

# Compilar assets (producción)
docker compose exec app npm run build
```

---

## Módulos del sistema

1. **Autenticación y roles** — Académico, Secretario, CCA, Analista CCDA, Admin
2. **Calendarización y nóminas** — Gestión de períodos y plazos por la CCDA
3. **Gestión del secretario** — Panel de seguimiento por académico y facultad
4. **Carga de evidencias** — Subida de archivos por las 5 categorías APA
5. **Evaluación CCA** — Panel de evaluación individual y calificación final
6. **Informes** — Generación de reportes por secretario
7. **Apelaciones** — Ventana controlada para re-subida de evidencias
8. **Cierre y actas** — Registro formal del cierre del proceso
9. **Notificaciones** — Alertas automáticas vía colas Redis

---

## Estructura del repositorio

```
apa-ucm/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── php.ini
├── src/                  ← Proyecto Laravel (generado por setup.sh)
├── .env.example
├── .gitignore
├── docker-compose.yml
├── Dockerfile
├── setup.sh
└── README.md
```

---

## Base de datos

- **Host:** localhost:5432  
- **Base de datos:** apa_ucm  
- **Usuario:** apa_user  
- **Contraseña:** secret (desarrollo)

Puedes conectarte con DBeaver, TablePlus o cualquier cliente PostgreSQL.

---

## Convenciones del proyecto

- **Ramas:** `main` (producción), `feature/nombre-modulo`
- **Commits:** en español, descriptivos. Ej: `feat: agregar módulo de carga de evidencias`
- **Migraciones:** siempre nombradas con el módulo. Ej: `create_evidencias_table`
