# Plan: Reporte de Recargos - Normativa Colombiana (CST)

## Contexto

El sistema BiometricIP registra asistencia pero no calcula recargos laborales (nocturno, dominical, festivo, extras). Se necesita un **reporte** que desglose las horas trabajadas en conceptos de recargo segun el Codigo Sustantivo del Trabajo colombiano, configurable por empresa (tenant).

**Jornada nocturna CST:** 7:00 PM a 6:00 AM

### Conceptos de Recargo

| Codigo | Concepto | Porcentaje | Extra | Nocturno | Festivo |
|--------|----------|------------|-------|----------|---------|
| NOCT_ORD | Recargo nocturno ordinario | 35% | No | Si | No |
| DOM_FEST_DIUR | Recargo dominical/festivo diurno | 90% | No | No | Si |
| NOCT_DOM | Recargo nocturno dominical | 125% | No | Si | Si |
| EXTRA_DIUR | Hora extra diurna | 125% | Si | No | No |
| EXTRA_NOCT | Hora extra nocturna | 175% | Si | Si | No |
| EXTRA_DIUR_DOM | H. extra diurna dominical/festiva | 215% | Si | No | Si |
| EXTRA_NOCT_DOM | Extra nocturna dominical | 265% | Si | Si | Si |
| FEST_DIUR | Hora festiva diurna | 190% | No | No | Si |
| FEST_NOCT | Hora festiva nocturna | 225% | No | Si | Si |

### Ejemplo de turnos

**Turno Dia T1:** Lun 8:30am-6:30pm, Mar-Jue 6:30am-6:30pm
- No genera recargos (todo dentro de jornada diurna ordinaria 6am-7pm)

**Turno Noche T2:** Mie-Vie 6:30pm-6:30am, Sab 6:30pm-4:30am
- Mie-Vie: 11h recargo nocturno ordinario (7pm-6am = 11h)
- Sabado que termina domingo: 5h nocturno ordinario (7pm-12am sabado) + 4.5h festiva nocturna (12am-4:30am domingo)

---

## Paso 1: Script SQL - Tablas de recargos

**Archivo:** `biometricip_api/database/scripts/040_create_tbl_conceptos_recargo.sql`

### Que hacer:
1. Crear tabla `tbl_conceptos_recargo` en BD tenant:
   - `id` INT PK AUTO_INCREMENT
   - `codigo` VARCHAR(30) NOT NULL UNIQUE (ej: 'NOCT_ORD')
   - `nombre` VARCHAR(100) NOT NULL
   - `porcentaje` DECIMAL(6,2) NOT NULL (ej: 35.00)
   - `es_extra` TINYINT(1) DEFAULT 0
   - `es_nocturno` TINYINT(1) DEFAULT 0
   - `es_festivo` TINYINT(1) DEFAULT 0
   - `is_active` TINYINT(1) DEFAULT 1
   - `created_at` TIMESTAMP NULL
   - `updated_at` TIMESTAMP NULL

2. Crear tabla `tbl_recargos_calculados` en BD tenant:
   - `id` BIGINT UNSIGNED PK AUTO_INCREMENT
   - `user_id` BIGINT UNSIGNED NOT NULL
   - `fecha` DATE NOT NULL
   - `horario_id` BIGINT UNSIGNED NULL
   - `concepto_recargo_id` INT NOT NULL (FK -> tbl_conceptos_recargo)
   - `minutos` DECIMAL(8,2) NOT NULL DEFAULT 0
   - `created_at` TIMESTAMP NULL
   - `updated_at` TIMESTAMP NULL
   - UNIQUE KEY `uq_user_fecha_concepto` (user_id, fecha, concepto_recargo_id)

3. INSERT de los 9 conceptos CST con sus porcentajes

4. INSERT INTO `tbl_admin_tenant` para ambas tablas:
   - `tbl_conceptos_recargo`: es_bd_central=0, copiar_estructura=1, copiar_datos=1
   - `tbl_recargos_calculados`: es_bd_central=0, copiar_estructura=1, copiar_datos=0

### Referencia:
- Ver patron en `biometricip_api/database/scripts/034_create_tbl_capacitaciones.sql`

---

## Paso 2: Script SQL - Permisos

