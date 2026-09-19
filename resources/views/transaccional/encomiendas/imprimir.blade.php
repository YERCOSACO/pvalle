<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket Encomienda {{ $encomienda->id }}</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f5f5f5;
        color: #111;
        font-size: 10px;
        line-height: 1.25;
    }
    .ticket {
        width: 7cm;
        min-height: 15cm;
        margin: 20px auto;
        background: #fff;
        padding: 9px;
        border-radius: 6px;
        overflow-wrap: anywhere;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .logo {
        text-align: center;
        margin-bottom: 7px;
    }
    .logo img {
        width: 42px;
        margin-bottom: 2px;
    }
    .empresa {
        font-size: 14px;
        font-weight: bold;
    }
    .slogan {
        font-size: 8px;
        color: #444;
    }
    .ubicacion {
        font-size: 8px;
        line-height: 1.2;
        margin-top: 4px;
    }
    .linea {
        border-top: 1px dashed #000;
        margin: 7px 0;
    }
    .titulo {
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 7px;
    }
    .fila {
        margin-bottom: 5px;
    }
    .label {
        font-size: 8px;
        color: #555;
        text-transform: uppercase;
    }
    .valor {
        font-size: 10px;
        font-weight: bold;
        color: #111;
    }
    .highlight {
        background: #f3f4f6;
        padding: 5px 6px;
        border-radius: 5px;
        margin: 5px 0;
        font-size: 11px;
    }
    .total {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-top: 10px;
    }
    .footer {
        text-align: center;
        font-size: 8px;
        margin-top: 8px;
        color: #444;
    }
    .btn {
        text-align: center;
        margin-top: 10px;
    }
    .section-title {
        border-bottom: 1px solid #111;
        font-size: 9px;
        font-weight: bold;
        margin: 7px 0 5px;
        padding-bottom: 2px;
        text-transform: uppercase;
    }
    .guide {
        border: 1px solid #555;
        margin-top: 5px;
        padding: 6px;
    }
    .guide-title {
        border-bottom: 1px solid #777;
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 5px;
        padding-bottom: 3px;
        text-align: center;
        text-transform: uppercase;
    }
    .guide-row {
        display: flex;
        gap: 4px;
        margin: 3px 0;
    }
    .guide-row .label {
        flex: 0 0 31%;
    }
    .guide-row .valor {
        flex: 1;
    }
    .issued-by {
        border-top: 1px dashed #777;
        margin-top: 6px;
        padding-top: 5px;
        text-align: center;
    }
    .summary {
        border: 1px solid #111;
        margin-top: 7px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 6px;
    }
    .summary-row + .summary-row {
        border-top: 1px solid #bbb;
    }
    .summary-total {
        background: #111;
        color: #fff;
        font-size: 13px;
        font-weight: bold;
    }
    .qr {
        display: block;
        height: 58px;
        margin: 5px auto 0;
        width: 58px;
    }
    button {
        padding: 10px 20px;
        background: #0f172a;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    @media print {
        body {
            background: white;
        }
        .btn {
            display: none;
        }
        .ticket {
            width: 7cm;
            margin: 0;
            padding: 6px;
            box-shadow: none;
        }
        @page {
            size: 70mm 170mm;
            margin: 0;
        }
    }
</style>
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
        <div class="valor" style="text-transform: capitalize;">{{ $encomienda->estado }}</div>
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
