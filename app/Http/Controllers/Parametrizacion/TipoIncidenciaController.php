<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\TipoIncidencia;
use Illuminate\Http\Request;

class TipoIncidenciaController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('tipoincidencias.ver');
    $request->validate([
        'search' => 'sometimes|nullable|string|max:255',
        'page' => 'sometimes|integer|min:1',
    ]);

    $tipoIncidencias = TipoIncidencia::where('estado_base', 1)
                                      ->when($request->search, function ($query, $search) {
                                          $query->where('nombre', 'like', "%{$search}%");
                                      })
                                      ->paginate(10)
                                      ->withQueryString();

    return view('parametrizacion.tipoincidencias.index', compact('tipoIncidencias'));
    }
    public function create()
    {
        $this->authorize('tipoincidencias.crear');

        return view('parametrizacion.tipoincidencias.create');
    }

    public function store(Request $request)
    {
        $this->authorize('tipoincidencias.crear');

        $datos = $request->validate([
            'nombre'         => 'required|string|max:255',
            'descripcion'    => 'nullable|string|max:255',
            'nivel_gravedad' => 'required|in:Leve,Moderado,Grave',
        ]);

        TipoIncidencia::create($datos);

        return redirect()->route('parametrizacion.tipoincidencias.index')
                         ->with('success', 'Tipo de incidencia creado correctamente.');
    }

    public function edit(TipoIncidencia $tipoIncidencia)
    {
        $this->authorize('tipoincidencias.editar');

        return view('parametrizacion.tipoincidencias.edit', compact('tipoIncidencia'));
    }

    public function update(Request $request, TipoIncidencia $tipoIncidencia)
    {
        $this->authorize('tipoincidencias.editar');

        $datos = $request->validate([
            'nombre'         => 'required|string|max:255',
            'descripcion'    => 'nullable|string|max:255',
            'nivel_gravedad' => 'required|in:Leve,Moderado,Grave',
        ]);

        $tipoIncidencia->update($datos);

        return redirect()->route('parametrizacion.tipoincidencias.index')
                         ->with('success', 'Tipo de incidencia actualizado correctamente.');
    }

    public function destroy(TipoIncidencia $tipoIncidencia)
    {
        $this->authorize('tipoincidencias.eliminar');

        $tipoIncidencia->update(['estado_base' => 0]);

        return redirect()->route('parametrizacion.tipoincidencias.index')
                         ->with('success', 'Tipo de incidencia eliminado correctamente.');
    }
}