**Archivo:** `biometricip_api/database/scripts/041_add_recargos_permissions.sql`

### Que hacer:
1. Insertar permisos Spatie en `permissions`:
   - `recargos.ver`
   - `recargos.configurar`
   - `recargos.calcular`

2. Asignar los 3 permisos al rol "Super Admin"

### Referencia:
- Ver patron en `biometricip_api/database/scripts/033_add_permissions_resumen_mensual.sql`

---

## Paso 3: Modelos Eloquent

### 3a. ConceptoRecargo

**Archivo:** `biometricip_api/app/Models/ConceptoRecargo.php`

```
- $connection = 'tenant'
- $table = 'tbl_conceptos_recargo'
- $fillable = ['codigo', 'nombre', 'porcentaje', 'es_extra', 'es_nocturno', 'es_festivo', 'is_active']
- Relacion: hasMany(RecargoCalculado::class, 'concepto_recargo_id')
```

### 3b. RecargoCalculado

**Archivo:** `biometricip_api/app/Models/RecargoCalculado.php`

```
- $connection = 'tenant'
- $table = 'tbl_recargos_calculados'
- $fillable = ['user_id', 'fecha', 'horario_id', 'concepto_recargo_id', 'minutos']
- Relaciones:
  - belongsTo(ConceptoRecargo::class, 'concepto_recargo_id')
  - belongsTo(Horario::class, 'horario_id')
```

### Referencia:
- Seguir patron de `biometricip_api/app/Models/Festivo.php`

---

## Paso 4: Fix bug turnos nocturnos

**Archivo:** `biometricip_api/app/Http/Controllers/AdminController.php`
**Lineas:** 500-502

### Problema actual:
```php
$minEsperados = (((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE))
    - (int)($diaHorario->duracion_almuerzo_min ?? 0);
$minEsperados = max(0, $minEsperados);
```
Para turno 22:00-06:00: (6*60) - (22*60) = 360 - 1320 = -960 -> max(0, -960) = 0

### Solucion:
```php
$diff = ((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE);
if ($diff < 0) $diff += 1440; // +24 horas para turnos que cruzan medianoche
$minEsperados = max(0, $diff - (int)($diaHorario->duracion_almuerzo_min ?? 0));
```
Para turno 22:00-06:00: -960 + 1440 = 480 minutos = 8 horas (correcto)

---

## Paso 5: Servicio de calculo de recargos

**Archivo:** `biometricip_api/app/Services/RecargoCalculatorService.php`

### Metodo principal: `calcular(array $userIds, Carbon $desde, Carbon $hasta): array`

1. **Cargar datos:**
   - Registros de asistencia del periodo (reusar query de `AdminController::resumenMensualData()` linea 413-434)
   - Festivos: `Festivo::whereBetween('fecha', [$desde, $hasta])->pluck('fecha')`
   - Conceptos activos: `ConceptoRecargo::where('is_active', 1)->get()->keyBy('codigo')`

2. **Agrupar registros** por empleado + fecha (reusar logica lineas 438-449)

3. **Emparejar entrada/salida** (reusar logica lineas 459-468):
   ```
   entrada -> salida = sesion de trabajo
   ```

4. **Para cada sesion, dividir en segmentos** cortando en:
   - Cada medianoche (00:00) -> separa dias calendario
   - 06:00 -> inicio jornada diurna
   - 19:00 -> inicio jornada nocturna

5. **Clasificar cada segmento** segun la matriz de conceptos

6. **Persistir resultados** en `tbl_recargos_calculados` con upsert

### Metodo: `splitIntoSegments(Carbon $start, Carbon $end): array`

Divide una sesion de trabajo en segmentos homogeneos.

**Ejemplo:** Sesion 18:30 sabado -> 04:30 domingo

Puntos de corte: 19:00 (inicio nocturno), 00:00 (medianoche)

Resultado:
```
Segmento 1: 18:30-19:00 sabado   -> DIURNO,   sabado     = 30 min
Segmento 2: 19:00-00:00 sabado   -> NOCTURNO, sabado     = 300 min (5h)
Segmento 3: 00:00-04:30 domingo  -> NOCTURNO, domingo    = 270 min (4.5h)
```

### Metodo: `classifySegment(segment, festivos): string`

