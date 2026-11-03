# 🏗️ ARQUITECTURA DEL MÓDULO DE REPORTES

## Flujo de Datos

```
┌─────────────────────────────────────────────────────────────────┐
│                         USUARIO FINAL                           │
│                      (Cliente Web Browser)                       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              HTTP REQUEST (GET /reportes/boletos)               │
│                  ?mes=10&anio=2026&imprimir=1                   │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│           ReporteController (app/Http/Controllers)              │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ 1. boletos(Request $request): View|Response             │   │
│  │    ├─ Autorizar: $this->authorize('reportes.ver')       │   │
│  │    ├─ Validar: validarPeriodo($request)                 │   │
│  │    └─ Llamar Service                                    │   │
│  │                                                          │   │
│  │ 2. viajes(Request $request): View|Response              │   │
│  │    ├─ Autorizar: $this->authorize('reportes.ver')       │   │
│  │    ├─ Validar: estado, mes, año                         │   │
│  │    └─ Llamar Service                                    │   │
│  │                                                          │   │
│  │ 3. validarPeriodo(Request): array                        │   │
│  │    ├─ Validar mes (1-12)                                │   │
│  │    └─ Validar año (2020-2100)                           │   │
│  └──────────────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
                ▼                         ▼
   ┌──────────────────────┐   ┌──────────────────────┐
   │   ReporteService     │   │  ReporteHelper       │
   │  (Lógica de Negocio) │   │ (Datos Compartidos)  │
   ├──────────────────────┤   ├──────────────────────┤
   │ • boletosPorMesAnio()│   │ • meses()            │
   │ • viajesPorMesAnio() │   │ • estados()          │
   │                      │   │ • coloresBoletos()   │
   │ Retorna:             │   │ • coloresViajes()    │
   │ [                    │   │ • nombreMes()        │
   │   'boletos' => [...] │   │ • nombreEstado()     │
   │   'resumen' => [...] │   │                      │
   │ ]                    │   │ Usado por:           │
   │                      │   │ - Todas las vistas   │
   │ Consultas:           │   │ - Componentes        │
   │ Boleto::activos()    │   │ - Helpers            │
   │ Viaje::activos()     │   │                      │
   └──────────────────────┘   └──────────────────────┘
                │
                └────────────┬────────────┐
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATABASE                                  │
│           (Modelos: Boleto, Viaje, Ruta, Bus)                   │
└─────────────────────────────────────────────────────────────────┘
                             │
            ┌────────────────┼────────────────┐
            │                │                │
            ▼                ▼                ▼
    ┌────────────────┐  ┌──────────────┐  ┌──────────────┐
    │   Boletos      │  │   Viajes     │  │ Rutas/Buses  │
    │   (Confirmado) │  │   (Estado)   │  │ (Capacidad)  │
    │   (Pendiente)  │  │ (con count)  │  │              │
    │   (Cancelado)  │  │              │  │              │
    └────────────────┘  └──────────────┘  └──────────────┘
            │                │                │
            └────────────────┼────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              Seleccionar Vista (index o imprimir)                │
│                                                                  │
│  Si imprimir=1:                  Si imprimir=0:                 │
│  → reporte.boletos.imprimir      → reporte.boletos.index        │
│  → reporte.viajes.imprimir       → reporte.viajes.index         │
└────────────────────────────┬────────────────────────────────────┘
                             │
          ┌──────────────────┼──────────────────┐
          │                  │                  │
          ▼                  ▼                  ▼
    ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
    │  BLADE VIEW  │   │  HTML PRINT  │   │   Imprime    │
    │  (Tailwind)  │   │  (Plain CSS) │   │  en Navegador│
    │              │   │              │   │              │
    │ • Filtros    │   │ • Tabla      │   │ • PDF        │
    │ • Tabla      │   │ • Resumen    │   │ • Papel A4   │
    │ • Resumen    │   │ • Estilos    │   │              │
    │              │   │   optimizados│   │              │
    └──────────────┘   └──────────────┘   └──────────────┘
          │                  │                  │
          └──────────────────┼──────────────────┘
                             │
                             ▼
            ┌────────────────────────────────┐
            │      RESPONSE USUARIO           │
            │                                │
            │ - Página web con tabla y       │
            │   filtros dinámicos            │
            │ - O documento imprimible       │
            │ - Con estilos optimizados      │
            └────────────────────────────────┘
```

