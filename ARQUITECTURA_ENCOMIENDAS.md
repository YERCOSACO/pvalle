# 🏗️ ARQUITECTURA - REPORTE DE ENCOMIENDAS

## Diagrama de Componentes

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          USUARIO / NAVEGADOR                            │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                    HTTP GET /reportes/encomiendas
                    ?mes=10&anio=2026&estado=entregado
                                 │
                    ┌────────────▼────────────┐
                    │   Laravel Router        │
                    │  (routes/web.php)       │
                    └────────────┬────────────┘
                                 │
                    ┌────────────▼──────────────────────────┐
                    │  ReporteController::encomiendas()     │
                    │                                       │
                    │  ├─ authorize('reportes.ver')        │
                    │  ├─ validarPeriodo()                 │
                    │  ├─ new ReporteEncomiendaService()   │
                    │  └─ return view() | pdf()            │
                    └────────────┬──────────────────────────┘
                                 │
                    ┌────────────▼──────────────────────────┐
                    │ ReporteEncomiendaService              │
                    │                                       │
                    │ obtenerEncomiendas()                  │
                    │  ├─ Query builder                     │
                    │  ├─ whereYear($anio)                 │
                    │  ├─ whereMonth($mes)                 │
                    │  ├─ where('estado', $estado)         │
                    │  └─ with(...relaciones)              │
                    │                                       │
                    │ calcularEstadisticas()                │
                    │  ├─ Total count                       │
                    │  ├─ Stats por estado                  │
                    │  └─ Ingresos totales                  │
                    └────────────┬──────────────────────────┘
                                 │
                    ┌────────────▼──────────────────────────┐
                    │       Encomienda Model                │
                    │                                       │
                    │  + cliente                            │
                    │  + viaje                              │
                    │  + tipoEncomienda                     │
                    │  + usuario                            │
                    └────────────┬──────────────────────────┘
                                 │
                    ┌────────────▼──────────────────────────┐
                    │      Base de Datos (Query)            │
                    │                                       │
                    │  SELECT * FROM encomiendas            │
                    │  WHERE YEAR(created_at) = 2026        │
                    │  AND MONTH(created_at) = 10           │
                    │  AND estado = 'entregado'             │
                    └────────────┬──────────────────────────┘
                                 │
                    ┌────────────▼──────────────────────────┐
                    │   Resultados (Collection)             │
                    │                                       │
                    │  [Encomienda, Encomienda, ...]        │
                    └────────────┬──────────────────────────┘
                                 │
         ┌───────────────────────┴───────────────────────┐
         │                                               │
         ▼                                               ▼
┌─────────────────────────┐              ┌──────────────────────────┐
│ request('imprimir')?    │              │  ReporteHelper           │
│ No: web                 │              │                          │
│ Sí: pdf                 │              │  ├─ meses()              │
└─────────────────────────┘              │  ├─ estados()            │
         │                               │  ├─ coloresEncomiendas() │
         ▼                               │  ├─ estadosEncomiendas() │
┌─────────────────────────────────────┐  │  └─ nombreEstado...()    │
│   index.blade.php                   │  └──────────────────────────┘
│                                     │           │
│  ├─ Filtros (mes, año, estado)     │           │ (acceso desde views)
│  ├─ Tabla con datos                 │           │
│  ├─ Resumen (stats)                 │           ▼
│  ├─ Tailwind CSS                    │  Datos sincronizados
│  ├─ JavaScript interactivo          │  Único punto de cambio
│  └─ Botón Imprimir                  │
└─────────────────────────────────────┘
         │                       ▲
         │                       │
         │ (imprimir=1)          │ (datos)
         ▼                       │
┌─────────────────────────────────────┐
│   imprimir.blade.php                │
│                                     │
│  ├─ Encabezado limpio               │
│  ├─ Tabla HTML simple               │
│  ├─ CSS para impresión              │
│  ├─ Media queries @print            │
│  └─ Optimizado para PDF             │
└─────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│   PDF / Impresora                   │
│   (Ctrl+P → Guardar PDF)            │
└─────────────────────────────────────┘
```

---

## Ciclo de Vida de una Solicitud

### **Paso 1: Autenticación y Autorización**
```
GET /reportes/encomiendas?mes=10&anio=2026
  │
  ├─ ¿Usuario autenticado? NO → Redirect login
  │
  ├─ ¿Tiene permiso reportes.ver? NO → 403 Forbidden
  │
  └─ ✅ Continuar
