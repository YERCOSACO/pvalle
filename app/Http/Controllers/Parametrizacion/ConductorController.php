<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use Illuminate\Http\Request;

class ConductorController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('conductores.ver');

    $conductores = Conductor::where('estado_base', 1)
                             ->when($request->search, function ($query, $search) {
                                 $query->where(function ($q) use ($search) {
                                     $q->where('nombre', 'like', "%{$search}%")
                                       ->orWhere('apellido', 'like', "%{$search}%")
                                       ->orWhere('licencia', 'like', "%{$search}%");
                                 });
                             })
                             ->paginate(10)
                             ->withQueryString();

    return view('parametrizacion.conductores.index', compact('conductores'));
    }

    public function create()
    {
        $this->authorize('conductores.crear');

        return view('parametrizacion.conductores.create');
    }

    public function store(Request $request)
    {
        $this->authorize('conductores.crear');

        $request->validate([
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'licencia' => 'required|string|unique:conductores,licencia',
            'telefono' => 'nullable|string|max:20',
        ]);

        Conductor::create($request->only('nombre', 'apellido', 'licencia', 'telefono'));

        return redirect()->route('parametrizacion.conductores.index')
                         ->with('success', 'Conductor creado correctamente.');
    }

    public function edit(Conductor $conductor)
    {
        $this->authorize('conductores.editar');

        return view('parametrizacion.conductores.edit', compact('conductor'));
    }

    public function update(Request $request, Conductor $conductor)
    {
        $this->authorize('conductores.editar');

        $request->validate([
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'licencia' => 'required|string|unique:conductores,licencia,' . $conductor->id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $conductor->update($request->only('nombre', 'apellido', 'licencia', 'telefono'));

        return redirect()->route('parametrizacion.conductores.index')
                         ->with('success', 'Conductor actualizado correctamente.');
    }

    public function destroy(Conductor $conductor)
    {
        $this->authorize('conductores.eliminar');

        $conductor->update(['estado_base' => 0]);

        return redirect()->route('parametrizacion.conductores.index')
                         ->with('success', 'Conductor eliminado correctamente.');
    }
}