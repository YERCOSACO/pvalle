<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('buses.ver');

    $buses = Bus::where('estado_base', 1)
                 ->when($request->search, function ($query, $search) {
                     $query->where(function ($q) use ($search) {
                         $q->where('placa', 'like', "%{$search}%")
                           ->orWhere('modelo', 'like', "%{$search}%");
                     });
                 })
                 ->paginate(10)
                 ->withQueryString();

    return view('parametrizacion.buses.index', compact('buses'));
    }

    public function create()
    {
        $this->authorize('buses.crear');

        return view('parametrizacion.buses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('buses.crear');

        $request->validate([
            'placa'      => 'required|string|unique:buses,placa',
            'capacidad'  => 'required|integer|min:1',
            'modelo'     => 'nullable|string|max:255',
            'tipo_bus'   => 'nullable|string|max:255',
        ]);

        Bus::create($request->only('placa', 'capacidad', 'modelo', 'tipo_bus'));

        return redirect()->route('parametrizacion.buses.index')
                         ->with('success', 'Bus creado correctamente.');
    }

    public function edit(Bus $bus)
    {
        $this->authorize('buses.editar');

        return view('parametrizacion.buses.edit', compact('bus'));
    }

    public function update(Request $request, Bus $bus)
    {
        $this->authorize('buses.editar');

        $request->validate([
            'placa'      => 'required|string|unique:buses,placa,' . $bus->id,
            'capacidad'  => 'required|integer|min:1',
            'modelo'     => 'nullable|string|max:255',
            'tipo_bus'   => 'nullable|string|max:255',
        ]);

        $bus->update($request->only('placa', 'capacidad', 'modelo', 'tipo_bus'));

        return redirect()->route('parametrizacion.buses.index')
                         ->with('success', 'Bus actualizado correctamente.');
    }

    public function destroy(Bus $bus)
    {
        $this->authorize('buses.eliminar');

        $bus->update(['estado_base' => 0]);

        return redirect()->route('parametrizacion.buses.index')
                         ->with('success', 'Bus eliminado correctamente.');
    }
}