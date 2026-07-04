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

        $request->validate([
            'origen'       => 'required|string|max:255',
            'destino'      => 'required|string|max:255',
            'distancia_km' => 'nullable|numeric|min:0',
            'precio_base'  => 'required|numeric|min:0',
        ]);

        Ruta::create($request->only('origen', 'destino', 'distancia_km', 'precio_base'));

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

        $request->validate([
            'origen'       => 'required|string|max:255',
            'destino'      => 'required|string|max:255',
            'distancia_km' => 'nullable|numeric|min:0',
            'precio_base'  => 'required|numeric|min:0',
        ]);

        $ruta->update($request->only('origen', 'destino', 'distancia_km', 'precio_base'));

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