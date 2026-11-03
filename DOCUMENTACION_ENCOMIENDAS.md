# 📦 REPORTE DE ENCOMIENDAS - DOCUMENTACIÓN

## 📋 Descripción General

Se ha creado un módulo de reportes para **Encomiendas** que permite consultar, filtrar e imprimir registros de encomiendas de manera centralizada y eficiente, siguiendo el mismo patrón que boletos y viajes.

---

## 🎯 Características

✅ **Filtrado por:**
- Mes y Año
- Estado de Encomienda (Recibido, En Tránsito, Entregado, Cancelado)

✅ **Visualización:**
- Tabla interactiva con datos completos
- Estadísticas resumidas (totales por estado)
- Colores de estados para identificación rápida

✅ **Impresión:**
- Versión optimizada para PDF
- Estilos limpios sin Tailwind
- Apto para impresoras

✅ **Arquitectura:**
- Centralización de datos en `ReporteHelper`
- Servicio `ReporteEncomiendaService` para lógica de negocio
- Controlador con validación y autorización
- Sin redundancias

---

## 📁 Archivos Creados/Modificados

### **Creados:**
1. `app/Services/Reporte/ReporteHelper.php` (MODIFICADO)
   - Métodos nuevos:
     - `estadosEncomiendas()` - Array de estados
     - `coloresEncomiendas()` - Colores por estado
     - `nombreEstadoEncomienda()` - Nombre formateado de estado

2. `app/Services/ReporteEncomiendaService.php` (NUEVO)
   - `obtenerEncomiendas()` - Filtrado inteligente
   - `calcularEstadisticas()` - Métricas del reporte

3. `app/Http/Controllers/Reporte/ReporteController.php` (MODIFICADO)
   - Método `encomiendas()` - Maneja vista e impresión

4. `resources/views/reporte/encomiendas/index.blade.php` (NUEVO)
   - Vista principal con filtros y tabla

5. `resources/views/reporte/encomiendas/imprimir.blade.php` (NUEVO)
   - Vista optimizada para impresión/PDF

---

## 🚀 Cómo Usar

### **1. Agregar Rutas**

Edita `routes/web.php` y agrega:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('reportes')->name('reporte.')->group(function () {
        Route::get('boletos', [\App\Http\Controllers\Reporte\ReporteController::class, 'boletos'])->name('boletos');
        Route::get('viajes', [\App\Http\Controllers\Reporte\ReporteController::class, 'viajes'])->name('viajes');
        Route::get('encomiendas', [\App\Http\Controllers\Reporte\ReporteController::class, 'encomiendas'])->name('encomiendas'); // NUEVA
    });
});
```

### **2. Verificar Permisos**

Asegúrate de que `reportes.ver` existe en tu seeder (generalmente ya está).

```php
// En RolesAndPermissionsSeeder.php, si no existe:
Permission::create(['name' => 'reportes.ver']);
$admin->givePermissionTo('reportes.ver');
```

### **3. Acceder al Reporte**

```
GET /reportes/encomiendas                    // Vista principal
GET /reportes/encomiendas?mes=10&anio=2026   // Filtrado por mes
GET /reportes/encomiendas?estado=entregado   // Filtrado por estado
GET /reportes/encomiendas?mes=10&anio=2026&estado=entregado&imprimir=1  // PDF
```

---

## 📊 Estados de Encomienda

| Estado | Color | Significado |
|--------|-------|------------|
| **recibido** | 🔵 Azul | Encomienda recibida en oficina |
| **en transito** | 🟡 Amarillo | En camino en el viaje |
| **entregado** | 🟢 Verde | Entregada al destinatario |
| **cancelado** | 🔴 Rojo | Transacción cancelada |

---

## 🔍 Ejemplo de Uso

### **Consultar Encomiendas de Octubre de 2026**
```
GET /reportes/encomiendas?mes=10&anio=2026
```

**Respuesta:**
- Tabla con todas las encomiendas de octubre 2026
- Resumen: Total, Entregadas, En Tránsito, Canceladas
- Información: Remitente, Destinatario, Tipo, Cantidad, Total, Estado, Viaje

### **Generar PDF de Encomiendas Entregadas**
```
GET /reportes/encomiendas?mes=10&anio=2026&estado=entregado&imprimir=1
```

**Respuesta:**
- Página HTML optimizada para impresión
- Puede guardarse como PDF desde navegador (Ctrl+P → Guardar PDF)
- Estilos limpios sin colores de Tailwind

---

## 🛠️ Estructura Técnica

### **Flujo de Datos**

```
Request GET /reportes/encomiendas?mes=10&anio=2026&estado=entregado
    ↓
