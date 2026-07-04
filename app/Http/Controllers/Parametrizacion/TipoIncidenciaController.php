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

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'descripcion'    => 'nullable|string',
            'nivel_gravedad' => 'required|string|max:50',
        ]);

        TipoIncidencia::create($request->only('nombre', 'descripcion', 'nivel_gravedad'));

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

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'descripcion'    => 'nullable|string',
            'nivel_gravedad' => 'required|string|max:50',
        ]);

        $tipoIncidencia->update($request->only('nombre', 'descripcion', 'nivel_gravedad'));

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