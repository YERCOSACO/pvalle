<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('usuarios.ver');

    $usuarios = User::with('roles')
                     ->where('estado_base', 1)
                     ->when($request->search, function ($query, $search) {
                         $query->where(function ($q) use ($search) {
                             $q->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                         });
                     })
                     ->paginate(10)
                     ->withQueryString();

    return view('seguridad.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $this->authorize('usuarios.crear');

        return view('seguridad.usuarios.create');
    }

    public function store(Request $request)
    {
        $this->authorize('usuarios.crear');

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            //6
            'password' => 'required|min:12|confirmed',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('seguridad.usuarios.index')
                         ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $this->authorize('usuarios.editar');

        return view('seguridad.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorize('usuarios.editar');

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
        ]);

        $usuario->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:12|confirmed']);
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('seguridad.usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        $this->authorize('usuarios.eliminar');

        $usuario->update(['estado_base' => 0]);

        return redirect()->route('seguridad.usuarios.index')
                         ->with('success', 'Usuario eliminado correctamente.');
    }
}