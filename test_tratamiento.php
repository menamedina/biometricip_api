<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'menamedina@gmail.com')->first();

echo "User ID: {$user->id}\n";
echo "Connection: {$user->getConnectionName()}\n";
echo "Table: {$user->getTable()}\n";
echo "tratamiento_datos: {$user->tratamiento_datos}\n";
echo "tratamiento_datos_at: {$user->tratamiento_datos_at}\n";
echo "In fillable: " . (in_array('tratamiento_datos', $user->getFillable()) ? 'YES' : 'NO') . "\n";

// Intentar update
try {
    $result = $user->update([
        'tratamiento_datos' => true,
        'tratamiento_datos_at' => now(),
    ]);
    echo "Update result: " . ($result ? 'TRUE' : 'FALSE') . "\n";

    $fresh = $user->fresh();
    echo "After update - tratamiento_datos: {$fresh->tratamiento_datos}\n";
    echo "After update - tratamiento_datos_at: {$fresh->tratamiento_datos_at}\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n";
}
