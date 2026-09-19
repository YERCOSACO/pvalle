<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de asientos - {{ $viaje->ruta->nombre_ruta }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 28px; color: #172033; font-family: Arial, Helvetica, sans-serif; background: #edf1f5; }
        .reporte { max-width: 1060px; margin: auto; padding: 34px; background: #fff; border-top: 6px solid #0f766e; box-shadow: 0 12px 35px rgba(15, 23, 42, .08); }
        .encabezado { display: flex; justify-content: space-between; gap: 24px; border-bottom: 1px solid #cbd5e1; padding-bottom: 20px; }
        .logo { width: 120px; height: 78px; object-fit: contain; object-position: left center; margin-bottom: 10px; }
        .marca { color: #0f766e; font-size: 11px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }
        h1 { margin: 0 0 8px; font-size: 26px; letter-spacing: -.5px; }
        h2 { margin: 0 0 7px; font-size: 18px; color: #334155; }
        p { margin: 5px 0; font-size: 13px; }
        .fecha { color: #64748b; text-align: right; line-height: 1.4; }
        .resumen { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 24px 0; }
        .resumen div { border: 1px solid #d7e0e8; border-left: 4px solid #0f766e; padding: 13px; background: #f8fafc; }
        .resumen div:last-child { border-left-color: #c58b28; }
        .resumen strong { display: block; font-size: 23px; }
        .resumen span { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; }
        .tabla-titulo { display: flex; justify-content: space-between; align-items: center; margin: 26px 0 10px; }
        .tabla-titulo h3 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: .8px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border-bottom: 1px solid #d7dce5; padding: 10px; text-align: left; }
        th { background: #172033; color: #fff; font-size: 11px; letter-spacing: .3px; text-transform: uppercase; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .check { width: 44px; text-align: center; }
        input[type="checkbox"] { width: 17px; height: 17px; accent-color: #0f766e; }
        .estado { font-weight: bold; text-transform: capitalize; }
        .disponible { color: #047857; }
        .ocupado { color: #be123c; }
        .pendiente { color: #a16207; }
        .pie { margin-top: 24px; color: #5b6475; font-size: 11px; }
        .acciones { display: flex; justify-content: center; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        button { border: 0; border-radius: 4px; padding: 12px 20px; color: #fff; font-size: 12px; font-weight: bold; letter-spacing: .2px; cursor: pointer; }
        .btn-informe { background: #0f766e; }
        .btn-tabla { background: #172033; }
        .btn-informe:hover { background: #115e59; }
        .btn-tabla:hover { background: #334155; }
        @media print {
            body { padding: 0; background: #fff; }
            .reporte { max-width: none; padding: 0; }
            .acciones { display: none; }
            @page { size: A4 portrait; margin: 12mm; }
        }
        @media (max-width: 700px) {
            body { padding: 10px; }
            .reporte { padding: 16px; }
            .encabezado { display: block; }
            .fecha { margin-top: 12px; text-align: left; }
            .resumen { grid-template-columns: repeat(2, 1fr); }
            .acciones { flex-direction: column; }
        }
    </style>
</head>
<body>
    @php
        $total = $asientos->count();
        $ocupados = $asientos->whereIn('estado', ['ocupado', 'bloqueado'])->count();
        $pendientes = $boletos->where('estado', 'pendiente')->count();
        $disponibles = max(0, $total - $ocupados);
        $totalGenerado = $boletos->where('estado', 'confirmado')->sum('precio');
    @endphp

    <main class="reporte">
        <section class="informe-seccion">
            <header class="encabezado">
        <div class="encabezado">
            @if (file_exists(public_path('images/logo.png')))
                <img class="logo" src="{{ asset('images/logo.png') }}" alt="Logo Mi Valle">
            @endif

            <div class="info-encabezado">
                <h1>Reporte de asientos</h1>
                <h2>{{ $viaje->ruta->nombre_ruta }}</h2>
                <p>
                Bus: {{ $viaje->bus->placa }} |
                Capacidad: {{ $viaje->bus->capacidad }} asientos
                </p>
                <p>
                <strong>Conductores asignados:</strong>
                @forelse($viaje->asignaciones as $asignacion)
                    {{ $asignacion->conductor?->nombre_completo ?? $asignacion->nombre_ayudante ?? 'Ayudante' }}
                    ({{ $asignacion->tipo_asignacion }}){{ !$loop->last ? ', ' : '' }}
                @empty
                    Sin conductores asignados
                @endforelse
                </p>
            </div>
        </div>
            <div class="fecha">
                <p><strong>Fecha del viaje:</strong> {{ $viaje->fecha_viaje->format('d/m/Y') }}</p>
                <p><strong>Hora de salida:</strong> {{ $viaje->hora_salida }}</p>
                <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            </header>

            <section class="resumen">
                <div><strong>{{ $total }}</strong><span>Total asientos</span></div>
                <div><strong>{{ $disponibles }}</strong><span>Disponibles</span></div>
                <div><strong>{{ $ocupados }}</strong><span>Ocupados</span></div>
                <div><strong>Bs {{ number_format($totalGenerado, 2) }}</strong><span>Total generado</span></div>
            </section>
        </section>

        <section class="tabla-seccion">
            <div class="tabla-titulo">
                <h3>Detalle de asientos</h3>
                <span class="fecha">Marque cada asiento revisado</span>
            </div>
            <table>
            <thead>
                <tr>
                    <th class="check">OK</th>
                    <th>#</th>
                    <th>Asiento</th>
                    <th>Estado del asiento</th>
                    <th>Pasajero</th>
                    <th>CI</th>
                    <th>Estado del boleto</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asientos as $asiento)
                    @php
                        $claveAsiento = strtoupper(trim((string) $asiento->numero_asiento));
                        $boleto = $boletos[$claveAsiento] ?? null;
                        $estadoAsiento = $asiento->estado;
                        $estadoBoleto = $boleto?->estado ?? 'sin boleto';
                    @endphp
                    <tr>
                        <td class="check"><input type="checkbox" aria-label="Asiento {{ $asiento->numero_asiento }} revisado"></td>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $asiento->numero_asiento }}</strong></td>
                        <td class="estado {{ $estadoAsiento }}">{{ $estadoAsiento }}</td>
                        <td>{{ $boleto?->nombre_pasajero ?? 'Sin pasajero' }}</td>
                        <td>{{ $boleto?->ci_pasajero ?? '—' }}</td>
                        <td class="estado {{ $estadoBoleto }}">{{ ucfirst($estadoBoleto) }}</td>
                        <td>{{ $boleto?->estado === 'confirmado' ? 'Bs ' . number_format($boleto->precio, 2) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </section>

        <div class="acciones">
            <button type="button" class="btn-tabla" onclick="window.print()">IMPRIMIR</button>
        </div>
    </main>
</body>
</html>
