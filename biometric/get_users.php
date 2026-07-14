<?php
/**
 * Script local: obtiene usuarios registrados en el MB160 y los retorna en JSON
 * Uso: php get_users.php <ip> <puerto>
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

    $serial = trim(str_replace('~SerialNumber=', '', $zk->serialNumber()));
    $users  = @$zk->getUser();
    $zk->disconnect();

    echo json_encode([
        'success'  => true,
        'ip'       => $ip,
        'puerto'   => $port,
        'serial'   => $serial,
        'total'    => is_array($users) ? count($users) : 0,
        'usuarios' => is_array($users) ? $users : [],
    ]);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit(1);
}
