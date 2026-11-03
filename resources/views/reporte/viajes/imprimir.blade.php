<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de viajes - {{ $desde }} al {{ $hasta }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; line-height: 1.4; }
        h1 { font-size: 18px; margin-bottom: 4px; font-weight: bold; }
        h2 { font-size: 13px; font-weight: normal; margin-bottom: 14px; color: #555; }
        .header { display: flex; align-items: center; gap: 18px; border-bottom: 2px solid #0f766e; padding-bottom: 14px; }
        .header img { width: 105px; height: 70px; object-fit: contain; }

        .resumen { display: flex; gap: 24px; margin-bottom: 12px; flex-wrap: wrap; }
        .resumen div { border: 1px solid #ccc; padding: 6px 12px; background: #f9f9f9; }
        .resumen b { display: block; font-size: 14px; color: #222; }

        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #eee; font-weight: bold; text-align: left; padding: 6px; border: 1px solid #bbb; }
        td { padding: 4px 6px; border: 1px solid #bbb; }
        th.num, td.num { text-align: right; }

        .no-print { margin-bottom: 12px; padding: 8px 12px; background: #f0f0f0; border: none; cursor: pointer; border-radius: 4px; }
        .no-print:hover { background: #e0e0e0; }

        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Imprimir / Guardar como PDF</button>

    <header class="header">
        @if (file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo de la empresa">
        @endif
        <div>
            <h1>Reporte de viajes</h1>
            <h2>
                Salidas del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}
                @if($viajeSeleccionado)
                    · Viaje: {{ $viajeSeleccionado->ruta?->nombre_ruta }} ({{ $viajeSeleccionado->fecha_viaje?->format('d/m/Y') }})
                @endif
                @if($buscar)
                    · Búsqueda: {{ $buscar }}
                @endif
                · Generado el {{ now()->format('d/m/Y H:i') }}
            </h2>
        </div>
    </header>

    <div class="resumen">
        <div>Viajes: <b>{{ $resumen['total'] }}</b></div>
        <div>Boletos confirmados: <b>{{ $resumen['vendidos'] }}</b></div>
        <div>Asientos ofertados: <b>{{ $resumen['capacidad'] }}</b></div>
        <div>Ocupación: <b>{{ $resumen['ocupacion'] }}%</b></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ruta</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Bus</th>
                <th class="num">Capacidad</th>
                <th class="num">Vendidos</th>
                <th class="num">Ocupación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($viajes as $viaje)
                @php($cap = $viaje->bus?->capacidad ?? 0)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $viaje->ruta?->nombre_ruta ?? 'N/A' }}</td>
                    <td>{{ $viaje->fecha_viaje?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $viaje->hora_salida ? substr($viaje->hora_salida, 0, 5) : '—' }}</td>
                    <td>{{ $viaje->bus?->placa ?? 'N/A' }}</td>
                    <td class="num">{{ $cap }}</td>
                    <td class="num">{{ $viaje->vendidos }}</td>
                    <td class="num">{{ $cap > 0 ? round($viaje->vendidos * 100 / $cap) : 0 }}%</td>
                    <td>{{ ucfirst($viaje->estado) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                        Sin viajes en las fechas seleccionadas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
