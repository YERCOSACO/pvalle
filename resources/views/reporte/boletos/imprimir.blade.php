<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de boletos - {{ $desde }} al {{ $hasta }}</title>
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
            <h1>Reporte de boletos</h1>
            <h2>
                Emisión del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}
                @if($buscar)
                    · Búsqueda: {{ $buscar }}
                @endif
                · Generado el {{ now()->format('d/m/Y H:i') }}
            </h2>
        </div>
    </header>

    <div class="resumen">
        <div>Boletos: <b>{{ $resumen['total'] }}</b></div>
        <div>Confirmados: <b>{{ $resumen['confirmados'] }}</b></div>
        <div>Pendientes: <b>{{ $resumen['pendientes'] }}</b></div>
        <div>Cancelados: <b>{{ $resumen['cancelados'] }}</b></div>
        <div>Recaudado: <b>Bs {{ number_format($resumen['recaudado'], 2) }}</b></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Emitido</th>
                <th>Pasajero</th>
                <th>CI</th>
                <th>Ruta</th>
                <th>Fecha viaje</th>
                <th>Asiento</th>
                <th>Pago</th>
                <th class="num">Precio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boletos as $boleto)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $boleto->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $boleto->nombre_pasajero ?: 'No registrado' }}</td>
                    <td>{{ $boleto->ci_pasajero ?? '—' }}</td>
                    <td>{{ $boleto->viaje?->ruta?->nombre_ruta ?? 'No disponible' }}</td>
                    <td>
                        {{ $boleto->viaje?->fecha_viaje?->format('d/m/Y') ?? '—' }}
                        @if($boleto->viaje?->hora_salida)
                            <br>{{ substr($boleto->viaje->hora_salida, 0, 5) }}
                        @endif
                    </td>
                    <td>{{ $boleto->numero_asiento ?: '—' }}</td>
                    <td>{{ $boleto->metodo_pago ?: '—' }}</td>
                    <td class="num">Bs {{ number_format((float) $boleto->precio, 2) }}</td>
                    <td>{{ ucfirst($boleto->estado ?? 'Sin estado') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #999;">Sin boletos en las fechas seleccionadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
