<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Boleto de viaje {{ $boleto->id }}</title>
@vite('resources/css/print/travel-ticket.css')
</head>
<body>
<div class="ticket">
    <div class="logo">
        @if (file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo Mi Valle">
        @endif
        <div class="empresa">TRANS COMARAPA</div>
        <div class="slogan">Transporte seguro y confiable</div>
        <div class="ubicacion">Doble Vía La Guardia, 4to Anillo<br>Esquina frente a la Plaza Gran Plaza Las Palmas<br>N° 23</div>
    </div>

    <div class="linea"></div>
    <div class="titulo">BOLETO DE VIAJE</div>

    <div class="guide">
        <div class="guide-title">Detalle del pasajero</div>
        <div class="guide-row"><div class="label">N° boleto</div><div class="valor">BOL-{{ str_pad($boleto->id, 6, '0', STR_PAD_LEFT) }}</div></div>
        <div class="guide-row"><div class="label">Pasajero</div><div class="valor">{{ $boleto->nombre_pasajero }}</div></div>
        <div class="guide-row"><div class="label">CI</div><div class="valor">{{ $boleto->ci_pasajero ?? '—' }}</div></div>
        <div class="guide-row"><div class="label">Teléfono</div><div class="valor">{{ $boleto->telefono_pasajero ?? '—' }}</div></div>
    </div>

    <div class="linea"></div>
    <div class="guide">
        <div class="guide-title">Datos del viaje</div>
        <div class="guide-row"><div class="label">Ruta</div><div class="valor">{{ $boleto->viaje->ruta->origen }} → {{ $boleto->viaje->ruta->destino }}</div></div>
        <div class="guide-row"><div class="label">Fecha</div><div class="valor">{{ \Carbon\Carbon::parse($boleto->viaje->fecha_viaje)->format('d/m/Y') }}</div></div>
        <div class="guide-row"><div class="label">Hora</div><div class="valor">{{ $boleto->viaje->hora_salida }}</div></div>
        <div class="guide-row"><div class="label">Bus</div><div class="valor">{{ $boleto->viaje->bus->placa }}</div></div>
    </div>

    <div class="highlight">
        <span class="label">Asiento asignado</span>
        <span class="valor">{{ $boleto->numero_asiento }}</span>
    </div>

    <div class="summary">
        <div class="summary-row"><span>Método de pago</span><strong>{{ $boleto->metodo_pago }}</strong></div>
        <div class="summary-row"><span>Estado</span><strong class="estado-valor">{{ $boleto->estado }}</strong></div>
        <div class="summary-row summary-total"><span>Total a pagar</span><span>Bs {{ number_format($boleto->precio, 2) }}</span></div>
    </div>

    <div class="footer">
        <p>Escanea para identificar este boleto.</p>
        <img class="qr" src="https://quickchart.io/qr?text={{ urlencode('BOL-' . str_pad($boleto->id, 6, '0', STR_PAD_LEFT) . ' | ' . $boleto->nombre_pasajero . ' | Asiento ' . $boleto->numero_asiento) }}&size=150" alt="Código QR del boleto">
        <p>Gracias por viajar con Mi Valle.</p>
    </div>

    <div class="btn"><button onclick="window.print()">Imprimir</button></div>
</div>
<script>
window.onload = function() { setTimeout(() => window.print(), 500); };
</script>
</body>
</html>
