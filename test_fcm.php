<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $messaging = app(\Kreait\Firebase\Contract\Messaging::class);
    $token = \App\Models\DeviceToken::where('user_id', 1)->where('is_active', true)->first();

    if (!$token) {
        echo "No se encontro token FCM para user_id=1\n";
        exit(1);
    }

    echo "Token encontrado: " . substr($token->token, 0, 30) . "...\n";

    $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
        'token' => $token->token,
        'notification' => [
            'title' => 'Prueba BiometricIP',
            'body' => 'Notificacion de prueba desde Laravel',
        ],
    ]);

    $result = $messaging->send($message);
    echo "Enviado OK!\n";
    echo json_encode($result) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