Dado un segmento, retorna el codigo del concepto:

```
                    | Dia ordinario (L-S) | Domingo/Festivo
--------------------|---------------------|------------------
Diurno regular      | (sin recargo, 0%)   | FEST_DIUR (190%)
Nocturno regular    | NOCT_ORD (35%)      | FEST_NOCT (225%)
Diurno extra        | EXTRA_DIUR (125%)   | EXTRA_DIUR_DOM (215%)
Nocturno extra      | EXTRA_NOCT (175%)   | EXTRA_NOCT_DOM (265%)
```

### Determinacion de horas extras:

1. Obtener minutos contratados del dia desde `HorarioDia` (horario_id + dia_semana)
2. Si `hora_entrada` es NULL -> dia no laboral -> todas las horas son extras
3. Total trabajado en el dia - minutos contratados = minutos extras (si > 0)
4. Las extras se clasifican segun la franja horaria donde ocurren
5. Los minutos regulares se asignan primero, los extras son los que sobran

---

## Paso 6: Controller - Metodos de recargos

**Archivo:** `biometricip_api/app/Http/Controllers/AdminController.php`

Agregar despues de `resumenMensualData()` (linea 517):

### `recargosIndex(): View`
- `abort_unless(auth()->user()->can('recargos.ver'), 403)`
- Cargar sedes, horarios, conceptos activos
- Retornar vista `admin.recargos.index`

### `recargosData(Request $request): JsonResponse`
- Validar: `anio` (required int), `mes` (required int 1-12)
- Filtros opcionales: `user_id`, `sede_id`
- Instanciar `RecargoCalculatorService`
- Llamar `calcular(userIds, desde, hasta)`
- Retornar JSON:
```json
{
  "data": [
    {
      "user_id": 1,
      "nombre": "Juan Perez",
      "codigo_empleado": "E001",
      "departamento_id": 2,
      "conceptos": {
        "NOCT_ORD": 660,
        "FEST_NOCT": 270
      },
      "total_recargo_min": 930
    }
  ],
  "conceptos": [
    {"codigo": "NOCT_ORD", "nombre": "Recargo nocturno ordinario", "porcentaje": 35}
  ]
}
```

### `recargosConceptosIndex(): View`
- `abort_unless(auth()->user()->can('recargos.configurar'), 403)`
- Cargar todos los conceptos
- Retornar vista `admin.recargos.conceptos`

### `recargosConceptosUpdate(Request $request): JsonResponse`
- Validar array de `conceptos.*.id`, `conceptos.*.porcentaje`, `conceptos.*.is_active`
- Actualizar cada concepto
- Retornar success

---

## Paso 7: Rutas web

**Archivo:** `biometricip_api/routes/web.php`

Agregar despues de las rutas de resumen-mensual:

```php
// Recargos
Route::get('/admin/recargos',            [AdminController::class, 'recargosIndex'])->name('admin.recargos.index');
Route::get('/admin/recargos/data',       [AdminController::class, 'recargosData'])->name('admin.recargos.data');
Route::get('/admin/recargos/conceptos',  [AdminController::class, 'recargosConceptosIndex'])->name('admin.recargos.conceptos');
Route::put('/admin/recargos/conceptos',  [AdminController::class, 'recargosConceptosUpdate'])->name('admin.recargos.conceptos.update');
```

---

## Paso 8: Vista - Reporte de Recargos

**Archivo:** `biometricip_api/resources/views/admin/recargos/index.blade.php`

### Estructura (seguir patron de resumen_mensual/index.blade.php):

1. **Encabezado:** Titulo "Reporte de Recargos" + boton "Exportar CSV"

2. **Filtros (card):**
   - Mes (select)
   - Anio (input number)
   - Empleado (select, solo para admins)
   - Departamento (select, solo para admins)
   - Boton "Calcular" + "Limpiar"

3. **KPI Cards (fila de 4):**
   - Total empleados con recargos
   - Total horas recargo del mes
   - Promedio recargos por empleado
   - Conceptos activos

4. **Tabla DataTable:**
   - Columna fija izquierda: Empleado (sticky)
   - Columna: Codigo empleado
   - Una columna por cada concepto activo (header: nombre concepto + porcentaje)
   - Contenido celda: horas:minutos (ej: "11:00")
   - Columna fija derecha: Total Recargos (sticky)
   - Si no hay minutos en un concepto: celda vacia/gris

