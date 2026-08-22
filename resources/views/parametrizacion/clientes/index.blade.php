<x-app-layout>
    <x-slot name="header">
        <div class="section-toolbar">
            <div>
                <h2 class="page-title">Clientes</h2>
                <p class="page-subtitle">Administra la información de contacto y registro de cada cliente.</p>
            </div>
            <a href="{{ route('parametrizacion.clientes.create') }}" class="btn-primary">
                Agregar cliente
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('parametrizacion.clientes.index') }}" class="search-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, cédula o email...">
            <button type="submit" class="btn-primary">Buscar cliente</button>
            @if(request('search'))
                <a href="{{ route('parametrizacion.clientes.index') }}" class="btn-secondary">
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
                        <th class="px-6 py-3 text-left">Cédula</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Teléfono</th>
                        <th class="px-6 py-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($clientes as $cliente)
                    <tr>
                        <td class="px-6 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $cliente->nombre_completo }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $cliente->cedula }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $cliente->email }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $cliente->telefono ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <div class="table-actions">
                            <a href="{{ route('parametrizacion.clientes.edit', $cliente) }}"
                               class="btn-warning btn-sm">
                                Modificar Cliente
                            </a>
                            <form action="{{ route('parametrizacion.clientes.destroy', $cliente) }}"
                                  method="POST" onsubmit="return confirm('¿Eliminar este cliente?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger btn-sm">
                                    Eliminar Cliente
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            Sin clientes registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
            @if($clientes->hasPages())
                <div class="px-6 py-4 border-t">{{ $clientes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
