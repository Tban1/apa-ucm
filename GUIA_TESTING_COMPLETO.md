# 🧪 Guía de Testing Completo — Sistema APA UCM

> **Versión:** 2026-1 | **Última actualización:** Junio 2026

---

## 📋 Tabla de Contenidos

1. [Setup Inicial](#-1-setup-inicial)
2. [Usuarios de Prueba](#-2-usuarios-de-prueba)
3. [Flujo Completo de Testing](#-3-flujo-completo-de-testing)
4. [Casos Especiales](#-4-casos-especiales)
5. [Troubleshooting](#-5-troubleshooting)

---

## 🚀 1. SETUP INICIAL

### 1.1 Levantar el sistema

```bash
cd /Users/tban/Documents/apa-ucm

# Levantar contenedores Docker
docker compose up -d

# Esperar 10 segundos para que PostgreSQL esté listo
sleep 10

# Reset completo de BD + seeders
docker compose exec app php artisan migrate:fresh --seed
```

### 1.2 Verificar que todo está OK

```bash
# Verificar migraciones
docker compose exec app php artisan migrate:status

# Verificar usuarios creados
docker compose exec app php artisan tinker --execute="echo App\Models\User::count();"
```

### 1.3 Acceder al sistema

- **URL:** http://localhost:8080
- **Password universal:** `password`

---

## 👥 2. USUARIOS DE PRUEBA

| Email | Rol | Facultad | Estado Inicial |
|-------|-----|----------|----------------|
| `admin@ucm.cl` | Admin | — | Configura semestres |
| `analista@ucm.cl` | Analista CCDA | — | Gestiona nóminas y reportes |
| `secretario@ucm.cl` | Secretario | FCI | Valida expedientes |
| `cca@ucm.cl` | Miembro CCA | FCI | Evalúa académicos |
| `jefe@ucm.cl` | Jefe Académico | FCI | Emite informes |
| `vicerrectora@ucm.cl` | Vicerrectora | — | Revisión final |
| `academico@ucm.cl` | Académico | FCI | **S1 y S2 ya declarados** |
| `secretario.fcaf@ucm.cl` | Secretario | FCAF | Valida FCAF |
| `cca.fcaf@ucm.cl` | Miembro CCA | FCAF | Evalúa FCAF |
| `jefe.fcaf@ucm.cl` | Jefe Académico | FCAF | Informes FCAF |
| `academico.fcaf@ucm.cl` | Académico | FCAF | **Solo S1 declarado, debe declarar S2** |

---

## 🔄 3. FLUJO COMPLETO DE TESTING

### 📍 **PARTE A: Admin — Configuración de Semestres**

#### Test A.1: Verificar configuración inicial
1. Login: `admin@ucm.cl` / `password`
2. En el dashboard, click en **"Configuración de Semestres"**
3. **Verificar:**
   - ✅ Se muestran 2 fechas precargadas (S1 y S2)
   - ✅ Fecha S1 está antes de Fecha S2
4. **Opcional:** Modificar las fechas y guardar

---

### 📍 **PARTE B: Analista CCDA — Gestión de Nómina**

#### Test B.1: Visualizar nómina del período
1. Logout y login: `analista@ucm.cl`
2. Click en **"Períodos"**
3. Entrar al período activo (2026-1)
4. Click en **"Gestionar"** o **"Nómina"**
5. **Verificar:**
   - ✅ Ves 8 académicos cargados (6 FCI + 2 FCAF)
   - ✅ NO existe botón "Exportar Excelentes"
   - ✅ NO hay botones "+ Caso" o "Quitar caso"
   - ✅ Académicos con licencia médica muestran badge "especial"

#### Test B.2: Importar nómina desde CSV
1. En el panel lateral **"Importar Excel SAPD"**
2. Click en **"Seleccionar archivo"**
3. Seleccionar: `/Users/tban/Documents/apa-ucm/nomina_academicos_vigencia_realista.csv`
4. Click en **"Cargar"**
5. **Verificar:**
   - ✅ Se detectan ~12-14 campos automáticamente
   - ✅ "Categoría 2026" y "Fecha Categoría 2026" se mapean como campos principales
6. Click en **"Importar nómina"**
7. **Verificar:**
   - ✅ Se importan 20 académicos nuevos
   - ✅ Total nómina: 28 académicos (8 originales + 20 nuevos)

#### Test B.3: Verificar coherencia de vigencias
1. Buscar académico **"Juan Carlos Pérez Muñoz"** en la lista
2. Click en el nombre para ver detalle
3. **Verificar:**
   - ✅ Categoría: **Titular**
   - ✅ Fecha Categorización: **15/03/2024**
   - ✅ Vencimiento: **15/03/2026** (NO 15/03/2024) ❌ Vencida
   - ✅ Historial muestra: 2020 (Adjunto), 2023 (Adjunto), 2026 (Titular)

4. Buscar **"Pedro Antonio Silva Lagos"** (Auxiliar)
5. **Verificar:**
   - ✅ Categoría: **Auxiliar**
   - ✅ Fecha Categorización: **15/05/2025**
   - ✅ Vencimiento: **15/05/2026** (1 año después) ❌ Vencida

#### Test B.4: Reporte Consolidado con columnas personalizadas
1. Volver al menú lateral, click en **"Reporte de Calificaciones"**
2. **Verificar:**
   - ✅ Se muestra el reporte agrupado por facultad
   - ✅ Aparece nuevo botón azul: **"+ Agregar columna manual"**
3. Click en **"+ Agregar columna manual"**
4. Agregar 2 columnas:
   - "Observaciones"
   - "Fecha revisión"
5. **Verificar:**
   - ✅ Las columnas aparecen al final de la tabla
   - ✅ Los campos son editables (click para escribir)
6. Escribir datos en algunos campos
7. Click en **"Imprimir / Guardar como PDF"**
8. **Verificar:**
   - ✅ El PDF incluye las columnas personalizadas con sus datos

---

### 📍 **PARTE C: Académico — Declaración APA Secuencial**

#### Test C.1: Académico con S1 y S2 ya declarados (puede cargar evidencias)
1. Logout y login: `academico@ucm.cl` / `password`
2. **Verificar:**
   - ✅ Va directo al dashboard (no redirige a declaración)
3. Click en **"Cargar Evidencias"**
4. **Verificar:**
   - ✅ Puede acceder normalmente
   - ✅ Ve sus porcentajes declarados S1 y S2
   - ✅ Puede subir evidencias en cada área APA

#### Test C.2: Académico que debe declarar S2 (FCAF)
1. Logout y login: `academico.fcaf@ucm.cl` / `password`
2. **Verificar:**
   - ✅ Es redirigido automáticamente a `/academico/declaracion-apa/S2`
   - ✅ Mensaje indica que debe declarar S2 antes de continuar
3. **Llenar el formulario S2:**
   - Docencia: `40`
   - Investigación: `30`
   - Extensión: `20`
   - Administración: `10`
   - **Total: 100%** ✓
4. **Verificar:**
   - ✅ El contador suma 100% en verde
   - ✅ Botón "Confirmar" se habilita
5. Click en **"Confirmar Declaración"**
6. **Verificar:**
   - ✅ Mensaje de éxito
   - ✅ Redirige a Carga de Evidencias

#### Test C.3: Validaciones de declaración
1. Sin guardar, escribir porcentajes que NO sumen 100% (ej: 50+30+10+5)
2. **Verificar:**
   - ✅ Botón "Confirmar" deshabilitado
   - ✅ Indicador muestra suma actual y advertencia

---

### 📍 **PARTE D: Secretario — Validación de Expedientes**

#### Test D.1: Validar expediente
1. Logout y login: `secretario@ucm.cl` / `password`
2. Click en **"Expedientes"**
3. **Verificar:**
   - ✅ Ve solo académicos de FCI (no FCAF)
4. Click en el expediente de `Académico Prueba`
5. **Verificar:**
   - ✅ Ve compromisos APA (S1 y S2)
   - ✅ Ve evidencias cargadas
6. Click en **"Validar expediente"**
7. **Verificar:**
   - ✅ Estado cambia a "validado"

---

### 📍 **PARTE E: Jefe Académico — Informe**

#### Test E.1: Emitir informe
1. Logout y login: `jefe@ucm.cl` / `password`
2. Click en **"Académicos"**
3. Click en el académico validado
4. Llenar informe:
   - Comentario detallado
   - Sugerencia: "Muy Bueno"
5. Click en **"Enviar informe"**
6. **Verificar:**
   - ✅ Informe guardado
   - ✅ Expediente pasa a evaluación CCA

---

### 📍 **PARTE F: Miembro CCA — Evaluación Final**

#### Test F.1: Evaluar académico
1. Logout y login: `cca@ucm.cl` / `password`
2. Click en **"Expedientes"**
3. Click en el académico
4. **Verificar:**
   - ✅ Ve compromisos S1 y S2 (pesos promediados)
   - ✅ Ve informe de jefatura
5. Asignar puntajes (1-7) por cada área:
   - Docencia: 5.5
   - Investigación: 6.0
   - Extensión: 5.0
   - Administración: 5.5
   - Otras: 5.0
6. Click en **"Finalizar evaluación"**
7. **Verificar:**
   - ✅ Calificación final calculada usando pesos (S1+S2)/2
   - ✅ Concepto asignado (Excelente/Muy Bueno/etc.)

---

### 📍 **PARTE G: Vicerrectora — Revisión Final**

#### Test G.1: Comentar evaluación
1. Logout y login: `vicerrectora@ucm.cl` / `password`
2. Click en **"Académicos"**
3. Buscar el académico evaluado
4. Agregar comentario en su evaluación
5. **Verificar:**
   - ✅ Comentario guardado y visible

---

## 🎯 4. CASOS ESPECIALES

### 4.1 Flujo Multifacultad
- **FCI:** Repetir tests C-G con usuarios `@ucm.cl`
- **FCAF:** Repetir tests C-G con usuarios `.fcaf@ucm.cl`
- **Verificar:** Cada secretario/CCA solo ve su facultad

### 4.2 Académico sin declarar nada
Para forzar este flujo, ejecutar:
```bash
docker compose exec app php artisan tinker
> $u = App\Models\User::where('email','academico@ucm.cl')->first();
> $n = App\Models\Nomina::where('user_id', $u->id)->first();
> App\Models\CompromisoApa::where('nomina_id', $n->id)->delete();
> exit
```
Luego login como `academico@ucm.cl` → debe redirigir a declaración S1.

### 4.3 Forzar fecha de cierre S1 pasada
Para probar que S2 se desbloquea cuando S1 cierra:
```bash
docker compose exec app php artisan tinker
> App\Models\SemestreAcademico::where('numero', 1)->update(['fecha_cierre' => now()->subDays(5)]);
> exit
```

### 4.4 Generar reporte de incumplimientos
1. Como `analista@ucm.cl`
2. Ir a **"Incumplimientos"**
3. Ver académicos con nota vencida (los 9 vencidos del CSV)

---

## 🐛 5. TROUBLESHOOTING

### Problema: Error al hacer login
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```

### Problema: Cambios en código no se reflejan
```bash
# Si modificaste archivos React (.jsx)
docker compose restart vite

# Si modificaste archivos PHP
docker compose restart app
```

### Problema: Quiero empezar de cero
```bash
docker compose exec app php artisan migrate:fresh --seed
```

### Problema: La importación CSV no detecta columnas correctas
- Verificar que el CSV tenga encabezados en la primera fila
- Verificar que use **"Categoría 2026"** (no solo "Categoría") para la categoría actual

### Problema: Vencimientos incorrectos
- **Auxiliar:** debe tener fecha categorización en **2025** (vence 2026)
- **Adjunto/Titular:** debe tener fecha categorización en **2024** (vence 2026)
- Verificar que la columna "Categoría 2026" tenga las fechas correctas

---

## ✅ CHECKLIST FINAL DE PRUEBAS

Marca cada test al completarlo:

### Admin
- [ ] Test A.1: Configurar semestres

### Analista CCDA
- [ ] Test B.1: Visualizar nómina (sin botón "Exportar Excelentes", sin "+ Caso")
- [ ] Test B.2: Importar CSV con 20 académicos
- [ ] Test B.3: Verificar vigencias coherentes (Juan Pérez vence 15/03/2026)
- [ ] Test B.4: Agregar columnas manuales al reporte y exportar PDF

### Académico
- [ ] Test C.1: `academico@ucm.cl` accede directo a evidencias (S1 y S2 OK)
- [ ] Test C.2: `academico.fcaf@ucm.cl` redirige a declarar S2
- [ ] Test C.3: Validación de suma 100%

### Secretario
- [ ] Test D.1: Validar expediente FCI

### Jefe Académico
- [ ] Test E.1: Emitir informe

### Miembro CCA
- [ ] Test F.1: Evaluar con pesos promediados S1/S2

### Vicerrectora
- [ ] Test G.1: Comentar evaluación

### Casos especiales
- [ ] Multifacultad FCI/FCAF segregado
- [ ] Académico sin compromisos redirige a S1
- [ ] Forzar cierre S1 desbloquea S2

---

## 📞 Recursos

- **Archivo CSV:** `/Users/tban/Documents/apa-ucm/nomina_academicos_vigencia_realista.csv`
- **URL local:** http://localhost:8080
- **Password universal:** `password`
- **Vite dev:** http://localhost:5173

---

**¡Listo para testing! 🚀**
