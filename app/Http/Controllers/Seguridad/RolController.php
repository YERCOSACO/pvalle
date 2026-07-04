<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index(Request $request)
{
    $this->authorize('roles.ver');

    $roles = Role::with('permissions')
                  ->where('estado_base', 1)
                  ->when($request->search, function ($query, $search) {
                      $query->where('name', 'like', "%{$search}%");
                  })
                  ->paginate(10)
                  ->withQueryString();

    return view('seguridad.roles.index', compact('roles'));
}

    public function create()
    {
        $this->authorize('roles.crear');

        $permisos = Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]);
        return view('seguridad.roles.create', compact('permisos'));
    }

    public function store(Request $request)
    {
        $this->authorize('roles.crear');

        $request->validate([
            'name'     => 'required|unique:roles,name',
            'permisos' => 'array',
        ]);

        $rol = Role::create(['name' => $request->name]);
        $rol->syncPermissions($request->permisos ?? []);

        return redirect()->route('seguridad.roles.index')
                         ->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $rol)
    {
        $this->authorize('roles.editar');

        $permisos = Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]);
        $permisosActuales = $rol->permissions->pluck('name')->toArray();

        return view('seguridad.roles.edit', compact('rol', 'permisos', 'permisosActuales'));
    }

    public function update(Request $request, Role $rol)
    {
        $this->authorize('roles.editar');

        $request->validate([
            'name'     => 'required|unique:roles,name,' . $rol->id,
            'permisos' => 'array',
        ]);

        $rol->update(['name' => $request->name]);
        $rol->syncPermissions($request->permisos ?? []);

        return redirect()->route('seguridad.roles.index')
                         ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $rol)
    {
        $this->authorize('roles.eliminar');

        $rol->update(['estado_base' => 0]);

        return redirect()->route('seguridad.roles.index')
                         ->with('success', 'Rol eliminado correctamente.');
    }
}