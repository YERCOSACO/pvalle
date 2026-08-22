<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Roles</h2>
            <a href="{{ route('seguridad.roles.create') }}"
               class="btn-primary">
                Agregar Rol
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
<form method="GET" action="{{ route('seguridad.roles.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar rol..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="btn-primary">
        Buscar Rol
    </button>
    @if(request('search'))
        <a href="{{ route('seguridad.roles.index') }}"
           class="btn-secondary">
            Limpiar
        </a>
    @endif
</form>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Rol</th>
                        <th class="px-6 py-3 text-left">Permisos</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $rol)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $rol->name }}</td>
                        <td class="px-6 py-3">
                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                                {{ $rol->permissions->count() }} permisos
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('seguridad.roles.edit', $rol) }}"
                               class="btn-warning btn-sm">
                                Modificar Rol
                            </a>
                            <form action="{{ route('seguridad.roles.destroy', $rol) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este rol?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                Eliminar Rol
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            Sin roles registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>