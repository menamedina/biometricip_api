<?php

namespace App\Jobs;

use App\Helpers\TenantHelper;
use App\Models\Empresa;
use App\Models\ProgramacionTurno;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AplicarTurnoEmpresaJob
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int     $empresaId,
        public readonly ?string $fecha,    // Y-m-d del día de ejecución; null = toda la pendiente
    ) {}

    public function handle(): int
    {
        $empresa = Empresa::find($this->empresaId);

        if (!$empresa) {
            Log::warning('AplicarTurnoEmpresaJob: empresa no encontrada', ['empresa_id' => $this->empresaId]);
            return 0;
        }

        try {
            TenantHelper::switchTenant($this->empresaId);
        } catch (\Throwable $e) {
            Log::warning('AplicarTurnoEmpresaJob: tenant no encontrado', [
                'empresa_id' => $this->empresaId,
                'error'      => $e->getMessage(),
            ]);
            return 0;
        }

        try {
            $query = ProgramacionTurno::query();
            if ($this->fecha) {
                $query->where('semana_inicio', $this->fecha);
            }
            $programaciones = $query->get();

            if ($programaciones->isEmpty()) {
                Log::info('AplicarTurnoEmpresaJob: sin programaciones', [
                    'empresa_id' => $this->empresaId,
                    'fecha'      => $this->fecha,
                ]);
                return 0;
            }

            $aplicados = 0;

            foreach ($programaciones as $prog) {
                // User usa conexión 'mysql' (central) — no requiere switch
                $actualizado = User::where('id', $prog->user_id)
                    ->where('empresa_id', $this->empresaId)
                    ->update(['horario_id' => $prog->horario_id]);

                if ($actualizado) {
                    $aplicados++;
                }
            }

            // Limpiar programaciones aplicadas
            $deleteQuery = ProgramacionTurno::query();
            if ($this->fecha) {
                $deleteQuery->where('semana_inicio', $this->fecha);
            }
            $deleteQuery->delete();

            Log::info('AplicarTurnoEmpresaJob: turnos aplicados', [
                'empresa_id' => $this->empresaId,
                'fecha'      => $this->fecha,
                'aplicados'  => $aplicados,
            ]);

            return $aplicados;

        } finally {
            TenantHelper::switchToCentral();
        }
    }
}
