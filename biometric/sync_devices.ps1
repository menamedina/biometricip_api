# sync_devices.ps1 - Agente local BiometricIP
# Ruta final: C:\ExpertosIP\biometric\sync_devices.ps1

# --- CONFIGURACION ---
#$VPS_URL       = "https://biometricip.innovasoftip.com"
#$VPS_URL      = "http://82.180.160.92:8000"
$VPS_URL      = "http://10.1.40.14:8000"
#$AGENT_TOKEN   = "jaLESXY799kLsQelzgMNgzVAAwJkjIi8bqfLq1x5EHerD3sF" #pro
$AGENT_TOKEN  = "7nWBS1dkjwjBCrziCMeNA6HtWoCY0k8c0RIdnfj2N8e1IvXq" #test
$PHP_PATH      = "$PSScriptRoot\php\php.exe"
$SCRIPT_DIR    = $PSScriptRoot
$INTERVAL_MINS = 5
$AUTO_CLEAR    = $true   # Borrar registros del dispositivo despues de sync exitoso

# Dispositivos locales: id = ID en el sistema, ip = IP local, puerto = puerto
$DEVICES = @(
    @{ id = 1; nombre = "MB160 Principal"; ip = "10.1.40.33"; puerto = 4370 }
    <# @{ id = 2; nombre = "MB160 Recepcion"; ip = "10.1.40.24"; puerto = 4370 }
       @{ id = 3; nombre = "MB160 Bodega";    ip = "10.1.40.25"; puerto = 4370 } #>
)
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

function Sync-Devices {
    Write-Log "=== BiometricIP Sync Agent ===" "Cyan"
    Write-Log "Dispositivos configurados: $($DEVICES.Count)" "Green"

    foreach ($device in $DEVICES) {
        Write-Log "Procesando: $($device.nombre) ($($device.ip):$($device.puerto))" "Cyan"

        # --- Sincronizar usuarios del dispositivo ---
        $usersOutput = & $PHP_PATH "$SCRIPT_DIR\get_users.php" $device.ip $device.puerto 2>&1
        try {
            $usersData = $usersOutput | ConvertFrom-Json
            if ($usersData.success -and $usersData.total -gt 0) {
                $usersBody = @{
                    device_id = $device.id
                    serial    = $usersData.serial
                    usuarios  = $usersData.usuarios
                } | ConvertTo-Json -Depth 5
                try {
                    $usersRes = Invoke-RestMethod -Uri "$VPS_URL/api/agent/users/sync" -Method POST -Headers $headers -Body ([System.Text.Encoding]::UTF8.GetBytes($usersBody))
                    Write-Log "  Usuarios - Creados: $($usersRes.creados) | Omitidos: $($usersRes.omitidos)" "Green"
                } catch {
                    Write-Log "  AVISO usuarios: $_" "Yellow"
                }
            }
        } catch {
            Write-Log "  AVISO: no se pudieron leer usuarios del dispositivo" "Yellow"
        }

        # --- Sincronizar marcaciones ---
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
            serial    = $data.serial
            registros = $data.registros
        } | ConvertTo-Json -Depth 5

        try {
            $syncRes = Invoke-RestMethod -Uri "$VPS_URL/api/agent/sync" -Method POST -Headers $headers -Body ([System.Text.Encoding]::UTF8.GetBytes($body))
            Write-Log "  OK - Nuevos: $($syncRes.registros_nuevos) | Omitidos: $($syncRes.omitidos) | Sin usuario: $($syncRes.sin_usuario)" "Green"

            if ($AUTO_CLEAR) {
                if ($syncRes.sin_usuario -gt 0) {
                    Write-Log "  AVISO: $($syncRes.sin_usuario) registros sin usuario. Dispositivo NO limpiado para no perder datos." "Yellow"
                } else {
                    $clearOutput = & $PHP_PATH "$SCRIPT_DIR\clear_attendance.php" $device.ip $device.puerto 2>&1
                    try {
                        $clearRes = $clearOutput | ConvertFrom-Json
                        if ($clearRes.success) {
                            Write-Log "  Dispositivo limpiado correctamente" "Green"
                        } else {
                            Write-Log "  AVISO al limpiar dispositivo: $($clearRes.error)" "Yellow"
                        }
                    } catch {
                        Write-Log "  AVISO: respuesta invalida al limpiar dispositivo" "Yellow"
                    }
                }
            }
        } catch {
            Write-Log "  ERROR al sincronizar: $_" "Red"
        }
    }

    Write-Log "=== Sync completado ===" "Cyan"
}

# Loop principal — cuando corre como servicio NSSM
while ($true) {
    Sync-Devices
    Start-Sleep -Seconds ($INTERVAL_MINS * 60)
}
