<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Boleto {{ $boleto->id }}</title>

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        font-family: Arial, Helvetica, sans-serif;
        background:#f5f5f5;
    }

    .ticket{
        width:7cm;
        min-height:15cm;
        margin:auto;
        background:white;
        padding:10px;
        color:#000;
    }

    .logo{
        text-align:center;
        margin-bottom:10px;
    }

    .logo img{
        width:60px;
        margin-bottom:5px;
    }

    .empresa{
        font-size:18px;
        font-weight:bold;
    }

    .slogan{
        font-size:10px;
        color:#555;
    }

    .linea{
        border-top:1px dashed #000;
        margin:10px 0;
    }

    .titulo{
        text-align:center;
        font-weight:bold;
        font-size:14px;
        margin-bottom:10px;
    }

    .fila{
        margin-bottom:8px;
    }

    .label{
        font-size:10px;
        color:#666;
    }

    .valor{
        font-size:13px;
        font-weight:bold;
    }

    .asiento{
        border:2px solid #000;
        text-align:center;
        padding:8px;
        font-size:24px;
        font-weight:bold;
        margin:10px 0;
    }

    .precio{
        text-align:center;
        font-size:20px;
        font-weight:bold;
        margin-top:10px;
    }

    .codigo{
        text-align:center;
        font-size:12px;
        margin-top:10px;
    }

    .footer{
        text-align:center;
        font-size:10px;
        margin-top:15px;
    }

    .btn{
        text-align:center;
        margin-top:20px;
    }

    button{
        padding:10px 20px;
        background:#0f172a;
        color:white;
        border:none;
        border-radius:5px;
        cursor:pointer;
    }

    @media print{
        body{
            background:white;
        }

        .btn{
            display:none;
        }

        .ticket{
            width:7cm;
            margin:0;
            padding:5px;
        }

        @page{
            size:70mm 150mm;
            margin:0;
        }
    }
</style>
</head>
<body>

<div class="ticket">

    <div class="logo">

        {{-- Cambia la ruta por tu logo --}}
        <img src="{{ asset('images/logo.png') }}">

        <div class="empresa">
            TRANS VALLE
        </div>

        <div class="slogan">
            Transporte seguro y confiable
        </div>
    </div>

    <div class="linea"></div>

    <div class="titulo">
        BOLETO DE VIAJE
    </div>

    <div class="fila">
        <div class="label">N° BOLETO</div>
        <div class="valor">
            BOL-{{ str_pad($boleto->id,6,'0',STR_PAD_LEFT) }}
        </div>
    </div>

    <div class="fila">
        <div class="label">PASAJERO</div>
        <div class="valor">
            {{ $boleto->nombre_pasajero }}
        </div>
    </div>

    <div class="fila">
        <div class="label">CI</div>
        <div class="valor">
            {{ $boleto->ci_pasajero }}
        </div>
    </div>

    <div class="fila">
        <div class="label">TELÉFONO</div>
        <div class="valor">
            {{ $boleto->telefono_pasajero ?? '---' }}
        </div>
    </div>

    <div class="linea"></div>

    <div class="fila">
        <div class="label">RUTA</div>
        <div class="valor">
            {{ $boleto->viaje->ruta->origen }}
            →
            {{ $boleto->viaje->ruta->destino }}
        </div>
    </div>

    <div class="fila">
        <div class="label">FECHA</div>
        <div class="valor">
            {{ \Carbon\Carbon::parse($boleto->viaje->fecha_viaje)->format('d/m/Y') }}
        </div>
    </div>

    <div class="fila">
        <div class="label">HORA</div>
        <div class="valor">
            {{ $boleto->viaje->hora_salida }}
        </div>
    </div>

    <div class="fila">
        <div class="label">BUS</div>
        <div class="valor">
            {{ $boleto->viaje->bus->placa }}
        </div>
    </div>

    <div class="asiento">
        ASIENTO<br>
        {{ $boleto->numero_asiento }}
    </div>

    <div class="precio">
        Bs {{ number_format($boleto->precio,2) }}
    </div>

    <div class="linea"></div>

    <div class="codigo">
        BOL-{{ str_pad($boleto->id,6,'0',STR_PAD_LEFT) }}
    </div>

    <div class="footer">
        Gracias por viajar con nosotros<br>
        Trans Valle
    </div>

    <div class="btn">
        <button onclick="window.print()">
            🖨 Imprimir
        </button>
    </div>

</div>

<script>
window.onload = function () {
    setTimeout(() => {
        window.print();
    }, 500);
};
</script>

</body>
</html>