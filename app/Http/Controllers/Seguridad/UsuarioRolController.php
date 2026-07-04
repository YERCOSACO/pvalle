<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('usuarios.ver');

    $usuarios = User::with('roles')
                     ->where('estado_base', 1)
                     ->when($request->search, function ($query, $search) {
                         $query->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                     })
                     ->paginate(10)
                     ->withQueryString();

    return view('seguridad.usuariorol.index', compact('usuarios'));
    }

    public function create()
    {
        $this->authorize('usuarios.editar');

        $usuarios = User::where('estado_base', 1)->get();
        $roles = Role::where('estado_base', 1)->get();

        return view('seguridad.usuariorol.create', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('usuarios.editar');

        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'rol'        => 'required|exists:roles,name',
        ]);

        $usuario = User::where('id', $request->usuario_id)
                        ->where('estado_base', 1)
                        ->firstOrFail();

        $rol = Role::where('name', $request->rol)
                    ->where('estado_base', 1)
                    ->firstOrFail();

        $usuario->syncRoles([$rol->name]);

        return redirect()->route('seguridad.usuariorol.index')
                         ->with('success', 'Rol asignado correctamente.');
    }

    public function edit(User $usuario)
    {
        $this->authorize('usuarios.editar');

        $roles = Role::where('estado_base', 1)->get();
        $rolActual = $usuario->roles->first()?->name;

        return view('seguridad.usuariorol.edit', compact('usuario', 'roles', 'rolActual'));
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorize('usuarios.editar');

        $request->validate([
            'rol' => 'required|exists:roles,name',
        ]);

        $rol = Role::where('name', $request->rol)
                    ->where('estado_base', 1)
                    ->firstOrFail();

        $usuario->syncRoles([$rol->name]);

        return redirect()->route('seguridad.usuariorol.index')
                         ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        $this->authorize('usuarios.editar');

        $usuario->syncRoles([]);

        return redirect()->route('seguridad.usuariorol.index')
                         ->with('success', 'Rol removido del usuario.');
    }
}