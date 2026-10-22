<?php

namespace App\Console\Commands;

use App\Helpers\TenantHelper;
use App\Jobs\EnviarReporteLiderJob;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarReporteLideresCommand extends Command
{
    protected $signature = 'reporte:lideres
                            {--empresa_id= : Procesar solo esta empresa}
                            {--lider_id=   : Enviar reporte solo a este líder}';

    protected $description = 'Envía el reporte diario de personal a cada líder (ejecutar a las 8 AM)';

    public function handle(): int
    {
        $empresaId = $this->option('empresa_id');

        $query = Empresa::where('is_active', true);

        if ($empresaId) {
            $query->where('id', (int) $empresaId);
        }

        $empresas = $query->get();

        if ($empresas->isEmpty()) {
            $this->warn('No se encontraron empresas activas.');
            return Command::SUCCESS;
        }

        foreach ($empresas as $empresa) {
            $this->procesarEmpresa($empresa);
        }

        $this->info('Reporte de líderes despachado correctamente.');
        return Command::SUCCESS;
    }

    private function procesarEmpresa(Empresa $empresa): void
    {
        // Verificar que el tenant existe
        try {
            TenantHelper::switchTenant($empresa->id);
            TenantHelper::switchToCentral();
        } catch (\Throwable $e) {
            $this->warn("Empresa {$empresa->id} ({$empresa->nombre}): tenant no configurado, omitida.");
            Log::warning('reporte:lideres - tenant no encontrado', [
                'empresa_id' => $empresa->id,
                'error'      => $e->getMessage(),
            ]);
            return;
        }

        // Obtener IDs únicos de líderes que tienen empleados activos en esta empresa
        $query = User::where('empresa_id', $empresa->id)
            ->where('is_active', true)
            ->whereNotNull('lider_id')
            ->distinct();

        // Filtrar por líder específico si se pasó la opción
        $liderIdFiltro = $this->option('lider_id');
        if ($liderIdFiltro) {
            $query->where('lider_id', (int) $liderIdFiltro);
        }

        $liderIds = $query->pluck('lider_id');

        if ($liderIds->isEmpty()) {
            $this->line("Empresa {$empresa->id} ({$empresa->nombre}): sin líderes asignados.");
            return;
        }

        $despachados = 0;

        foreach ($liderIds as $liderId) {
            $lider = User::find($liderId);

            if (!$lider) {
                $this->warn("Líder ID {$liderId} no encontrado, omitido.");
                continue;
            }

            if (empty($lider->email)) {
                $this->warn("Líder {$lider->name} (ID:{$liderId}) sin email, omitido.");
                continue;
            }

            EnviarReporteLiderJob::dispatchSync($liderId, $empresa->id);
            $despachados++;

            $this->info("  → Reporte despachado: {$lider->name} <{$lider->email}> — Empresa: {$empresa->nombre}");
        }

        $this->line("Empresa {$empresa->nombre}: {$despachados} reportes despachados.");
    }
}
