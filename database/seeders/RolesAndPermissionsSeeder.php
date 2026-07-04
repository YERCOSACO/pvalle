<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── PERMISOS ──────────────────────────────────────────
        $permisos = [
            // Seguridad
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            'roles.ver', 'roles.crear', 'roles.editar', 'roles.eliminar',
            'usuariorol.ver', 'usuariorol.crear', 'usuariorol.editar', 'usuariorol.eliminar',

            // Parametrización
            'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
            'buses.ver', 'buses.crear', 'buses.editar', 'buses.eliminar',
            'rutas.ver', 'rutas.crear', 'rutas.editar', 'rutas.eliminar',
            'conductores.ver', 'conductores.crear', 'conductores.editar', 'conductores.eliminar',
            'tipoencomiendas.ver', 'tipoencomiendas.crear', 'tipoencomiendas.editar', 'tipoencomiendas.eliminar',
            'tipoincidencias.ver', 'tipoincidencias.crear', 'tipoincidencias.editar', 'tipoincidencias.eliminar',

            // Transaccional
            'reservas.ver', 'reservas.crear', 'reservas.editar', 'reservas.eliminar',
            'viajes.ver', 'viajes.crear', 'viajes.editar', 'viajes.eliminar',
            'asignacionconductor.ver', 'asignacionconductor.crear', 'asignacionconductor.editar', 'asignacionconductor.eliminar',
            'encomiendas.ver', 'encomiendas.crear', 'encomiendas.editar', 'encomiendas.eliminar',
            'incidenciaviaje.ver', 'incidenciaviaje.crear', 'incidenciaviaje.editar', 'incidenciaviaje.eliminar',
            'notificaciones.ver', 'notificaciones.crear', 'notificaciones.editar', 'notificaciones.eliminar',
            'asientoviaje.ver', 'asientoviaje.crear', 'asientoviaje.editar', 'asientoviaje.eliminar',
            'boletos.ver', 'boletos.crear', 'boletos.editar', 'boletos.eliminar',
            'reportes.ver', 'estadisticas.ver',
            'reservas.ver', 'reservas.crear', 'reservas.editar', 'reservas.eliminar',
            // Reportes
            'reportes.ver',

            // Estadísticas
            'estadisticas.ver',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ── ROLES ─────────────────────────────────────────────
        $admin         = Role::firstOrCreate(['name' => 'Administrador']);
        $recepcionista = Role::firstOrCreate(['name' => 'Recepcionista']);
        $vendedor      = Role::firstOrCreate(['name' => 'Vendedor']);

        // Administrador tiene TODO
        $admin->syncPermissions(Permission::all());

        // Recepcionista gestiona lo transaccional y parametrización básica
        $recepcionista->syncPermissions([
            'clientes.ver', 'clientes.crear', 'clientes.editar',
            'buses.ver', 'rutas.ver', 'conductores.ver',
            'tipoencomiendas.ver', 'tipoincidencias.ver',
            'reservas.ver', 'reservas.crear', 'reservas.editar',
            'viajes.ver', 'viajes.crear', 'viajes.editar',
            'encomiendas.ver', 'encomiendas.crear', 'encomiendas.editar',
            'incidenciaviaje.ver', 'incidenciaviaje.crear', 'incidenciaviaje.editar', 'incidenciaviaje.eliminar',
            'notificaciones.ver', 'notificaciones.crear', 'notificaciones.editar', 'notificaciones.eliminar',
            'asientoviaje.ver', 'asientoviaje.crear', 'asientoviaje.editar', 'asientoviaje.eliminar',
            'boletos.ver', 'boletos.crear', 'boletos.editar', 'boletos.eliminar',
            'reportes.ver', 'estadisticas.ver',
            'reservas.ver', 'reservas.crear', 'reservas.editar', 'reservas.eliminar',
            ]);

        // Vendedor solo ve reportes y estadísticas
        $vendedor->syncPermissions([
            'reportes.ver',
            'estadisticas.ver',
        ]);

        // ── USUARIO ADMINISTRADOR ─────────────────────────────
        $usuarioAdmin = User::firstOrCreate(
            ['email' => 'yerko71005452@gmail.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('yerco75076058'),
            ]
        );

        $usuarioAdmin->assignRole($admin);
    }
}