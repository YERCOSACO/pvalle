<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket Encomienda {{ $encomienda->id }}</title>
@vite('resources/css/print/parcel-ticket.css')
</head>
<body>
<div class="ticket">
    <div class="logo">
        @if (file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        @endif
        <div class="empresa">TRANS COMARAPA</div>
        <div class="slogan">Envíos seguros y rápidos</div>
        <div class="ubicacion">Doble Vía La Guardia, 4to Anillo<br>Esquina frente a la Plaza Gran Plaza Las Palmas<br>N° 23</div>
    </div>

    <div class="linea"></div>
    <div class="titulo">BOLETO DE ENCOMIENDA</div>

    <div class="issued-by">
        <div class="label">Registrado por</div>
        <div class="valor">{{ $encomienda->usuario?->name ?? 'No registrado' }}</div>
    </div>

    <div class="guide">
        <div class="guide-title">Guía electrónica de carga</div>
        <div class="guide-row"><div class="label">N° guía</div><div class="valor">ENV-{{ str_pad($encomienda->id, 6, '0', STR_PAD_LEFT) }}</div></div>
        <div class="guide-row"><div class="label">Remitente</div><div class="valor">{{ $encomienda->nombre_remitente }}</div></div>
    @if($encomienda->remitente_ci)
        <div class="guide-row"><div class="label">CI</div><div class="valor">{{ $encomienda->remitente_ci }}</div></div>
    @endif
    @if($encomienda->remitente_telefono)
        <div class="guide-row"><div class="label">Teléfono</div><div class="valor">{{ $encomienda->remitente_telefono }}</div></div>
    @endif
        <div class="guide-row"><div class="label">Destinatario</div><div class="valor">{{ $encomienda->destinatario_nombre }}</div></div>
    @if($encomienda->destinatario_ci)
        <div class="guide-row"><div class="label">CI</div><div class="valor">{{ $encomienda->destinatario_ci }}</div></div>
    @endif
    @if($encomienda->destinatario_telefono)
        <div class="guide-row"><div class="label">Teléfono</div><div class="valor">{{ $encomienda->destinatario_telefono }}</div></div>
    @endif
        <div class="guide-row"><div class="label">Origen</div><div class="valor">{{ $encomienda->viaje->ruta->origen }}</div></div>
        <div class="guide-row"><div class="label">Destino</div><div class="valor">{{ $encomienda->viaje->ruta->destino }}</div></div>
        <div class="guide-row"><div class="label">Fecha / hora</div><div class="valor">{{ \Carbon\Carbon::parse($encomienda->viaje->fecha_viaje)->format('d/m/Y') }} / {{ $encomienda->viaje->hora_salida }}</div></div>
    </div>
    <div class="linea"></div>
    <div class="summary">
        <div class="summary-row"><span>Tipo de encomienda</span><strong>{{ $encomienda->tipoEncomienda->nombre }}</strong></div>
        <div class="summary-row"><span>Cantidad</span><strong>{{ $encomienda->cantidad }}</strong></div>
        <div class="summary-row summary-total"><span>Total a pagar</span><span>Bs {{ number_format($encomienda->total_pagar, 2) }}</span></div>
    </div>
    <div class="fila">
        <div class="label">Estado</div>
        <div class="valor estado-valor">{{ $encomienda->estado }}</div>
    </div>
    <div class="footer">
    <p>Visítanos y controla tu encomienda.</p>
    <img class="qr" src="https://quickchart.io/qr?text={{ urlencode('ENV-' . str_pad($encomienda->id, 6, '0', STR_PAD_LEFT) . ' | ' . $encomienda->destinatario_nombre . ' | ' . $encomienda->viaje->ruta->origen . ' - ' . $encomienda->viaje->ruta->destino) }}&size=150" alt="Código QR de la encomienda">
</div>

    <div class="btn">
        <button onclick="window.print()">Imprimir</button>
    </div>
</div>

<script>
window.onload = function() {
    setTimeout(() => window.print(), 500);
};
</script>
</body>
</html>
