<?php

namespace App\Services;

use Rats\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\Log;

class ZKTecoService
{
    protected ZKTeco $zk;
    protected string $ip;
    protected int $port;

    public function __construct(string $ip, int $port = 4370)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->zk = new ZKTeco($ip, $port);
    }

    public function connect(): bool
    {
        try {
            return $this->zk->connect();
        } catch (\Exception $e) {
            Log::error("ZKTeco: Error conectando a {$this->ip}:{$this->port}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function disconnect(): void
    {
        try {
            $this->zk->disconnect();
        } catch (\Exception $e) {
            // silenciar errores de desconexión
        }
    }

    /**
     * Obtener información del dispositivo.
     */
    public function getDeviceInfo(): array
    {
        return [
            'nombre'     => trim(str_replace('~DeviceName=', '', $this->zk->deviceName())),
            'plataforma' => trim(str_replace('~Platform=', '', $this->zk->platform())),
            'firmware'   => trim(str_replace('~ZKFPVersion=', '', $this->zk->fmVersion())),
            'serial'     => trim(str_replace('~SerialNumber=', '', $this->zk->serialNumber())),
        ];
    }

    /**
     * Obtener todos los usuarios registrados en el dispositivo.
     */
    public function getUsers(): array
    {
        $users = @$this->zk->getUser();
        return is_array($users) ? $users : [];
    }

    /**
     * Obtener todos los registros de asistencia del dispositivo.
     */
    public function getAttendance(): array
    {
        $records = @$this->zk->getAttendance();
        return is_array($records) ? $records : [];
    }

    /**
     * Obtener registros de asistencia filtrados desde una fecha.
     */
    public function getAttendanceSince(?string $sinceDateTime = null): array
    {
        $records = $this->getAttendance();

        if ($sinceDateTime) {
            $records = array_filter($records, function ($record) use ($sinceDateTime) {
                return isset($record['timestamp']) && $record['timestamp'] > $sinceDateTime;
            });
        }

        return array_values($records);
    }

    /**
     * Limpiar los registros de asistencia del dispositivo.
     */
    public function clearAttendance(): bool
    {
        try {
            return $this->zk->clearAttendance();
        } catch (\Exception $e) {
            Log::error("ZKTeco: Error limpiando asistencia en {$this->ip}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reiniciar el dispositivo.
     */
    public function restart(): bool
    {
        try {
            return $this->zk->restart();
        } catch (\Exception $e) {
            Log::error("ZKTeco: Error reiniciando {$this->ip}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
