<?php
/**
 * Script local: obtiene asistencias del MB160 y las retorna en JSON
 * Uso: php get_attendance.php <ip> <puerto>
 */

require __DIR__ . '/../vendor/autoload.php';

use Rats\Zkteco\Lib\ZKTeco;

$ip    = $argv[1] ?? '10.1.40.23';
$port  = (int)($argv[2] ?? 4370);

$zk = new ZKTeco($ip, $port);

try {
    $connected = $zk->connect();

    if (!$connected) {
        echo json_encode(['success' => false, 'error' => "No se pudo conectar a {$ip}:{$port}"]);
        exit(1);
    }

    $serial     = trim(str_replace('~SerialNumber=', '', $zk->serialNumber()));
    $attendance = @$zk->getAttendance();
    $zk->disconnect();

    echo json_encode([
        'success'    => true,
        'ip'         => $ip,
        'puerto'     => $port,
        'serial'     => $serial,
        'total'      => is_array($attendance) ? count($attendance) : 0,
        'registros'  => is_array($attendance) ? $attendance : [],
    ]);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit(1);
}
