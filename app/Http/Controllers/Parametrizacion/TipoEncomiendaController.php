<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\TipoEncomienda;
use Illuminate\Http\Request;

class TipoEncomiendaController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('tipoencomiendas.ver');

    $tipoEncomiendas = TipoEncomienda::where('estado_base', 1)
                                      ->when($request->search, function ($query, $search) {
                                          $query->where('nombre', 'like', "%{$search}%");
                                      })
                                      ->paginate(10)
                                      ->withQueryString();

    return view('parametrizacion.tipoencomiendas.index', compact('tipoEncomiendas'));
    }

    public function create()
    {
        $this->authorize('tipoencomiendas.crear');

        return view('parametrizacion.tipoencomiendas.create');
    }

    public function store(Request $request)
    {
        $this->authorize('tipoencomiendas.crear');

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
        ]);

        TipoEncomienda::create($request->only('nombre', 'descripcion', 'precio'));

        return redirect()->route('parametrizacion.tipoencomiendas.index')
                         ->with('success', 'Tipo de encomienda creado correctamente.');
    }

    public function edit(TipoEncomienda $tipoEncomienda)
    {
        $this->authorize('tipoencomiendas.editar');

        return view('parametrizacion.tipoencomiendas.edit', compact('tipoEncomienda'));
    }

    public function update(Request $request, TipoEncomienda $tipoEncomienda)
    {
        $this->authorize('tipoencomiendas.editar');

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
        ]);

        $tipoEncomienda->update($request->only('nombre', 'descripcion', 'precio'));

        return redirect()->route('parametrizacion.tipoencomiendas.index')
                         ->with('success', 'Tipo de encomienda actualizado correctamente.');
    }

    public function destroy(TipoEncomienda $tipoEncomienda)
    {
        $this->authorize('tipoencomiendas.eliminar');

        $tipoEncomienda->update(['estado_base' => 0]);

        return redirect()->route('parametrizacion.tipoencomiendas.index')
                         ->with('success', 'Tipo de encomienda eliminado correctamente.');
    }
}