<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                UsuarioRol
            </h2>
            <a href="{{ route('seguridad.usuariorol.create') }}"
               class="btn-primary">
                Agregar UsuarioRol
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
<form method="GET" action="{{ route('seguridad.usuariorol.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar usuario..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="btn-primary">
        Buscar UsuarioRol
    </button>
    @if(request('search'))
        <a href="{{ route('seguridad.usuariorol.index') }}"
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
                        <th class="px-6 py-3 text-left">Usuario</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Rol asignado</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $usuario->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $usuario->email }}</td>
                        <td class="px-6 py-3">
                            @forelse($usuario->roles as $r)
                                <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">
                                    {{ $r->name }}
                                </span>
                            @empty
                                <span class="text-gray-400 text-xs">Sin rol</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('seguridad.usuariorol.edit', $usuario) }}"
                               class="btn-warning btn-sm">
                                Modificar UsuarioRol
                            </a>
                            <form action="{{ route('seguridad.usuariorol.destroy', $usuario) }}"
                                  method="POST" onsubmit="return confirm('¿Quitar rol de este usuario?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                Eliminar UsuarioRol
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            Sin asignaciones registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
