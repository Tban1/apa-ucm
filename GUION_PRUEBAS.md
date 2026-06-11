# Guion de Pruebas en Vivo — Sistema APA UCM

> Fecha: Junio 2026  
> Rama: `main` — cambios activos en NominaController, Nomina/Create, Nomina/Detalle, Vicerrectora/Dashboard

---

## 0. Antes de empezar

```bash
# En WSL, dentro de src/
php artisan serve      # o el alias que uses
npm run dev            # Vite en paralelo
```

Tener a mano:
- Un archivo Excel SAPD de prueba (ver sección 2.A para columnas requeridas)
- Credenciales de cada rol (ver `database/seeders/`)

---

## 1. LOGIN Y REDIRECCIÓN POR ROL

| Rol | Email ejemplo | Destino esperado |
|---|---|---|
| `analista_ccda` | analista@ucm.cl | `/analista/dashboard` |
| `academico` | academico@ucm.cl | `/academico/declaracion-apa` o `/academico/dashboard` |
| `jefe_academico` | jefe@ucm.cl | `/jefe/dashboard` |
| `secretario` | secretario@ucm.cl | `/secretario/dashboard` |
| `miembro_cca` | cca@ucm.cl | `/cca/dashboard` |
| `vicerrectora` | vicerrectora@ucm.cl | `/vicerrectora/dashboard` |

**Verificar:**
- [ ] Login con credenciales correctas → redirige al dashboard del rol
- [ ] Login con credenciales incorrectas → error visible en pantalla
- [ ] Acceder a URL de otro rol sin permiso → redirige o error 403

---

## 2. ROL: ANALISTA CCDA

### 2.A — Importar nómina desde Excel SAPD

**Ruta:** `/analista/periodos/{id}/nominas/crear`

#### Preparar Excel de prueba

El archivo debe tener en la fila 1 estos encabezados (pueden estar en cualquier orden):

```
N° Personal | Cédula de Identidad | Nombre del Trabajador | Adscripción Académica
Unidad Superior | Unidad | Nombre de la Posición | Tipo de Trabajador
Fecha de Inicio de Contrato | Horas de Contrato
Categoría_2023 | Fecha_Categoría_2023 | Calificación_2023 | Concepto_Resultado_2023
Categoría_2024 | Fecha_Categoría_2024 | Calificación_2024 | Concepto_Resultado_2024
```

Datos de ejemplo para 2 filas:

| N° Personal | Cédula... | Nombre... | Adscripción... | Unidad Superior | ... | Categoría_2024 | Calificación_2024 |
|---|---|---|---|---|---|---|---|
| 12345 | 12.345.678-9 | Juan Pérez Muñoz | Académica | Facultad de Ciencias | ... | adjunto | 6.5 |
| 67890 | 9.876.543-2 | María López Soto | Administrativa | Facultad de Ingeniería | ... | titular | 7.0 |

**Pasos:**

1. Ir a la lista de períodos → `/analista/periodos`
2. Seleccionar un período activo → clic en "Gestionar nómina"
3. En el panel izquierdo **"Importar Excel SAPD"**:
   - [ ] Hacer clic en el selector de archivo y elegir el `.xlsx`
   - [ ] Hacer clic en **"Cargar"**

**Verificar después del Cargar:**
- [ ] Aparece una tabla preview con las primeras 4 filas del Excel
- [ ] Se muestra el mensaje **"X detectados automáticamente"** en verde (deben detectarse al menos RUT, Nombre, categoría, calificaciones si los encabezados coinciden con SAPD)
- [ ] Los campos con auto-detección tienen el ícono **✓** verde al lado del label
- [ ] Si hay columnas no reconocidas → aparece aviso amarillo **"X columna(s) no reconocida(s)"**
- [ ] El botón **"Importar nómina"** está deshabilitado hasta que RUT y Nombre tengan columna asignada

4. Ajustar manualmente los `selects` si algún campo no se detectó
5. Verificar que el checkbox **"La primera fila es encabezado"** esté marcado
6. Clic en **"Importar nómina"**

**Verificar resultado:**
- [ ] Mensaje verde de éxito: **"X académico(s) importado(s)."**
- [ ] Si había filas con RUT duplicado: **"X ya estaban en la nómina (datos actualizados)."**
- [ ] Si hubo filas con RUT o Nombre vacío: **"X fila(s) con errores omitidas."**
- [ ] La tabla de nómina se actualiza con los nuevos registros
- [ ] Las columnas N° Personal, Unidad, Categoría, Fecha Ctto. se rellenan con los datos del Excel

#### Probar importación doble (idempotencia)

- [ ] Importar el mismo archivo por segunda vez
- [ ] Resultado esperado: **"0 importado(s). X ya estaban en la nómina (datos actualizados)."**
- [ ] Los datos de la nómina se actualizan (no se duplican)

#### Probar archivo con columnas mínimas

Subir un Excel con solo `Cédula de Identidad` y `Nombre del Trabajador`:
- [ ] Se importa correctamente con solo los campos requeridos
- [ ] Los campos SAPD opcionales quedan como `—` en la grilla

