<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Boleto de viaje {{ $boleto->id }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background: #f5f5f5; color: #111; font-size: 10px; line-height: 1.25; }
    .ticket { width: 7cm; min-height: 15cm; margin: 20px auto; background: #fff; padding: 9px; border-radius: 6px; overflow-wrap: anywhere; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .logo { text-align: center; margin-bottom: 7px; }
    .logo img { width: 58px; margin-bottom: 2px; }
    .empresa { font-size: 14px; font-weight: bold; }
    .slogan { font-size: 8px; color: #444; }
    .ubicacion { font-size: 8px; color: #444; line-height: 1.3; margin-top: 3px; }
    .linea { border-top: 1px dashed #000; margin: 7px 0; }
    .titulo { text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 7px; }
    .fila { margin-bottom: 5px; }
    .label { font-size: 8px; color: #555; text-transform: uppercase; }
    .valor { font-size: 10px; font-weight: bold; color: #111; }
    .guide { border: 1px solid #555; margin-top: 5px; padding: 6px; }
    .guide-title { border-bottom: 1px solid #777; font-size: 10px; font-weight: bold; margin-bottom: 5px; padding-bottom: 3px; text-align: center; text-transform: uppercase; }
    .guide-row { display: flex; gap: 4px; margin: 3px 0; }
    .guide-row .label { flex: 0 0 31%; }
    .guide-row .valor { flex: 1; }
    .highlight { background: #f3f4f6; padding: 7px 6px; border-radius: 5px; margin: 7px 0; text-align: center; }
    .highlight .label { display: block; }
    .highlight .valor { font-size: 24px; }
    .summary { border: 1px solid #111; margin-top: 7px; }
    .summary-row { display: flex; justify-content: space-between; padding: 4px 6px; gap: 8px; }
    .summary-row + .summary-row { border-top: 1px solid #bbb; }
    .summary-total { background: #111; color: #fff; font-size: 13px; font-weight: bold; }
    .qr { display: block; height: 62px; margin: 8px auto 0; width: 62px; }
    .footer { text-align: center; font-size: 8px; margin-top: 8px; color: #444; }
    .btn { text-align: center; margin-top: 10px; }
    button { padding: 10px 20px; background: #0f172a; color: white; border: none; border-radius: 5px; cursor: pointer; }
    @media print {
        body { background: white; }
        .btn { display: none; }
        .ticket { width: 7cm; margin: 0; padding: 6px; box-shadow: none; }
        @page { size: 70mm 170mm; margin: 0; }
    }
</style>
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
        <div class="summary-row"><span>Estado</span><strong style="text-transform: capitalize;">{{ $boleto->estado }}</strong></div>
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
