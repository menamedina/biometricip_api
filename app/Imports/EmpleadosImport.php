<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserSede;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class EmpleadosImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithEvents
{
    public int   $created  = 0;
    public int   $updated  = 0;
    public array $skipped  = [];
    public array $createdList = [];
    public array $updatedList = [];

    /** email => sede_id  — se procesa en afterImport */
    private array $sedePending = [];

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

        $deptoId     = $this->parseId($row['departamento'] ?? '');
        $cargoId     = $this->parseId($row['cargo']        ?? '');
        $horarioId   = $this->parseId($row['horario']      ?? '');
        $empleadorId = $this->parseId($row['empleador']    ?? '');
        $liderId     = $this->parseId($row['lider']        ?? '');
        $sedeId      = $this->parseId($row['sede']         ?? '');

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

            if ($sedeId) $this->sedePending[$email] = $sedeId;

            return null;
        }

        // ── Crear nuevo ───────────────────────────────────────────────────────
        $pass = trim($row['password'] ?? '') ?: 'Cambiar123';

        if ($sedeId) $this->sedePending[$email] = $sedeId;

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

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                foreach ($this->sedePending as $email => $sedeId) {
                    $user = User::where('email', $email)->first();
                    if (!$user) continue;
                    UserSede::updateOrCreate(
                        ['user_id' => $user->id, 'empresa_id' => $this->empresaId],
                        ['sede_id' => $sedeId]
                    );
                }
            },
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseId(mixed $valor): ?int
    {
        $valor = trim((string) $valor);
        if ($valor === '') return null;
        $id = (int) explode(' - ', $valor, 2)[0];
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
