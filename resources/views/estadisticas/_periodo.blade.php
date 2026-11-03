@php
    $nombresMeses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
@endphp

<form method="GET" action="{{ route($action) }}" class="flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div>
        <label for="mes" class="block text-sm font-semibold text-slate-700">Mes</label>
        <select id="mes" name="mes" class="mt-1 rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach ($nombresMeses as $numero => $nombre)
                <option value="{{ $numero }}" @selected($mes === $numero)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="anio" class="block text-sm font-semibold text-slate-700">Año</label>
        <input id="anio" name="anio" type="number" min="2020" max="2100" value="{{ $anio }}"
               class="mt-1 w-28 rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
    </div>
    <button type="submit" class="rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">
        Aplicar periodo
    </button>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('estadisticas.ingresos', ['mes' => $mes, 'anio' => $anio]) }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-teal-800 hover:bg-teal-50">Ingresos</a>
        <a href="{{ route('estadisticas.viajes-ruta', ['mes' => $mes, 'anio' => $anio]) }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-teal-800 hover:bg-teal-50">Viajes por rutas</a>
        <a href="{{ route('estadisticas.encomiendas', ['mes' => $mes, 'anio' => $anio]) }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-teal-800 hover:bg-teal-50">Encomiendas</a>
    </div>
</form>