5. **Export CSV:** Mismo patron que resumen mensual

### Flujo JS:
```
1. Al hacer clic en "Calcular":
   - Mostrar spinner
   - fetch('/admin/recargos/data?anio=X&mes=Y&...')
   - Construir thead dinamico con conceptos
   - Construir filas con datos por empleado
   - Inicializar DataTable
   - Mostrar KPIs
```

---

## Paso 9: Vista - Configuracion de Conceptos

**Archivo:** `biometricip_api/resources/views/admin/recargos/conceptos.blade.php`

### Estructura:
1. **Encabezado:** "Conceptos de Recargo" + boton "Guardar cambios"
2. **Tabla:**
   - Codigo (texto, readonly)
   - Nombre (texto, readonly)
   - Porcentaje (input number, step=0.01, editable)
   - Activo (toggle switch)
3. **Boton guardar:** PUT a `/admin/recargos/conceptos` con JSON de conceptos modificados

---

## Paso 10: Menu lateral (Sidebar)

**Archivo:** `biometricip_api/resources/views/layouts/admin.blade.php`

### 10a. Seccion Asistencia (despues de "Resumen Mensual", linea 227):
```blade
@can('recargos.ver')
<li class="side-nav-item">
    <a href="{{ route('admin.recargos.index') }}" class="side-nav-link {{ request()->routeIs('admin.recargos.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-receipt-2"></i></span>
        <span class="menu-text">Recargos</span>
    </a>
</li>
@endcan
```

### 10b. Seccion Configuracion (despues de "Festivos", linea 307):
```blade
@can('recargos.configurar')
<li class="side-nav-item">
    <a href="{{ route('admin.recargos.conceptos') }}" class="side-nav-link {{ request()->routeIs('admin.recargos.conceptos') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-percentage"></i></span>
        <span class="menu-text">Conceptos Recargo</span>
    </a>
</li>
@endcan
```

### 10c. Actualizar @canany de Asistencia (linea 201):
Agregar `'recargos.ver'` a la lista

### 10d. Actualizar @canany de Configuracion (linea 289):
Agregar `'recargos.configurar'` a la lista

---

## Resumen de archivos

| # | Accion | Archivo |
|---|--------|---------|
| 1 | CREAR | `database/scripts/040_create_tbl_conceptos_recargo.sql` |
| 2 | CREAR | `database/scripts/041_add_recargos_permissions.sql` |
| 3 | CREAR | `app/Models/ConceptoRecargo.php` |
| 4 | CREAR | `app/Models/RecargoCalculado.php` |
| 5 | CREAR | `app/Services/RecargoCalculatorService.php` |
| 6 | CREAR | `resources/views/admin/recargos/index.blade.php` |
| 7 | CREAR | `resources/views/admin/recargos/conceptos.blade.php` |
| 8 | EDITAR | `app/Http/Controllers/AdminController.php` |
| 9 | EDITAR | `routes/web.php` |
| 10 | EDITAR | `resources/views/layouts/admin.blade.php` |

Todos los archivos dentro de `biometricip_api/`.

---

## Verificacion

1. Ejecutar script SQL 040 en BD tenant de prueba
2. Ejecutar script SQL 041 en BD tenant de prueba
3. Verificar fix overnight: turno 22:00-06:00 debe mostrar 8h esperadas en resumen mensual
4. Crear horarios T1 (Dia) y T2 (Noche) segun el ejemplo
5. Registrar asistencia de prueba para ambos turnos
6. Ir a `/admin/recargos` -> seleccionar mes -> clic "Calcular"
7. **T1 debe dar 0 recargos** (todo diurno ordinario L-J)
8. **T2 debe dar:**
   - Mie-Vie: 11h recargo nocturno ordinario cada dia (7pm-6am)
   - Sabado: 5h nocturno ordinario (7pm-12am) + 4.5h festiva nocturna (12am-4:30am domingo)
9. Ir a `/admin/recargos/conceptos` -> cambiar porcentaje -> guardar -> verificar
10. Exportar CSV y validar datos
