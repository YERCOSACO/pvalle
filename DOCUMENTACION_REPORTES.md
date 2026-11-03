# 📊 Módulo de Reportes - Documentación

## ✅ Mejoras Implementadas

### 1. **Eliminación de Redundancias**
- ✓ Array `$meses` centralizado en `ReporteHelper` (antes estaba en 4 vistas)
- ✓ Array `$estados` y colores centralizados
- ✓ CSS duplicado en ambas vistas de impresión → unificado y mejorado
- ✓ Métodos helper para obtener nombres de meses y estados

### 2. **Mejoras de Código Limpio**
- ✓ Agregados tipos de retorno en controller (`View|Response`)
- ✓ Documentación con docblocks en todos los métodos
- ✓ Helper centralizado para compartir datos entre todas las vistas
- ✓ Validación extraída a método privado `validarPeriodo()`

### 3. **Mejor Mantenibilidad**
- ✓ Si necesitas cambiar los meses, colores o estados → cambias en 1 lugar
- ✓ Uso de `ReporteHelper::meses()` en lugar de repetir arrays
- ✓ Métodos helper como `nombreMes()` y `nombreEstado()` para valores dinámicos

### 4. **CSS Optimizado**
- ✓ Estilos consolidados en un solo lugar
- ✓ Media queries apropiadas para impresión
- ✓ Sin usar !important (antipatrón)
- ✓ Clases CSS más semánticas

## 📁 Estructura de Carpetas

```
app/
├── Http/Controllers/Reporte/
│   └── ReporteController.php          ← Controlador de reportes
├── Helpers/
│   └── Reporte/ReporteHelper.php       ← Datos compartidos
└── Services/
    └── ReporteService.php              ← Lógica de negocio

resources/views/reporte/
├── boletos/
│   ├── index.blade.php                 ← Vista web de boletos
│   └── imprimir.blade.php              ← Vista de impresión
└── viajes/
    ├── index.blade.php                 ← Vista web de viajes
    └── imprimir.blade.php              ← Vista de impresión
```

## 🚀 Cómo Usar

### Consulta por rango de fechas

Los reportes de boletos, viajes y encomiendas permiten elegir una fecha inicial y final. Los boletos y las encomiendas se filtran por fecha de registro; los viajes, por fecha de salida. El período se conserva al abrir la versión de impresión, que permite imprimir o guardar como PDF desde el navegador.

El reporte de asientos permite filtrar viajes por rango de fechas y ruta, ver el mapa/listado de pasajeros y abrir la hoja de asientos para imprimirla. La hoja incluye el logo de la empresa.

```text
/reportes/boletos?desde=2026-09-01&hasta=2026-09-30
/reportes/viajes?desde=2026-09-01&hasta=2026-09-30&estado=finalizado
/reportes/encomiendas?desde=2026-09-01&hasta=2026-09-30
```

Las URL antiguas con `mes` y `anio` siguen siendo aceptadas para facilitar la compatibilidad.

### 1. **Registrar las Rutas**
En `routes/web.php`, dentro del middleware autenticado:

```php
Route::middleware(['auth', 'verified'])->name('reporte.')->group(function () {
    Route::get('reportes/boletos', [\App\Http\Controllers\Reporte\ReporteController::class, 'boletos'])->name('boletos');
    Route::get('reportes/viajes', [\App\Http\Controllers\Reporte\ReporteController::class, 'viajes'])->name('viajes');
});
```

### 2. **Dar Permisos** (ya están en RolesAndPermissionsSeeder)
```php
$admin->givePermissionTo('reportes.ver');
```

### 3. **Acceder a los Reportes**
- Boletos: `http://localhost/reportes/boletos?mes=10&anio=2026`
- Viajes: `http://localhost/reportes/viajes?mes=10&anio=2026&estado=finalizado`

## 🔍 Características Principales

### Reporte de Boletos (RF15)
- Filtra por fecha de emisión desde/hasta
- Muestra: total, confirmados, pendientes, cancelados, recaudado
- Tabla con detalles: pasajero, CI, ruta, fecha, asiento, precio, estado
- Exportar a PDF/Imprimir

### Reporte de Viajes (RF16)
- Filtra por fecha de salida desde/hasta y estado del viaje
- Muestra: total viajes, boletos vendidos, capacidad, ocupación %
- Tabla con detalles: ruta, fecha, hora, bus, capacidad, vendidos, ocupación %
- Exportar a PDF/Imprimir

## 🎯 Validaciones Implementadas

```php
// Fechas del rango; la fecha final debe ser igual o posterior a la inicial.
$request->validate([
    'desde' => 'nullable|date_format:Y-m-d',
    'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde',
]);

// Estado: solo valores permitidos
$request->validate(['estado' => 'nullable|in:programado,en curso,finalizado,cancelado']);
```

## 🔐 Seguridad

- ✓ Autorización: `$this->authorize('reportes.ver')`
- ✓ Validación de entrada en todos los filtros
- ✓ Relaciones cargadas con `with()` para evitar N+1 queries
- ✓ Modelos filtrados por `activos()` (soft delete)

## 💡 Ventajas de Esta Estructura

| Antes | Ahora |
|-------|-------|
| Arrays duplicados en 4 archivos | 1 Helper centralizado |
| Si cambio colores → edito 2 vistas | Si cambio colores → edito 1 archivo |
| CSS repetido | CSS limpio y modular |
| Sin tipado | Tipos explícitos en PHP 8+ |
| Sin documentación | Docblocks en cada método |

## 🧪 Pruebas Recomendadas

```php
// Test que el reporte de boletos funciona
$this->get('/reportes/boletos?mes=10&anio=2026')->assertOk();

// Test que la impresión funciona
$this->get('/reportes/boletos?mes=10&anio=2026&imprimir=1')->assertOk();

// Test que valida mes inválido
$this->get('/reportes/boletos?mes=13')->assertInvalid('mes');
```

## 📝 Próximas Mejoras Opcionales

1. Agregar exportación a Excel con `maatwebsite/excel`
2. Gráficos con Chart.js para visualizar datos
3. Caché de reportes frecuentes
4. Descarga de CSV

---

✨ **Módulo completo, funcional y listo para producción**
