<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">
                Usuarios
            </h2>

            <a href="{{ route('seguridad.usuarios.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sp">
                Agregar Usuario
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
<form method="GET" action="{{ route('seguridad.usuarios.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Buscar por nombre o email..."
           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
        Buscar
    </button>
    @if(request('search'))
        <a href="{{ route('seguridad.usuarios.index') }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">
            Limpiar
        </a>
    @endif
</form>
        <div class="bg-white shadow rounded-lg overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
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

                        <tr class="hover:bg-gray-50">

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
                                <div class="flex flex-col md:flex-row gap-2">

                                    <a href="{{ route('seguridad.usuarios.edit', $usuario) }}"
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs text-center">
                                        Modificar Usuario
                                    </a>

                                    <form action="{{ route('seguridad.usuarios.destroy', $usuario) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar este usuario?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="w-full bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sp">
                                            Eliminar Usuario
                                        </button>

                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
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