```

### **Paso 2: Validación**
```
Parámetros de entrada:
  ├─ mes: 10 ✅ (1-12)
  ├─ anio: 2026 ✅ (2020-2100)
  ├─ estado: "entregado" ✅ (recibido|en transito|entregado|cancelado)
  └─ imprimir: null (booleano, opcional)
```

### **Paso 3: Obtención de Datos**
```
SELECT encomiendas.*, 
       clientes.nombre_completo,
       viajes.fecha_viaje, viajes.hora_salida,
       rutas.nombre_ruta,
       tipo_encomiendas.nombre
FROM encomiendas
JOIN clientes ON encomiendas.cliente_id = clientes.id
JOIN viajes ON encomiendas.viaje_id = viajes.id
JOIN rutas ON viajes.ruta_id = rutas.id
JOIN tipo_encomiendas ON encomiendas.tipo_encomienda_id = tipo_encomiendas.id
WHERE YEAR(encomiendas.created_at) = 2026
  AND MONTH(encomiendas.created_at) = 10
  AND encomiendas.estado = 'entregado'
ORDER BY encomiendas.created_at DESC
```

### **Paso 4: Renderizado**
```
Collection $encomiendas → Blade Template
  │
  ├─ Carga ReporteHelper
  │  ├─ estadosEncomiendas() → colors
  │  └─ coloresEncomiendas() → tailwind classes
  │
  ├─ Itera sobre encomiendas
  │  ├─ Calcula totales
  │  ├─ Aplica colores
  │  └─ Formatea datos
  │
  └─ Retorna HTML/PDF
```

---

## Estructura de Datos - Encomienda

```
Encomienda {
  id                 : int
  cliente_id         : int
  remitente_nombre   : string
  remitente_ci       : string
  remitente_telefono : string
  destinatario_nombre: string
  destinatario_ci    : string
  destinatario_telefono: string
  viaje_id           : int (FK)
  tipo_encomienda_id : int (FK)
  cantidad           : int
  total_pagar        : decimal(8,2)
  estado             : enum['recibido', 'en transito', 'entregado', 'cancelado']
  usuario_id         : int (FK)
  created_at         : timestamp
  updated_at         : timestamp
}

Relaciones:
  ├─ belongsTo(Cliente)
  ├─ belongsTo(Viaje)
  │   └─ hasOne(Ruta)
  │   └─ hasOne(Bus)
  ├─ belongsTo(TipoEncomienda)
  └─ belongsTo(User)
```

---

## Capas de la Aplicación

```
┌──────────────────────────────────────────────────────┐
│  CAPA DE PRESENTACIÓN (View)                         │
│  ├─ index.blade.php      (web)                      │
│  └─ imprimir.blade.php   (pdf)                      │
└──────────────────────────────────────────────────────┘
                     △
                     │
┌──────────────────────────────────────────────────────┐
│  CAPA DE CONTROL (Controller)                        │
│  └─ ReporteController::encomiendas()                │
│     ├─ Autorización                                 │
│     ├─ Validación                                   │
│     └─ Orquestación                                 │
└──────────────────────────────────────────────────────┘
                     △
                     │
┌──────────────────────────────────────────────────────┐
│  CAPA DE NEGOCIO (Services + Helpers)                │
│  ├─ ReporteEncomiendaService                        │
│  │  ├─ obtenerEncomiendas()                        │
│  │  └─ calcularEstadisticas()                       │
│  └─ ReporteHelper (datos compartidos)               │
│     ├─ estadosEncomiendas()                         │
│     └─ coloresEncomiendas()                         │
└──────────────────────────────────────────────────────┘
                     △
                     │
