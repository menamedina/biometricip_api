<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmpleadosImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int   $created  = 0;
    public int   $updated  = 0;
    public array $skipped  = [];
    public array $createdList = [];
    public array $updatedList = [];

    public function __construct(private int $empresaId) {}

    public function model(array $row): ?User
    {
        $nombre = trim($row['nombre'] ?? $row['nombre_*'] ?? '');
        $email  = mb_strtolower(trim($row['email'] ?? $row['email_*'] ?? ''));
        $cedula = trim($row['cedula'] ?? $row['cedula_*'] ?? '');

        if (!$nombre || !$email) return null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->skipped[] = "{$email} (email inválido)";
            return null;
        }

        // Los campos de catálogo vienen como "id - nombre" o simplemente un entero
        $deptoId     = $this->parseId($row['departamento'] ?? '');
        $cargoId     = $this->parseId($row['cargo']        ?? '');
        $horarioId   = $this->parseId($row['horario']      ?? '');
        $empleadorId = $this->parseId($row['empleador']    ?? '');
        $liderId     = $this->parseId($row['lider']        ?? '');

        $rol = in_array($row['rol'] ?? '', ['empleado', 'supervisor', 'admin'])
                ? $row['rol']
                : 'empleado';

        $isActive = isset($row['activo']) && $row['activo'] !== ''
                    ? (bool)(int)$row['activo']
                    : true;

        // ── Actualizar si el email ya existe ──────────────────────────────────
        $existing = User::where('email', $email)->first();

        if ($existing) {
            $fields = [
                'name'            => $nombre,
                'cedula'          => $cedula ?: $existing->cedula,
                'telefono'        => trim($row['telefono'] ?? '') ?: $existing->telefono,
                'departamento_id' => $deptoId     ?? $existing->departamento_id,
                'cargo_id'        => $cargoId     ?? $existing->cargo_id,
                'horario_id'      => $horarioId   ?? $existing->horario_id,
                'empleador_id'    => $empleadorId ?? $existing->empleador_id,
                'lider_id'        => $liderId     ?? $existing->lider_id,
                'role'            => $rol,
                'is_active'       => $isActive,
            ];
            $pass = trim($row['password'] ?? '');
            if ($pass) $fields['password'] = Hash::make($pass);

            $existing->update($fields);
            $this->updated++;
            $this->updatedList[] = $email;
            return null;
        }

        // ── Crear nuevo ───────────────────────────────────────────────────────
        $pass = trim($row['password'] ?? '') ?: 'Cambiar123';

        $this->created++;
        $this->createdList[] = $email;
        return new User([
            'name'            => $nombre,
            'email'           => $email,
            'password'        => Hash::make($pass),
            'cedula'          => $cedula,
            'telefono'        => trim($row['telefono'] ?? '') ?: null,
            'role'            => $rol,
            'tipo'            => 'usuario',
            'is_active'       => $isActive,
            'empresa_id'      => $this->empresaId,
            'codigo_empleado' => $this->generarCodigo(),
            'departamento_id' => $deptoId,
            'cargo_id'        => $cargoId,
            'horario_id'      => $horarioId,
            'empleador_id'    => $empleadorId,
            'lider_id'        => $liderId,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Extrae el ID numérico de un valor como "5 - Nombre" o simplemente "5".
     * Retorna null si el valor está vacío o no tiene un entero válido al inicio.
     */
    private function parseId(mixed $valor): ?int
    {
        $valor = trim((string) $valor);
        if ($valor === '') return null;

        // Tomar la parte antes del primer " - " o todo el string
        $parte = explode(' - ', $valor, 2)[0];
        $id    = (int) $parte;

        return $id > 0 ? $id : null;
    }

    private function generarCodigo(): string
    {
        $ultimo = User::where('empresa_id', $this->empresaId)
            ->where('codigo_empleado', 'regexp', '^EMP-[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(codigo_empleado, 5) AS UNSIGNED) DESC')
            ->value('codigo_empleado');

        $siguiente = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        while (
            User::where('empresa_id', $this->empresaId)
                ->where('codigo_empleado', 'EMP-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT))
                ->exists()
        ) {
            $siguiente++;
        }

        return 'EMP-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}