## 📦 Dependencias

```
ReporteController
├── ReporteService (inyectado)
│   ├── Boleto model
│   ├── Viaje model
│   ├── Ruta model
│   └── Bus model
└── ReporteHelper (usado en vistas)
    ├── meses()
    ├── estados()
    ├── coloresBoletos()
    ├── coloresViajes()
    ├── nombreMes()
    └── nombreEstado()
```

## 🔄 Ciclo de Vida - Reporte de Boletos

```
1. Usuario accede a: GET /reportes/boletos?mes=10&anio=2026&imprimir=1
                            │
                            ▼
2. ReporteController::boletos()
   ├─ $this->authorize('reportes.ver')    ✅ Verificar permisos
   │
   ├─ [$mes, $anio] = $this->validarPeriodo($request)
   │  └─ Valida que mes sea 1-12 y año 2020-2100
   │
   ├─ $datos = $this->reportes->boletosPorMesAnio($mes, $anio)
   │  └─ Retorna ['boletos' => [...], 'resumen' => [...]]
   │
   ├─ $vista = $request->boolean('imprimir') 
   │           ? 'reporte.boletos.imprimir'     ← imprimir=1
   │           : 'reporte.boletos.index'         ← mostrar
   │
   └─ return view($vista, $datos + compact('mes', 'anio'))
                            │
                            ▼
3. Vista Blade compila:
   ├─ use App\Services\Reporte\ReporteHelper
   ├─ $meses = ReporteHelper::meses()
   ├─ $colores = ReporteHelper::coloresBoletos()
   │
   ├─ @foreach($boletos as $boleto)
   │  └─ Renderiza cada boleto con colores
   │
   ├─ Suma resumen
   │  └─ Total, confirmados, pendientes, cancelados, recaudado
   │
   └─ Envía HTML al navegador
                            │
                            ▼
4. Navegador recibe HTML
   ├─ Si NO es impresión:
   │  └─ Muestra Tailwind CSS con tabla interactiva
   │
   ├─ Si ES impresión:
   │  ├─ Muestra HTML plano con CSS print-optimizado
   │  ├─ Usuario presiona Ctrl+P
   │  └─ Exporta a PDF/Papel
                            │
                            ▼
5. ✅ Reporte completado y exportado
```

## 🎯 Puntos de Extensión

Si necesitas agregar funcionalidades:

```
📌 Agregar nuevo filtro (ej: por ciudad)
   └─ Editar: ReporteController::validarPeriodo()
   └─ Editar: ReporteService::boletosPorMesAnio()

📌 Cambiar colores
   └─ Editar: ReporteHelper::coloresBoletos()
   └─ Todas las vistas se actualizan automáticamente ✨

📌 Agregar nueva columna a tabla
   └─ Editar: reporte.boletos.index (y .imprimir)
   └─ Nada más necesita cambiar

📌 Agregar exportación a Excel
   └─ Nuevo método: ReporteController::exportarBoletos()
   └─ Usar: maatwebsite/excel con datos de ReporteService

📌 Agregar gráficos
   └─ Nueva ruta: GET /reportes/boletos/grafico
   └─ Usar: Chart.js en la vista
   └─ Datos desde ReporteService::boletosPorMesAnio()
```

## 🔒 Capas de Seguridad

```
┌─ HTTP Request
│  └─ Validación de entrada (mes, año, estado)
│     ├─ Tipos de datos
│     └─ Rangos permitidos
│
├─ Controller
│  └─ $this->authorize('reportes.ver')  🔐
│
├─ Service
│  └─ Acceso solo a modelos activos (soft delete)
│     ├─ Boleto::activos()
│     └─ Viaje::activos()
│
└─ Database
   └─ Nivel de acceso controlado
      ├─ Solo admins
      ├─ Solo personas con permiso 'reportes.ver'
      └─ Soft delete protege datos
```

---

**✨ Arquitectura completa, segura y escalable**
