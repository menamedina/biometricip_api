<?php
/**
 * Script local: borra registros de asistencia del MB160
 * Uso: php clear_attendance.php <ip> <puerto>
 */

require __DIR__ . '/vendor/autoload.php';

use Rats\Zkteco\Lib\ZKTeco;

$ip   = $argv[1] ?? '10.1.40.14';
$port = (int)($argv[2] ?? 4370);

$zk = new ZKTeco($ip, $port);

try {
    $connected = $zk->connect();

    if (!$connected) {
        echo json_encode(['success' => false, 'error' => "No se pudo conectar a {$ip}:{$port}"]);
        exit(1);
    }

    $zk->clearAttendance();
    $zk->disconnect();

    echo json_encode(['success' => true, 'message' => "Registros borrados en {$ip}"]);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit(1);
}
