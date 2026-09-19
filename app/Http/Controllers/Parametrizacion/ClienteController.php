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

        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'cedula'           => 'required|string|unique:clientes,cedula',
            'email'            => 'required|email|unique:clientes,email',
            'contrasena'       => 'required|min:12|confirmed',
            'telefono'         => 'nullable|string|max:20',
            //6
            'direccion'        => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        Cliente::create([
            'nombre'           => $request->nombre,
            'apellido'         => $request->apellido,
            'cedula'           => $request->cedula,
            'email'            => $request->email,
            'contrasena'       => Hash::make($request->contrasena),
            'telefono'         => $request->telefono,
            'direccion'        => $request->direccion,
            'fecha_nacimiento' => $request->fecha_nacimiento,
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

        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'cedula'           => 'required|string|unique:clientes,cedula,' . $cliente->id,
            'email'            => 'required|email|unique:clientes,email,' . $cliente->id,
            'telefono'         => 'nullable|string|max:20',
            'direccion'        => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $cliente->update($request->only([
            'nombre', 'apellido', 'cedula', 'email',
            'telefono', 'direccion', 'fecha_nacimiento',
        ]));

        if ($request->filled('contrasena')) {
            $request->validate(['contrasena' => 'min:6|confirmed']);
            //1///////////////////////////////////////
            $cliente->update(['contrasena' => Hash::make($request->contrasena)]);
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