┌──────────────────────────────────────────────────────┐
│  CAPA DE DATOS (Models + Database)                   │
│  ├─ Encomienda (model)                              │
│  ├─ Cliente (model)                                 │
│  ├─ Viaje (model)                                   │
│  ├─ Ruta (model)                                    │
│  ├─ TipoEncomienda (model)                          │
│  └─ Base de Datos (encomiendas table)               │
└──────────────────────────────────────────────────────┘
```

---

## Puntos de Extensión

### **1. Agregar Nuevo Filtro**
```php
// En ReporteEncomiendaService::obtenerEncomiendas()
if ($ruta_id) {
    $query->whereHas('viaje.ruta', function ($q) use ($ruta_id) {
        $q->where('id', $ruta_id);
    });
}
```

### **2. Agregar Nueva Columna a Reporte**
```blade
<!-- En index.blade.php -->
<th>Peso Total</th>
<td>{{ $encomienda->cantidad * 5 }}kg</td>
```

### **3. Agregar Nuevo Estado**
```php
// En ReporteHelper
'en transito' => 'En Tránsito',
'entregado'   => 'Entregado',
'cancelado'   => 'Cancelado',
'devuelto'    => 'Devuelto', // NUEVO
```

### **4. Agregar Exportación Excel**
```php
// En ReporteController::encomiendas()
if ($request->boolean('excel')) {
    return Excel::download(new EncomiendasExport($encomiendas), 'reportes.xlsx');
}
```

---

## Dependencias y Relaciones

```
ReporteController
    ├─ uses → ReporteEncomiendaService
    │         ├─ queries → Encomienda
    │         │           ├─ belongsTo → Cliente
    │         │           ├─ belongsTo → Viaje
    │         │           │             └─ belongsTo → Ruta
    │         │           ├─ belongsTo → TipoEncomienda
    │         │           └─ belongsTo → User
    │         └─ returns → Collection<Encomienda>
    │
    └─ uses → ReporteHelper
              ├─ staticMethod → meses()
              ├─ staticMethod → estadosEncomiendas()
              ├─ staticMethod → coloresEncomiendas()
              └─ staticMethod → nombreEstadoEncomienda()

Blade Templates
    ├─ include → ReporteHelper (helpers)
    ├─ iterate → Collection $encomiendas
    ├─ display → colored badges
    └─ render → HTML or PDF
```

---

## Flujo de Filtrado

```
┌─────────────────────────────────────────┐
│ URL: /reportes/encomiendas?             │
│      mes=10&                            │
│      anio=2026&                         │
│      estado=entregado&                  │
│      imprimir=1                         │
└────────────────┬────────────────────────┘
                 │
     ┌───────────▼──────────┐
     │ validarPeriodo()     │
     │                      │
     │ mes = 10 ✅           │
     │ anio = 2026 ✅        │
     └───────────┬──────────┘
                 │
     ┌───────────▼──────────────────────────┐
     │ obtenerEncomiendas(10, 2026, null)  │
     │                                      │
     │ $query = Encomienda::query()         │
     │                                      │
     │ $query->whereYear('created_at', 2026)
     │ ✅ Filtra por año                    │
     │                                      │
     │ $query->whereMonth('created_at', 10)
     │ ✅ Filtra por mes                    │
     │                                      │
     │ $query->where('estado', 'entregado')
     │ ✅ Filtra por estado                 │
     │                                      │
     │ return $query->get()                 │
     │ → Collection[Encomienda, ...]       │
     └───────────┬──────────────────────────┘
                 │
     ┌───────────▼──────────┐
     │ ¿imprimir == 1?      │
     │                      │
     │ Sí → imprimir.blade  │
     │ No  → index.blade    │
     └──────────────────────┘
```

---

## Optimizaciones Aplicadas

### **1. Eager Loading (sin N+1)**
```php
->with(['cliente', 'viaje.ruta', 'tipoEncomienda', 'usuario'])
// En lugar de:
// $encomiendas->cliente    // 1 query por encomienda
// $encomiendas->viaje      // 1 query por encomienda
// etc...
```

### **2. Índices de Base de Datos**
```sql
-- Recomendado agregar:
ALTER TABLE encomiendas ADD INDEX idx_created_at (created_at);
ALTER TABLE encomiendas ADD INDEX idx_estado (estado);
ALTER TABLE encomiendas ADD INDEX idx_viaje_id (viaje_id);
```

### **3. Caché de Helper (opcional futuro)**
```php
Cache::remember('estados_encomiendas', 3600, function () {
    return ReporteHelper::estadosEncomiendas();
});
```

---

## Tabla de Responsabilidades

| Componente | Responsabilidad | Por Qué |
|-----------|-----------------|--------|
| **Controller** | Autorización, validación, orquestación | Controla entrada/salida |
| **Service** | Lógica de negocio, queries | Reutilizable, testeable |
| **Helper** | Datos compartidos, constantes | Punto único de cambio |
| **Blade** | Presentación, iteración, formateo | Solo responsable de UI |

---

**Arquitectura CLARA: Separación de responsabilidades, fácil mantenimiento, escalable** ✅
