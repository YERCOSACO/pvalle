<?php

namespace App\Http\Controllers\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('clientes.ver');
    $request->validate([
        'search' => 'sometimes|nullable|string|max:255',
        'page' => 'sometimes|integer|min:1',
    ]);

    $clientes = Cliente::where('estado_base', 1)
                        ->when($request->search, function ($query, $search) {
                            $query->where(function ($q) use ($search) {
                                $q->where('nombre', 'like', "%{$search}%")
                                  ->orWhere('apellido', 'like', "%{$search}%")
                                  ->orWhere('cedula', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                            });
                        })
                        ->paginate(10)
                        ->withQueryString();

    return view('parametrizacion.clientes.index', compact('clientes'));
    }

    public function create()
    {
        $this->authorize('clientes.crear');

        return view('parametrizacion.clientes.create');
    }

    public function store(Request $request)
    {
        $this->authorize('clientes.crear');

        $datos = $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'cedula'           => 'required|string|max:255|unique:clientes,cedula',
            'email'            => 'required|string|email|max:255|unique:clientes,email',
            'contrasena'       => 'required|string|min:12|confirmed',
            'telefono'         => 'nullable|string|max:20',
            //6
            'direccion'        => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
        ]);

        Cliente::create([
            ...$datos,
            'contrasena' => Hash::make($datos['contrasena']),
        ]);

        return redirect()->route('parametrizacion.clientes.index')
                         ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        $this->authorize('clientes.editar');

        return view('parametrizacion.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('clientes.editar');

        $datos = $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'cedula'           => 'required|string|max:255|unique:clientes,cedula,' . $cliente->id,
            'email'            => 'required|string|email|max:255|unique:clientes,email,' . $cliente->id,
            'telefono'         => 'nullable|string|max:20',
            'direccion'        => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
            'contrasena'       => 'sometimes|nullable|string|min:12|confirmed',
        ]);

        $datosCliente = $datos;
        unset($datosCliente['contrasena']);
        $cliente->update($datosCliente);

        if (! empty($datos['contrasena'])) {
            $cliente->update(['contrasena' => Hash::make($datos['contrasena'])]);
        }

        return redirect()->route('parametrizacion.clientes.index')
                         ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $this->authorize('clientes.eliminar');

        $cliente->update(['estado_base' => 0]);

        return redirect()->route('parametrizacion.clientes.index')
                         ->with('success', 'Cliente eliminado correctamente.');
    }
}