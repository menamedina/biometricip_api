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

    /** key => sede — se procesa en afterImport (user_id => sedeId para existentes, array para nuevos) */
    private array $sedePending = [];

    /** cedula => user_id — cargado al inicio desde la BD */
    private array $cedulaMap = [];

    /** cedulas ya asignadas en este lote (para evitar duplicados dentro del archivo) */
    private array $cedulasBatch = [];

    public function __construct(private int $empresaId)
    {
        $this->cedulaMap = User::where('empresa_id', $empresaId)
            ->whereNotNull('cedula')
            ->where('cedula', '!=', '')
            ->pluck('id', 'cedula')
            ->toArray();
    }

    public function model(array $row): ?User
    {
        $nombre = trim($row['nombre'] ?? $row['nombre_*'] ?? '');
        $email  = mb_strtolower(trim($row['email'] ?? $row['email_*'] ?? ''));
        $cedula = trim($row['cedula'] ?? $row['cedula_*'] ?? '');

        if (!$nombre || !$email) return null;

        if (!$cedula) {
            $this->skipped[] = "{$nombre} (cédula obligatoria)";
            return null;
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->skipped[] = "{$cedula} (email inválido: {$email})";
            return null;
        }

        // ── Cédula duplicada dentro del mismo archivo ─────────────────────────
        if (isset($this->cedulasBatch[$cedula])) {
            $this->skipped[] = "{$cedula} (duplicada en el archivo)";
            return null;
        }
        $this->cedulasBatch[$cedula] = true;

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

        // ── Buscar existente por cédula + empresa ─────────────────────────────
        $existing = isset($this->cedulaMap[$cedula])
            ? User::find($this->cedulaMap[$cedula])
            : null;

        if ($existing) {
            // Si el email cambia, verificar que no esté en uso por otro usuario
            if ($email && $email !== $existing->email) {
                $emailTaken = User::where('email', $email)
                    ->where('id', '!=', $existing->id)
                    ->exists();
                if ($emailTaken) {
                    $this->skipped[] = "{$cedula} (email {$email} ya está en uso por otro usuario)";
                    return null;
                }
            }

            $fields = [
                'name'            => $nombre,
                'cedula'          => $cedula,
                'telefono'        => trim($row['telefono'] ?? '') ?: $existing->telefono,
                'departamento_id' => $deptoId     ?? $existing->departamento_id,
                'cargo_id'        => $cargoId     ?? $existing->cargo_id,
                'horario_id'      => $horarioId   ?? $existing->horario_id,
                'empleador_id'    => $empleadorId ?? $existing->empleador_id,
                'lider_id'        => $liderId     ?? $existing->lider_id,
                'role'            => $rol,
                'is_active'       => $isActive,
            ];
            if ($email) $fields['email'] = $email;

            $existing->update($fields);
            $this->updated++;
            $this->updatedList[] = $cedula . ($email ? " ({$email})" : '');

            if ($sedeId) $this->sedePending[$existing->id] = $sedeId;

            return null;
        }

        // ── Crear nuevo ───────────────────────────────────────────────────────
        if (!$email) {
            $this->skipped[] = "{$cedula} (email obligatorio para nuevos empleados)";
            return null;
        }

        // Verificar email único
        if (User::where('email', $email)->exists()) {
            $this->skipped[] = "{$cedula} (email {$email} ya está en uso)";
            return null;
        }

        if ($sedeId) $this->sedePending['__new__' . $cedula] = ['sedeId' => $sedeId, 'cedula' => $cedula];

        $this->created++;
        $this->createdList[] = $cedula . " ({$email})";

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
                foreach ($this->sedePending as $key => $value) {
                    if (is_array($value)) {
                        // Nuevo empleado: buscar por cédula
                        $user = User::where('empresa_id', $this->empresaId)
                            ->where('cedula', $value['cedula'])
                            ->first();
                        $userId = $user?->id;
                        $sedeId = $value['sedeId'];
                    } else {
                        // Existente: key es user_id
                        $userId = $key;
                        $sedeId = $value;
                    }
                    if (!$userId) continue;
                    UserSede::updateOrCreate(
                        ['user_id' => $userId, 'empresa_id' => $this->empresaId],
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
