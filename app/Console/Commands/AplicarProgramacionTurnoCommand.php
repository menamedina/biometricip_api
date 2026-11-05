<?php

namespace App\Console\Commands;

use App\Jobs\AplicarTurnoEmpresaJob;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AplicarProgramacionTurnoCommand extends Command
{
    protected $signature = 'turno:aplicar
                            {--empresa_id= : Procesar solo esta empresa (ignora dia y hora)}
                            {--dia=        : Dia de semana 0=Dom..6=Sab (viene del scheduler)}
                            {--hora=       : Hora de ejecucion H:i (viene del scheduler)}
                            {--fecha=      : Fecha Y-m-d a procesar (solo pruebas manuales, default=hoy)}
                            {--ahora       : Aplica inmediatamente toda la programacion pendiente sin importar la fecha}';

    protected $description = 'Aplica la programacion de turno: actualiza horario_id en users y limpia la tabla.';

    public function handle(): int
    {
        $hoy             = Carbon::today();
        $empresaIdFiltro = $this->option('empresa_id');
        $diaFiltro       = $this->option('dia');
        $horaFiltro      = $this->option('hora');

        $forzar = (bool) $empresaIdFiltro;

        $query = Empresa::where('is_active', true);

        if ($empresaIdFiltro) {
            // Ejecución manual: procesar solo esta empresa sin validar día ni hora
            $query->where('id', (int) $empresaIdFiltro);
        } else {
            // Ejecución automática desde el scheduler: filtrar por día y hora configurados
            if ($diaFiltro === null || $horaFiltro === null) {
                $this->warn('Se requiere --dia y --hora para ejecución automática.');
                return Command::FAILURE;
            }

            $query->where('dia_ejecucion_turno', (int) $diaFiltro)
                  ->whereRaw("TIME_FORMAT(hora_ejecucion_turno, '%H:%i') = ?", [$horaFiltro]);
        }

        $empresas = $query->get();

        if ($empresas->isEmpty()) {
            $this->info('Sin empresas para procesar en esta ejecución.');
            return Command::SUCCESS;
        }

        $ahora = $this->option('ahora');
        $fecha = $this->option('fecha') ?? $hoy->toDateString();

        foreach ($empresas as $empresa) {
            if ($ahora) {
                $this->info("Procesando (--ahora): {$empresa->nombre} (ID: {$empresa->id})");
                $aplicados = AplicarTurnoEmpresaJob::dispatchSync($empresa->id, null);
            } else {
                $this->info("Procesando: {$empresa->nombre} (ID: {$empresa->id}) — {$fecha}");
                $aplicados = AplicarTurnoEmpresaJob::dispatchSync($empresa->id, $fecha);
            }
            $this->info("  → {$aplicados} turno(s) aplicado(s).");
        }

        $this->info('Comando turno:aplicar finalizado.');
        return Command::SUCCESS;
    }
}
