<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioRolController extends Controller
{
    public function index(Request $request)
    {
    $this->authorize('usuarios.ver');
    $request->validate([
        'search' => 'sometimes|nullable|string|max:255',
        'page' => 'sometimes|integer|min:1',
    ]);

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

        $datos = $request->validate([
            'usuario_id' => ['required', 'integer', Rule::exists('users', 'id')->where('estado_base', 1)],
            'rol'        => ['required', 'string', Rule::exists('roles', 'name')->where('estado_base', 1)->where('guard_name', 'web')],
        ]);

        $usuario = User::where('id', $datos['usuario_id'])
                        ->where('estado_base', 1)
                        ->firstOrFail();

        $rol = Role::where('name', $datos['rol'])
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

        $datos = $request->validate([
            'rol' => ['required', 'string', Rule::exists('roles', 'name')->where('estado_base', 1)->where('guard_name', 'web')],
        ]);

        $rol = Role::where('name', $datos['rol'])
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