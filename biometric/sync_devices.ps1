# sync_devices.ps1 - Agente local BiometricIP
# Ruta final: C:\ExpertosIP\biometric\sync_devices.ps1

# --- CONFIGURACION ---
$VPS_URL     = "https://biometricip.innovasoftip.com"
#$VPS_URL    = "http://82.180.160.92:8000"
#$VPS_URL    = "http://10.1.40.14:8000"
$AGENT_TOKEN = "jaLESXY799kLsQelzgMNgzVAAwJkjIi8bqfLq1x5EHerD3sF"
#$AGENT_TOKEN = "7nWBS1dkjwjBCrziCMeNA6HtWoCY0k8c0RIdnfj2N8e1IvXq"
$PHP_PATH    = "php"
$SCRIPT_DIR  = $PSScriptRoot
# ---------------------

$headers = @{
    "X-Agent-Token" = $AGENT_TOKEN
    "Content-Type"  = "application/json"
}

function Write-Log {
    param([string]$msg, [string]$color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] $msg" -ForegroundColor $color
}

Write-Log "=== BiometricIP Sync Agent ===" "Cyan"
Write-Log "Obteniendo dispositivos desde el VPS..."

try {
    $res     = Invoke-RestMethod -Uri "$VPS_URL/api/agent/devices" -Method GET -Headers $headers
    $devices = $res.devices
    Write-Log "Dispositivos encontrados: $($devices.Count)" "Green"
} catch {
    Write-Log "ERROR al obtener dispositivos: $_" "Red"
    exit 1
}

if ($devices.Count -eq 0) {
    Write-Log "No hay dispositivos activos. Fin." "Yellow"
    exit 0
}

foreach ($device in $devices) {
    Write-Log "Procesando: $($device.nombre) ($($device.ip):$($device.puerto))" "Cyan"

    $phpOutput = & $PHP_PATH "$SCRIPT_DIR\get_attendance.php" $device.ip $device.puerto 2>&1

    try {
        $data = $phpOutput | ConvertFrom-Json
    } catch {
        Write-Log "  ERROR: respuesta PHP invalida para $($device.nombre)" "Red"
        continue
    }

    if (-not $data.success) {
        Write-Log "  ERROR conexion: $($data.error)" "Red"
        continue
    }

    Write-Log "  Registros en dispositivo: $($data.total)"

    if ($data.total -eq 0) {
        Write-Log "  Sin registros. Saltando." "Yellow"
        continue
    }

    $body = @{
        device_id = $device.id
        registros = $data.registros
    } | ConvertTo-Json -Depth 5

    try {
        $syncRes = Invoke-RestMethod -Uri "$VPS_URL/api/agent/sync" -Method POST -Headers $headers -Body $body
        Write-Log "  OK - Nuevos: $($syncRes.registros_nuevos) | Omitidos: $($syncRes.omitidos) | Sin usuario: $($syncRes.sin_usuario)" "Green"
    } catch {
        Write-Log "  ERROR al sincronizar: $_" "Red"
    }
}

Write-Log "=== Sync completado ===" "Cyan"
