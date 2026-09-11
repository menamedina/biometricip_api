<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos por módulo ─────────────────────────────────────
        $permissions = [
            // Sedes
            'sedes.ver', 'sedes.crear', 'sedes.editar', 'sedes.eliminar',

            // Empleados
            'empleados.ver', 'empleados.crear', 'empleados.editar', 'empleados.eliminar',
            'empleados.exportar', 'empleados.importar', 'empleados.historial',

            // Visitantes
            'visitantes.ver', 'visitantes.crear', 'visitantes.editar', 'visitantes.historial', 'visitantes.exportar',

            // Dispositivos biométricos
            'dispositivos.ver', 'dispositivos.crear', 'dispositivos.editar', 'dispositivos.eliminar', 'dispositivos.vaciar', 'dispositivos.usuarios',

            // Asistencia
            'asistencia.ver', 'asistencia.exportar', 'asistencia.foto',

            // Permisos/ausencias
            'permisos.ver', 'permisos.crear', 'permisos.aprobar', 'permisos.eliminar',

            // Empleadores
            'empleadores.ver', 'empleadores.crear', 'empleadores.editar', 'empleadores.eliminar',

            // Departamentos y cargos
            'departamentos.ver', 'departamentos.crear', 'departamentos.editar', 'departamentos.eliminar',

            // Horarios
            'horarios.ver', 'horarios.crear', 'horarios.editar', 'horarios.eliminar',

            // Festivos
            'festivos.ver', 'festivos.crear', 'festivos.editar', 'festivos.eliminar',

            // Reportes
            'reportes.ver', 'reportes.exportar',

            // Notificaciones
            'notificaciones.ver', 'notificaciones.enviar',

            // Empresa (Mi Empresa)
            'empresa.ver', 'empresa.crear', 'empresa.editar', 'empresa.eliminar', 'empresa.token',

            // Roles y permisos
            'roles.ver', 'roles.crear', 'roles.editar', 'roles.eliminar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ───────────────────────────────────────────────────

        // Super Administrador — acceso total (admin_tenant)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Administrador — admin de empresa, todo excepto gestión de roles super-admin
        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'sedes.ver', 'sedes.crear', 'sedes.editar', 'sedes.eliminar',
            'empleados.ver', 'empleados.crear', 'empleados.editar', 'empleados.eliminar',
            'empleados.exportar', 'empleados.importar', 'empleados.historial',
            'visitantes.ver', 'visitantes.crear', 'visitantes.editar', 'visitantes.historial', 'visitantes.exportar',
            'dispositivos.ver', 'dispositivos.crear', 'dispositivos.editar', 'dispositivos.eliminar', 'dispositivos.vaciar', 'dispositivos.usuarios',
            'asistencia.ver', 'asistencia.exportar', 'asistencia.foto',
            'permisos.ver', 'permisos.crear', 'permisos.aprobar', 'permisos.eliminar',
            'empleadores.ver', 'empleadores.crear', 'empleadores.editar', 'empleadores.eliminar',
            'departamentos.ver', 'departamentos.crear', 'departamentos.editar', 'departamentos.eliminar',
            'horarios.ver', 'horarios.crear', 'horarios.editar', 'horarios.eliminar',
            'festivos.ver', 'festivos.crear', 'festivos.editar', 'festivos.eliminar',
            'reportes.ver', 'reportes.exportar',
            'notificaciones.ver', 'notificaciones.enviar',
            'empresa.ver', 'empresa.crear', 'empresa.editar', 'empresa.eliminar', 'empresa.token',
            'roles.ver', 'roles.crear', 'roles.editar', 'roles.eliminar',
        ]);

        // Supervisor — gestión operativa sin administración de sistema
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $supervisor->syncPermissions([
            'sedes.ver',
            'empleados.ver', 'empleados.editar',
            'empleados.exportar', 'empleados.historial',
            'visitantes.ver', 'visitantes.crear', 'visitantes.editar', 'visitantes.historial', 'visitantes.exportar',
            'dispositivos.ver',
            'asistencia.ver', 'asistencia.exportar', 'asistencia.foto',
            'permisos.ver', 'permisos.crear', 'permisos.aprobar',
            'empleadores.ver',
            'departamentos.ver',
            'horarios.ver',
            'festivos.ver',
            'reportes.ver', 'reportes.exportar',
            'notificaciones.ver',
        ]);

        // Empleado — acceso mínimo (principalmente app móvil)
        $empleado = Role::firstOrCreate(['name' => 'Empleado', 'guard_name' => 'web']);
        $empleado->syncPermissions([
            'asistencia.ver',
            'permisos.ver', 'permisos.crear',
        ]);
    }
}
