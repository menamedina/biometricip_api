# sync_test.ps1 - Prueba: lee asistencias del MB160 localmente
# Ruta final: C:\ExpertosIP\biometric\sync_test.ps1

$phpPath     = "php"
$scriptPath  = "$PSScriptRoot\get_attendance.php"
$deviceIp    = "10.1.40.23"
$devicePort  = 4370

Write-Host "=== BiometricIP Sync Test ===" -ForegroundColor Cyan
Write-Host "Dispositivo: $deviceIp`:$devicePort"
Write-Host "----------------------------------------"

# Llamar al PHP y obtener JSON
$output = & $phpPath $scriptPath $deviceIp $devicePort 2>&1

try {
    $result = $output | ConvertFrom-Json

    if ($result.success) {
        Write-Host "[OK] Conexion exitosa" -ForegroundColor Green
        Write-Host "Total registros: $($result.total)"

        # Mostrar ultimos 5 registros
        if ($result.total -gt 0) {
            Write-Host "`n--- Ultimos 5 registros ---"
            $last5 = $result.registros | Select-Object -Last 5
            foreach ($r in $last5) {
                Write-Host "  UID: $($r.uid) | ID: $($r.id) | Estado: $($r.state) | Fecha: $($r.timestamp)"
            }
        }
    } else {
        Write-Host "[ERROR] $($result.error)" -ForegroundColor Red
    }
} catch {
    Write-Host "[ERROR] Respuesta inesperada del PHP:" -ForegroundColor Red
    Write-Host $output
}
