<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de asientos - {{ $viaje->ruta->nombre_ruta }}</title>
    @vite('resources/css/print/seat-report.css')
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
