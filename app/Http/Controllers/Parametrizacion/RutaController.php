<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('rutas.ver');
    $request->validate([
        'search' => 'sometimes|nullable|string|max:255',
        'page' => 'sometimes|integer|min:1',
    ]);

    $rutas = Ruta::where('estado_base', 1)
                  ->when($request->search, function ($query, $search) {
                      $query->where(function ($q) use ($search) {
                          $q->where('origen', 'like', "%{$search}%")
                            ->orWhere('destino', 'like', "%{$search}%");
                      });
                  })
                  ->paginate(10)
                  ->withQueryString();

    return view('parametrizacion.rutas.index', compact('rutas'));
    }

    public function create()
    {
        $this->authorize('rutas.crear');

        return view('parametrizacion.rutas.create');
    }

    public function store(Request $request)
    {
        $this->authorize('rutas.crear');

        $datos = $request->validate([
            'origen'       => 'required|string|max:255',
            'destino'      => 'required|string|max:255',
            'distancia_km' => 'nullable|numeric|between:0,999999.99|decimal:0,2',
            'precio_base'  => 'required|numeric|between:0,999999.99|decimal:0,2',
        ]);

        Ruta::create($datos);

        return redirect()->route('parametrizacion.rutas.index')
                         ->with('success', 'Ruta creada correctamente.');
    }

    public function edit(Ruta $ruta)
    {
        $this->authorize('rutas.editar');

        return view('parametrizacion.rutas.edit', compact('ruta'));
    }

    public function update(Request $request, Ruta $ruta)
    {
        $this->authorize('rutas.editar');

        $datos = $request->validate([
            'origen'       => 'required|string|max:255',
            'destino'      => 'required|string|max:255',
            'distancia_km' => 'nullable|numeric|between:0,999999.99|decimal:0,2',
            'precio_base'  => 'required|numeric|between:0,999999.99|decimal:0,2',
        ]);

        $ruta->update($datos);

        return redirect()->route('parametrizacion.rutas.index')
                         ->with('success', 'Ruta actualizada correctamente.');
    }

    public function destroy(Ruta $ruta)
    {
        $this->authorize('rutas.eliminar');

        $ruta->update(['estado_base' => 0]);

        return redirect()->route('parametrizacion.rutas.index')
                         ->with('success', 'Ruta eliminada correctamente.');
    }
}