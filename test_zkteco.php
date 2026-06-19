<?php
/**
 * Script de prueba de conexión con ZKTeco MB160
 * Ejecutar: php test_zkteco.php
 */

require __DIR__ . '/vendor/autoload.php';

use Rats\Zkteco\Lib\ZKTeco;

$ip = '10.1.40.23';
$port = 4370;

echo "=== Prueba de conexión ZKTeco MB160 ===\n";
echo "IP: {$ip}\n";
echo "Puerto: {$port}\n";
echo "----------------------------------------\n";

$zk = new ZKTeco($ip, $port);

try {
    $connected = $zk->connect();

    if ($connected) {
        echo "[OK] Conexión exitosa!\n\n";

        // Info del dispositivo
        echo "--- Información del dispositivo ---\n";
        echo "Nombre:       " . $zk->deviceName() . "\n";
        echo "Plataforma:   " . $zk->platform() . "\n";
        echo "Versión FW:   " . $zk->fmVersion() . "\n";
        echo "Nº Serie:     " . $zk->serialNumber() . "\n";
        echo "MAC:          " . $zk->faceFunctionOn() . "\n";

        // Conteos
        $users = $zk->getUser();
        $attendance = $zk->getAttendance();

        echo "\n--- Datos almacenados ---\n";
        echo "Usuarios registrados: " . (is_array($users) ? count($users) : 0) . "\n";
        echo "Registros asistencia: " . (is_array($attendance) ? count($attendance) : 0) . "\n";

        // Mostrar primeros 5 registros de asistencia si existen
        if (is_array($attendance) && count($attendance) > 0) {
            echo "\n--- Últimos 5 registros ---\n";
            $last5 = array_slice($attendance, -5);
            foreach ($last5 as $record) {
                echo sprintf(
                    "  UID: %s | ID: %s | Estado: %s | Fecha: %s\n",
                    $record['uid'] ?? '-',
                    $record['id'] ?? '-',
                    $record['state'] ?? '-',
                    $record['timestamp'] ?? '-'
                );
            }
        }

        $zk->disconnect();
        echo "\n[OK] Desconectado correctamente.\n";
    } else {
        echo "[ERROR] No se pudo conectar al dispositivo.\n";
        echo "Verifica:\n";
        echo "  1. Que la IP {$ip} sea correcta\n";
        echo "  2. Que el puerto {$port} esté abierto\n";
        echo "  3. Que el servidor esté en la misma red\n";
        echo "  4. Que el dispositivo esté encendido\n";
    }
} catch (\Exception $e) {
    echo "[ERROR] Excepción: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