---

### 2.B — Agregar académico individual

1. Clic en el botón **"+ Agregar académico"** (esquina superior derecha)
2. Aparece modal con formulario
3. Completar:
   - RUT: `11.111.111-1`
   - Nombre: `Prueba Académico Test`
   - Tipo trabajador: `académico`
   - Horas contrato: `44`
   - Categoría: `adjunto`

**Verificar:**
- [ ] Modal se abre correctamente
- [ ] Al enviar → académico aparece en la tabla con estado `pendiente`
- [ ] Mensaje de éxito verde

4. Intentar agregar el mismo RUT nuevamente:
- [ ] Mensaje de error: **"X ya está en la nómina de este período."**

---

### 2.C — Ver detalle de académico

1. En la tabla de nómina, clic en el **nombre** de cualquier académico importado con historial
2. Ruta: `/analista/periodos/{periodo}/nominas/{nomina}/detalle`

**Verificar sección "Datos del académico":**
- [ ] Se muestran todos los campos SAPD: N° Personal, RUT, Nombre, Adscripción, Unidad Superior, Unidad, Posición, Tipo, Fecha Contrato, Horas, Categoría, Fecha Categorización
- [ ] Los campos vacíos muestran `—`

**Verificar sección "Nota vigente":**
- [ ] Si el académico tiene historial de calificaciones → muestra nota, estado (Vigente/Vencida) y fecha de vencimiento
- [ ] Si categoría es `auxiliar` → vigencia 1 año; si `adjunto/titular` → 2 años
- [ ] Si la nota está vigente → fondo verde; si venció → fondo gris

**Verificar "Historial de calificaciones":**
- [ ] Tabla con columnas: Año, Nota, Concepto, Observación, Proceso
- [ ] Filas ordenadas por año descendente
- [ ] Si no hay historial → mensaje "Sin historial importado."

**Verificar "Historial de categorías":**
- [ ] Tabla con columnas: Año, Categoría, Fecha Categorización
- [ ] Si no hay historial → mensaje correspondiente

---

### 2.D — Caso especial (licencia)

1. En la tabla de nómina, clic en **"+ Caso especial"** de cualquier académico
2. Aparece barra inferior con input de motivo
3. Escribir motivo: `Licencia médica prolongada`
4. Clic en **"Confirmar"**

**Verificar:**
- [ ] Badge **"especial"** aparece junto al nombre del académico
- [ ] El contador "Casos especiales" en el panel lateral se actualiza
- [ ] Clic en **"Quitar caso"** lo elimina y el badge desaparece

---

### 2.E — Exportar nómina

1. Clic en **"↓ Exportar Excel"**
2. Se descarga archivo `nomina_TODAS_{anio}.xlsx`

**Verificar:**
- [ ] El archivo se descarga sin errores
- [ ] Abrirlo y confirmar que tiene los académicos de la nómina

3. Clic en **"↓ Plantilla UCM"**

**Verificar:**
- [ ] Se descarga `plantilla_nomina_ucm.xlsx` con las columnas vacías (estructura SAPD)

---

## 3. ROL: ACADÉMICO

### 3.A — Compromiso APA

1. Login como académico
2. Ruta: `/academico/declaracion-apa`

**Verificar:**
- [ ] Formulario de compromiso APA cargado
- [ ] Enviar declaración → redirige a `/academico/dashboard`
- [ ] Si vuelve a `/academico/declaracion-apa` → redirige directo al dashboard (compromiso ya confirmado)

### 3.B — Carga de evidencias

1. Desde `/academico/dashboard`
2. Navegar a `/academico/evidencias`

**Verificar (cuando el plazo está abierto):**
- [ ] Puede subir archivos (PDF, Word, imágenes)
- [ ] Las evidencias aparecen listadas con nombre y fecha
- [ ] Puede eliminar evidencias propias
- [ ] Puede descargar sus propias evidencias

**Verificar (académico con caso especial activo sin plazo individual):**
- [ ] Redirige a `/academico/bloqueado`

---

## 4. ROL: JEFE ACADÉMICO

1. Login como jefe
2. Ruta: `/jefe/academicos`

**Verificar:**
- [ ] Lista de académicos de su facultad
- [ ] Clic en un académico → `/jefe/academicos/{nomina}`
- [ ] Puede ingresar informe de jefatura y guardar
- [ ] Clic en "Imprimir" → genera PDF del informe

---

## 5. ROL: SECRETARIO

### 5.A — Gestión de expedientes

1. Login como secretario
2. Ruta: `/secretario/expedientes`

**Verificar:**
- [ ] Lista de expedientes con estado
- [ ] Clic en un expediente → `/secretario/expedientes/{nomina}`
- [ ] Puede descargar evidencias del académico

### 5.B — Validar expediente

1. Dentro del detalle del expediente:
- [ ] Clic en **"Validar"** → expediente cambia a estado `en_evaluacion`
- [ ] Clic en **"Reabrir"** → vuelve a estado anterior