ReporteController::encomiendas()
    ↓ (valida mes, año, estado)
    ↓
ReporteEncomiendaService::obtenerEncomiendas()
    ↓ (construye query con filtros)
    ↓
Encomienda::with(['cliente', 'viaje.ruta', 'tipoEncomienda'])
    ↓
Retorna Collection
    ↓
Blade View (index.blade.php o imprimir.blade.php)
    ↓ (usa ReporteHelper para colores, estados)
    ↓
HTML + Tailwind (web) | HTML + CSS limpio (impresión)
```

---

## 🎨 Detalles de Implementación

### **ReporteHelper (Datos Centralizados)**
```php
ReporteHelper::estadosEncomiendas()  // ['recibido' => 'Recibido', ...]
ReporteHelper::coloresEncomiendas()  // ['recibido' => 'bg-blue-100...', ...]
ReporteHelper::nombreEstadoEncomienda('en transito')  // 'En Tránsito'
```

### **ReporteEncomiendaService (Lógica de Negocio)**
```php
$servicio = new ReporteEncomiendaService();
$encomiendas = $servicio->obtenerEncomiendas($mes, $anio, $estado);
$stats = $servicio->calcularEstadisticas($encomiendas);
```

### **Vistas (Blade)**
- **index.blade.php**: Filtros interactivos + Tabla + Resumen
- **imprimir.blade.php**: Versión limpia para PDF

---

## ✅ Validaciones

- **Mes**: 1-12 (nullable)
- **Año**: 2020-2100 (default actual)
- **Estado**: recibido | en transito | entregado | cancelado (nullable)
- **Permiso**: `reportes.ver` requerido

---

## 🔄 Patrón DRY (Don't Repeat Yourself)

### **Antes:**
- Estados hardcodeados en 2+ vistas
- Colores duplicados en múltiples archivos
- Si cambia un estado, hay que editar 4 lugares

### **Ahora:**
- Un único lugar para estados en `ReporteHelper`
- Un único lugar para colores en `ReporteHelper`
- Cambiar datos = 1 edición, se propaga automáticamente

---

## 📈 Posibles Mejoras Futuras

1. **Exportar a Excel**: Usar `maatwebsite/excel`
2. **Gráficos**: Integrar Chart.js para visualizar estados
3. **Rango de Fechas**: En lugar de solo mes/año
4. **Filtro por Ruta**: Agrupar por ruta de viaje
5. **Caché**: Si los reportes son lentos con muchos datos
6. **API**: Endpoint JSON `/api/reportes/encomiendas` para integración

---

## 🧪 Pruebas

```bash
# Ver reportes en el navegador
GET http://localhost/reportes/encomiendas

# Con filtros
GET http://localhost/reportes/encomiendas?mes=10&anio=2026

# Por estado
GET http://localhost/reportes/encomiendas?estado=entregado

# PDF
GET http://localhost/reportes/encomiendas?mes=10&anio=2026&imprimir=1
```

---

## 📞 Soporte

Si hay problemas:
1. Verifica que las rutas estén agregadas en `routes/web.php`
2. Verifica que el permiso `reportes.ver` exista
3. Revisa que el usuario tenga el permiso asignado
4. Verifica que los directorios de vistas existan

---

## 📝 Notas Técnicas

- **PHP 8.1+**: Usa type hints (`View|Response`)
- **Laravel 10+**: Blade templates modernos
- **Tailwind CSS**: Para estilos web
- **No dependencias externas**: Solo Blade nativo

---

**Fecha de Creación**: 2026-10-08  
**Versión**: 1.0  
**Status**: ✅ Producción Ready
