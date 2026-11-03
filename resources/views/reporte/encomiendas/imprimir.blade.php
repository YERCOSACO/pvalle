<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Encomiendas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: #333;
            line-height: 1.6;
            padding: 24px;
        }

        .print-action {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #0f766e;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            text-align: left;
            margin-bottom: 30px;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 18px;
        }

        .header img {
            width: 105px;
            height: 70px;
            object-fit: contain;
        }

        .header h1 {
            font-size: 24px;
            color: #0f2926;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #7f8c8d;
        }

        .filters {
            margin-bottom: 25px;
            padding: 12px 16px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 12px;
        }

        .filters strong {
            color: #0f2926;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        thead {
            background: #0f3d31;
            color: white;
        }

        th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0f3d31;
        }

        td {
            padding: 8px 10px;
            border: 1px solid #bdc3c7;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #ecf0f1;
        }

        .estado {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 3px;
            text-align: center;
            font-size: 10px;
        }

        .estado-recibido {
            background: #d1ecf1;
            color: #0c5460;
        }

        .estado-en-transito {
            background: #fff3cd;
            color: #856404;
        }

        .estado-entregado {
            background: #d4edda;
            color: #155724;
        }

        .estado-cancelado {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #bdc3c7;
            font-size: 10px;
            color: #7f8c8d;
            text-align: center;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 3px;
            text-align: center;
        }

        .summary-card h3 {
            font-size: 11px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #0f2926;
        }

        @media print {
            body {
                margin: 0;
                padding: 8mm;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-action {
                display: none;
            }

            .header {
                page-break-after: avoid;
            }

            table {
                page-break-inside: auto;
                font-size: 9px;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <button type="button" class="print-action" onclick="window.print()">Imprimir / Guardar como PDF</button>

    <div class="header">
        @if (file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa">
        @endif
        <div>
            <h1>Reporte de Encomiendas</h1>
            <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="filters">
        <p>
            <strong>Fecha de registro:</strong> {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }} |
            <strong>Viaje:</strong> {{ $viajeSeleccionado ? $viajeSeleccionado->ruta?->nombre_ruta . ' · ' . $viajeSeleccionado->fecha_viaje?->format('d/m/Y') : 'Todos' }}
            @if($buscar)
                | <strong>Búsqueda:</strong> {{ $buscar }}
            @endif
        </p>
    </div>

    <!-- Resumen -->
    <div class="summary">
        <div class="summary-card">
            <h3>Total Encomiendas</h3>
            <div class="value">{{ $encomiendas->count() }}</div>
        </div>
        <div class="summary-card">
            <h3>Recibidas</h3>
            <div class="value">{{ $encomiendas->where('estado', 'recibido')->count() }}</div>
        </div>
        <div class="summary-card">
            <h3>Entregadas</h3>
            <div class="value">{{ $encomiendas->where('estado', 'entregado')->count() }}</div>
        </div>
        <div class="summary-card">
            <h3>En Tránsito</h3>
            <div class="value">{{ $encomiendas->where('estado', 'en transito')->count() }}</div>
        </div>
        <div class="summary-card">
            <h3>Canceladas</h3>
            <div class="value">{{ $encomiendas->where('estado', 'cancelado')->count() }}</div>
        </div>
        <div class="summary-card">
            <h3>Total registrado</h3>
            <div class="value">Bs {{ number_format((float) $encomiendas->sum('total_pagar'), 2) }}</div>
        </div>
    </div>

    <!-- Tabla de Encomiendas -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 12%;">Remitente</th>
                <th style="width: 12%;">Destinatario</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 6%;">Cant.</th>
                <th style="width: 10%;">Total</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 18%;">Viaje</th>
                <th style="width: 12%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($encomiendas as $encomienda)
            <tr>
                <td>#{{ $encomienda->id }}</td>
                <td>{{ $encomienda->nombre_remitente ?? 'N/A' }}</td>
                <td>{{ $encomienda->destinatario_nombre }}</td>
                <td>{{ $encomienda->tipoEncomienda?->nombre ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $encomienda->cantidad }}</td>
                <td style="text-align: right;">Bs {{ number_format((float) $encomienda->total_pagar, 2) }}</td>
                <td>
                    <span class="estado estado-{{ str_replace(' ', '-', $encomienda->estado) }}">
                        {{ \App\Services\Reporte\ReporteHelper::nombreEstadoEncomienda($encomienda->estado) }}
                    </span>
                </td>
                <td>
                    {{ $encomienda->viaje?->ruta?->nombre_ruta ?? 'N/A' }}<br>
                    <small>{{ $encomienda->viaje?->fecha_viaje?->format('d/m/Y') ?? 'N/A' }}</small>
                </td>
                <td>{{ $encomienda->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 24px; color: #64748b;">
                    No hay encomiendas para los filtros seleccionados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pie de página -->
    <div class="footer">
        <p>Sistema de Reportes - {{ config('app.name') }} | Página generada automáticamente</p>
    </div>
</body>
</html>
