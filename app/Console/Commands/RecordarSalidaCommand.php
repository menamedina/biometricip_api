<?php

namespace App\Console\Commands;

use App\Helpers\TenantHelper;
use App\Models\AttendanceRecord;
use App\Models\Empresa;
use App\Models\HorarioDia;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RecordarSalidaCommand extends Command
{
    protected $signature = 'recordatorio:salida
                            {--minutos=15 : Minutos de anticipación antes de la hora de salida}
                            {--empresa_id= : Procesar solo esta empresa (modo manual)}
                            {--user_id= : Enviar push de prueba directo a este usuario (omite validaciones de horario)}';

    protected $description = 'Envía push a empleados próximos a su hora de salida que aún no han marcado (ejecutar cada minuto vía scheduler)';

    public function __construct(private readonly FcmService $fcm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $minutos   = (int) $this->option('minutos');
        $empresaId = $this->option('empresa_id');
        $userId    = $this->option('user_id');

        // Modo prueba: envío directo a un usuario sin validar horario ni asistencia
        if ($userId) {
            $resultado = $this->fcm->sendToUser(
                (int) $userId,
                '¡No olvides marcar tu salida! [PRUEBA]',
                'Este es un recordatorio de prueba. Recuerda registrar tu salida.',
                ['tipo' => 'recordatorio_salida', 'hora_salida' => Carbon::now()->format('H:i')]
            );
            $this->info("Push enviado al usuario {$userId}: success={$resultado['success']}, failure={$resultado['failure']}");
            return self::SUCCESS;
        }

        $empresas = $empresaId
            ? Empresa::where('id', $empresaId)->where('is_active', true)->get()
            : Empresa::where('is_active', true)->get();

        foreach ($empresas as $empresa) {
            $this->procesarEmpresa($empresa, $minutos);
        }

        return self::SUCCESS;
    }

    private function procesarEmpresa(Empresa $empresa, int $minutosAntes): void
    {
        try {
            TenantHelper::switchTenant($empresa->id);
        } catch (\Throwable $e) {
            Log::warning('RecordarSalidaCommand: tenant no disponible', [
                'empresa_id' => $empresa->id,
                'error'      => $e->getMessage(),
            ]);
            return;
        }

        try {
            $ahora    = Carbon::now();
            $objetivo = $ahora->copy()->addMinutes($minutosAntes);
            $diaISO   = (int) $ahora->format('N'); // 1=Lun ... 7=Dom

            // Ventana de exactamente 1 minuto para evitar duplicados entre ejecuciones
            $ventanaDesde = $objetivo->format('H:i:00');
            $ventanaHasta = $objetivo->copy()->addMinute()->format('H:i:00');

            $horarioIds = HorarioDia::where('dia_semana', $diaISO)
                ->whereNotNull('hora_salida')
                ->where('hora_salida', '>=', $ventanaDesde)
                ->where('hora_salida', '<', $ventanaHasta)
                ->pluck('horario_id')
                ->toArray();

            if (empty($horarioIds)) {
                return;
            }

            $userIds = User::where('empresa_id', $empresa->id)
                ->where('is_active', true)
                ->whereIn('horario_id', $horarioIds)
                ->pluck('id')
                ->toArray();

            if (empty($userIds)) {
                return;
            }

            $hoy = $ahora->toDateString();

            $conEntrada = AttendanceRecord::whereIn('user_id', $userIds)
                ->whereDate('fecha_hora', $hoy)
                ->where('tipo', 'entrada')
                ->pluck('user_id')
                ->unique()
                ->toArray();

            if (empty($conEntrada)) {
                return;
            }

            $conSalida = AttendanceRecord::whereIn('user_id', $conEntrada)
                ->whereDate('fecha_hora', $hoy)
                ->where('tipo', 'salida')
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $pendientes = array_values(array_diff($conEntrada, $conSalida));

            if (empty($pendientes)) {
                return;
            }

            $horaFormateada = $objetivo->format('H:i');

            $this->fcm->sendToUsers(
                $pendientes,
                '¡No olvides marcar tu salida!',
                "Tu jornada termina a las {$horaFormateada}. Recuerda registrar tu salida.",
                ['tipo' => 'recordatorio_salida', 'hora_salida' => $horaFormateada]
            );

            Log::info('RecordarSalidaCommand: notificaciones enviadas', [
                'empresa_id'    => $empresa->id,
                'pendientes'    => count($pendientes),
                'hora_objetivo' => $horaFormateada,
            ]);

        } finally {
            TenantHelper::switchToCentral();
        }
    }
}
