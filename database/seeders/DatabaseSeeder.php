<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Empleado;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@asistenciaqr.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $sede = Sede::create([
            'codigo' => 'SEDE-001',
            'nombre' => 'Sede Principal',
            'direccion' => 'Av. Reforma 222, CDMX',
            'lat' => 19.4326,
            'lng' => -99.1332,
            'radio_mts' => 150,
            'secret_key' => 'asistenciaqr-2025-sede01',
            'is_active' => true,
        ]);

        $departamentos = ['Ventas', 'TI', 'RRHH', 'Finanzas', 'Marketing', 'Operaciones', 'Legal', 'Ingeniería'];
        $empleadosData = [
            ['name' => 'Ana García', 'email' => 'ana.garcia@empresa.com', 'codigo' => 'EMP-0101', 'depto' => 'Ventas'],
            ['name' => 'Carlos López', 'email' => 'carlos.lopez@empresa.com', 'codigo' => 'EMP-0102', 'depto' => 'TI'],
            ['name' => 'Diana Ruiz', 'email' => 'diana.ruiz@empresa.com', 'codigo' => 'EMP-0103', 'depto' => 'RRHH'],
            ['name' => 'Eduardo Mtz', 'email' => 'eduardo.mtz@empresa.com', 'codigo' => 'EMP-0104', 'depto' => 'Finanzas'],
            ['name' => 'Fernanda Pérez', 'email' => 'fernanda.perez@empresa.com', 'codigo' => 'EMP-0105', 'depto' => 'Marketing'],
            ['name' => 'Gabriel Soto', 'email' => 'gabriel.soto@empresa.com', 'codigo' => 'EMP-0106', 'depto' => 'Operaciones'],
            ['name' => 'Helena Vargas', 'email' => 'helena.vargas@empresa.com', 'codigo' => 'EMP-0107', 'depto' => 'Legal'],
            ['name' => 'Iván Torres', 'email' => 'ivan.torres@empresa.com', 'codigo' => 'EMP-0108', 'depto' => 'TI'],
            ['name' => 'María Castillo', 'email' => 'maria.castillo@empresa.com', 'codigo' => 'EMP-0142', 'depto' => 'Ingeniería'],
        ];

        foreach ($empleadosData as $empData) {
            $user = User::create([
                'name' => $empData['name'],
                'email' => $empData['email'],
                'password' => Hash::make('password123'),
                'role' => 'empleado',
                'is_active' => true,
            ]);

            Empleado::create([
                'user_id' => $user->id,
                'codigo_empleado' => $empData['codigo'],
                'departamento' => $empData['depto'],
                'cargo' => 'Analista',
                'is_active' => true,
            ]);
        }
    }
}
