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
        color: #000;
    }
    .ticket {
        width: 7cm;
        min-height: 17cm;
        margin: 20px auto;
        background: #fff;
        padding: 12px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .logo {
        text-align: center;
        margin-bottom: 10px;
    }
    .logo img {
        width: 60px;
        margin-bottom: 5px;
    }
    .empresa {
        font-size: 16px;
        font-weight: bold;
    }
    .slogan {
        font-size: 10px;
        color: #555;
    }
    .linea {
        border-top: 1px dashed #000;
        margin: 10px 0;
    }
    .titulo {
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        margin-bottom: 10px;
    }
    .fila {
        margin-bottom: 8px;
    }
    .label {
        font-size: 10px;
        color: #555;
    }
    .valor {
        font-size: 12px;
        font-weight: bold;
        color: #111;
    }
    .highlight {
        background: #f3f4f6;
        padding: 8px;
        border-radius: 8px;
        margin: 8px 0;
        font-size: 12px;
    }
    .total {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-top: 10px;
    }
    .footer {
        text-align: center;
        font-size: 9px;
        margin-top: 14px;
        color: #444;
    }
    .btn {
        text-align: center;
        margin-top: 20px;
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
            padding: 8px;
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
        <div class="empresa">TRANS VALLE</div>
        <div class="slogan">Envíos seguros y rápidos</div>
    </div>

    <div class="linea"></div>
    <div class="titulo">TICKET DE ENCOMIENDA</div>

    <div class="fila">
        <div class="label">N° ENCOMIENDA</div>
        <div class="valor">ENV-{{ str_pad($encomienda->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="fila">
        <div class="label">REMITENTE</div>
        <div class="valor">{{ $encomienda->nombre_remitente }}</div>
    </div>
    @if($encomienda->remitente_ci)
    <div class="fila">
        <div class="label">CI remitente</div>
        <div class="valor">{{ $encomienda->remitente_ci }}</div>
    </div>
    @endif
    @if($encomienda->remitente_telefono)
    <div class="fila">
        <div class="label">Teléfono remitente</div>
        <div class="valor">{{ $encomienda->remitente_telefono }}</div>
    </div>
    @endif

    <div class="linea"></div>

    <div class="fila">
        <div class="label">DESTINATARIO</div>
        <div class="valor">{{ $encomienda->destinatario_nombre }}</div>
    </div>
    @if($encomienda->destinatario_ci)
    <div class="fila">
        <div class="label">CI destinatario</div>
        <div class="valor">{{ $encomienda->destinatario_ci }}</div>
    </div>
    @endif
    @if($encomienda->destinatario_telefono)
    <div class="fila">
        <div class="label">Teléfono destinatario</div>
        <div class="valor">{{ $encomienda->destinatario_telefono }}</div>
    </div>
    @endif

    <div class="linea"></div>

    <div class="fila">
        <div class="label">RUTA</div>
        <div class="valor">{{ $encomienda->viaje->ruta->origen }} → {{ $encomienda->viaje->ruta->destino }}</div>
    </div>
    <div class="fila">
        <div class="label">FECHA</div>
        <div class="valor">{{ \Carbon\Carbon::parse($encomienda->viaje->fecha_viaje)->format('d/m/Y') }}</div>
    </div>
    <div class="fila">
        <div class="label">HORA</div>
        <div class="valor">{{ $encomienda->viaje->hora_salida }}</div>
    </div>

    <div class="linea"></div>

    <div class="highlight">
        <div class="label">Tipo</div>
        <div class="valor">{{ $encomienda->tipoEncomienda->nombre }}</div>
    </div>

    <div class="highlight">
        <div class="label">Cantidad</div>
        <div class="valor">{{ $encomienda->cantidad }}</div>
    </div>

    <div class="highlight">
        <div class="label">Total</div>
        <div class="valor">Bs {{ number_format($encomienda->total_pagar, 2) }}</div>
    </div>

    <div class="fila">
        <div class="label">Estado</div>
        <div class="valor" style="text-transform: capitalize;">{{ $encomienda->estado }}</div>
    </div>

    <div class="linea"></div>

    <div class="footer">
        Gracias por confiar en nosotros.<br>
        Trans Valle
    </div>

    <div class="btn">
        <button onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<script>
window.onload = function() {
    setTimeout(() => window.print(), 500);
};
</script>
</body>
</html>