### 5.C — Configurar plazo de facultad

1. Desde el dashboard secretario, acceder a la sección de plazos:
- [ ] Crear plazo para una facultad con fecha de cierre
- [ ] Verificar que después de la fecha los académicos no pueden subir evidencias

### 5.D — Cierre de proceso

- [ ] **"Cerrar recepción"** → bloquea carga de evidencias
- [ ] **"Cerrar proceso"** → finaliza el período; genera acta de cierre descargable

---

## 6. ROL: MIEMBRO CCA

1. Login como miembro CCA
2. Ruta: `/cca/expedientes`

**Verificar:**
- [ ] Lista de expedientes en evaluación
- [ ] Clic en expediente → `/cca/expedientes/{nomina}`
- [ ] Puede ver evidencias del académico y descargarlas
- [ ] Puede ingresar evaluación (nota y concepto) y guardar
- [ ] Clic en **"Finalizar"** → expediente pasa a `evaluado`
- [ ] Puede generar PDF de calificación

---

## 7. ROL: VICERRECTORA

1. Login como vicerrectora
2. Ruta: `/vicerrectora/dashboard`

**Verificar tabla principal:**
- [ ] Lista de académicos con columnas: Académico, Facultad, Categoría, Nota, Vigencia, Comentario
- [ ] Notas vigentes → badge **verde** con número
- [ ] Notas vencidas → badge **rojo** con número + símbolo `⚠`
- [ ] Académicos sin calificación → badge gris `S/C`
- [ ] Académicos sin nota registrada → badge gris `Pendiente`

**Verificar filtro de facultad:**
- [ ] Selector "Todas las facultades" en la parte superior
- [ ] Al seleccionar una facultad → la tabla filtra en tiempo real (sin recarga)

**Verificar comentario:**
1. Clic en **"Comentar"** de cualquier académico
2. Aparece modal con textarea
3. Ingresar comentario y guardar

**Verificar:**
- [ ] El modal se cierra al guardar
- [ ] El comentario aparece en la columna "Comentario" (truncado si es largo)
- [ ] Volver a abrir el modal del mismo académico → el comentario previo está cargado

**Verificar ver expediente:**
- [ ] Clic en **"Ver expediente"** → redirige a `/vicerrectora/expedientes/{id}`
- [ ] Muestra el detalle completo del académico

---

## 8. FLUJO COMPLETO END-TO-END

Ejecutar en orden para verificar la integración de todos los roles:

```
1. [Analista]    Crear período → Importar nómina Excel SAPD
2. [Académico]   Confirmar compromiso APA → Subir evidencias
3. [Jefe]        Ingresar informe de jefatura
4. [Secretario]  Validar expediente → Cerrar recepción
5. [CCA]         Evaluar expediente → Finalizar evaluación
6. [Secretario]  Cerrar proceso → Descargar acta
7. [Vicerrectora] Revisar panel → Verificar notas → Dejar comentarios
```

**Checkpoints clave:**
- [ ] El estado de la nómina avanza: `pendiente → en_carga → en_evaluacion → evaluado → cerrado`
- [ ] Cada rol solo ve y puede hacer lo que corresponde a su etapa
- [ ] Las notas del historial del Excel importado aparecen en el dashboard de la vicerrectora
- [ ] La vigencia de nota se calcula correctamente según categoría (1 año auxiliar / 2 años resto)

---

## 9. CASOS BORDE A VERIFICAR

| Caso | Pasos | Resultado esperado |
|---|---|---|
| Excel con fechas en formato `d/m/Y` | Importar Excel con `Fecha de Inicio de Contrato` como `15/03/2022` | Se importa correctamente como fecha |
| Excel sin columna Categoría | Omitir columna `Categoría_XXXX` | Importa sin error; campo categoría queda `—` |
| Académico con RUT sin formato | RUT como `12345678` (sin puntos ni guión) | Se importa; verificar si el sistema lo acepta o muestra error |
| Excel con fila vacía al final | Última fila sin datos | Se omite silenciosamente |
| Importar mientras hay academico con caso especial | Reimportar nómina con académico que tiene `con_licencia=true` | Sus datos SAPD se actualizan pero el flag de licencia no se toca |
| Nota vencida en vicerrectora | Académico con calificación del 2022 y categoría auxiliar | Badge rojo con `⚠` en dashboard vicerrectora |

---

## 10. MENSAJES FLASH A VERIFICAR

| Acción | Mensaje esperado |
|---|---|
| Importación exitosa | `"X académico(s) importado(s)."` |
| Importación con duplicados | `"... X ya estaban en la nómina (datos actualizados)."` |
| Agregar individual exitoso | `"Nombre Apellido agregado a la nómina."` |
| Agregar individual duplicado | `"Nombre Apellido ya está en la nómina de este período."` |
| Todos ya en nómina (store manual) | `"Todos los académicos seleccionados ya están en la nómina."` |
| Caso especial registrado | `"Caso especial registrado correctamente."` |
| Caso especial removido | `"Caso especial removido."` |
