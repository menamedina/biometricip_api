<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\DispositivoBiometrico;
use App\Models\Horario;
use App\Models\SyncLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    /**
     * GET  /api/iclock/cdata  — registro y heartbeat del dispositivo
     * POST /api/iclock/cdata  — push de registros de asistencia
     */
    public function cdata(Request $request): Response
    {
        $sn    = $request->query('SN');
        $table = $request->query('table');

        if (!$sn) {
            return response('ERROR: No SN', 200)->header('Content-Type', 'text/plain');
        }

        Log::info('ADMS cdata', [
            'method' => $request->method(),
            'SN'     => $sn,
            'table'  => $table,
        ]);

        if ($request->isMethod('POST') && $table === 'ATTLOG') {
            return $this->handleAttendancePush($sn, $request);
        }

        return $this->handleRegistration($sn);
    }

    /**
     * GET /api/iclock/getrequest — el dispositivo solicita comandos pendientes
     */
    public function getrequest(Request $request): Response
    {
        Log::info('ADMS getrequest', ['SN' => $request->query('SN')]);
        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Responde con la configuración que el dispositivo debe usar.
     */
    private function handleRegistration(string $sn): Response
    {
        $body  = "GET OPTION FROM: {$sn}\r\n";
        $body .= "ATTLOGStamp=0\r\n";
        $body .= "OPERLOGStamp=9999\r\n";
        $body .= "ATTPHOTOStamp=0\r\n";
        $body .= "ErrorDelay=30\r\n";
        $body .= "Delay=10\r\n";
        $body .= "TransTimes=00:00;14:05\r\n";
        $body .= "TransInterval=1\r\n";
        $body .= "TransFlag=TransData AttLog\r\n";
        $body .= "TimeZone=0\r\n";
        $body .= "Realtime=1\r\n";
        $body .= "Encrypt=0\r\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Procesa las marcaciones enviadas por el dispositivo via PUSH.
     * Formato ATTLOG: userid\tchecktime\tstatus\tverify\tworkcode\treserved
     * status: 0 = entrada, 1 = salida
     */
    private function handleAttendancePush(string $sn, Request $request): Response
    {
        $result = $this->findDeviceBySn($sn);

        if (!$result) {
            Log::warning('ADMS: dispositivo no encontrado', ['SN' => $sn]);
            return response("OK: 0", 200)->header('Content-Type', 'text/plain');
        }

        $device = $result['device'];

        $lines   = array_filter(explode("\n", trim($request->getContent())));
        $created = 0;
        $skipped = 0;
        $noUser  = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line === 'ATTLOG') {
                continue;
            }

            $parts     = explode("\t", $line);
            $cedula    = trim($parts[0] ?? '');
            $timestamp = trim($parts[1] ?? '');
            $status    = (int) trim($parts[2] ?? 0);

            if (!$cedula || !$timestamp) {
                $skipped++;
                continue;
            }

            $userId = User::where('cedula', $cedula)->value('id');

            if (!$userId) {
                $noUser++;
                continue;
            }

            // uid único para deduplicación: serial + cédula + timestamp
            $uid = md5($sn . $cedula . $timestamp);

            $exists = AttendanceRecord::where('uid_dispositivo', $uid)
                ->where('dispositivo_id', $device->id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $empleado = User::find($userId);
            $horario  = $empleado?->horario_id ? Horario::find($empleado->horario_id) : null;

            AttendanceRecord::create([
                'user_id'               => $userId,
                'sede_id'               => $device->sede_id,
                'horario_id'            => $horario?->id,
                'tipo'                  => $status === 0 ? 'entrada' : 'salida',
                'lat'                   => $device->sede?->lat ?? 0,
                'lng'                   => $device->sede?->lng ?? 0,
                'metodo'                => 'dispositivo',
                'qr_validado'           => false,
                'geocerca_validada'     => true,
                'distancia_oficina_mts' => 0,
                'fecha_hora'            => Carbon::parse($timestamp),
                'dispositivo_id'        => $device->id,
                'uid_dispositivo'       => $uid,
            ]);

            $created++;
        }

        $device->update(['ultima_sync' => now()]);

        SyncLog::create([
            'dispositivo_id'   => $device->id,
            'registros_nuevos' => $created,
            'registros_total'  => $created + $skipped + $noUser,
            'status'           => 'ok',
            'mensaje'          => "PUSH - Nuevos: {$created}, Omitidos: {$skipped}, Sin usuario: {$noUser}",
            'created_at'       => now(),
        ]);

        Log::info('ADMS push procesado', [
            'SN'      => $sn,
            'created' => $created,
            'skipped' => $skipped,
            'noUser'  => $noUser,
        ]);

        return response("OK: {$created}", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Busca el dispositivo por número de serie iterando sobre todos los tenants.
     * Deja la conexión 'tenant' apuntando al tenant del dispositivo encontrado.
     */
    private function findDeviceBySn(string $sn): ?array
    {
        $tenants = DB::connection('mysql')->table('tenants')->get();

        foreach ($tenants as $tenant) {
            try {
                TenantHelper::switchTenant($tenant->empresa_id);

                $device = DispositivoBiometrico::where('numero_serie', $sn)
                    ->with('sede')
                    ->first();

                if ($device) {
                    return [
                        'device'     => $device,
                        'empresa_id' => $tenant->empresa_id,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('ADMS: error switching tenant', [
                    'empresa_id' => $tenant->empresa_id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return null;
    }
}
