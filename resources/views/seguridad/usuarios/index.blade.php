<x-app-layout>
    <x-slot name="header">
        <div class="section-toolbar">
            <div>
                <h2 class="page-title">Usuarios</h2>
                <p class="page-subtitle">Gestiona el acceso administrativo del sistema.</p>
            </div>
            <a href="{{ route('seguridad.usuarios.create') }}" class="btn-primary">
                Agregar usuario
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('seguridad.usuarios.index') }}" class="search-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email...">
            <button type="submit" class="btn-primary">Buscar</button>
            @if(request('search'))
                <a href="{{ route('seguridad.usuarios.index') }}" class="btn-secondary">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="table-card overflow-hidden">

            <div class="overflow-x-auto">
                <table class="table-shell text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Nombre</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                    @forelse($usuarios as $usuario)

                        <tr>

                            <td class="px-6 py-4 text-gray-400">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 font-medium">
                                {{ $usuario->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $usuario->email }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="table-actions">

                                    <a href="{{ route('seguridad.usuarios.edit', $usuario) }}"
                                       class="btn-warning btn-sm text-center">
                                        Modificar
                                    </a>

                                    <form action="{{ route('seguridad.usuarios.destroy', $usuario) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar este usuario?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn-danger btn-sm w-full">
                                            Eliminar
                                        </button>

                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="empty-state">
                                Sin usuarios registrados.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>
                </table>
            </div>

            @if($usuarios->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $usuarios->links() }}
                </div>
            @endif

        </div>

    </div>
</x-app-layout>
