# BiometricIPAgent - Instalacion como Servicio Windows

## Estructura de carpetas

```
C:\ExpertosIP\biometric\
  sync_devices.ps1
  get_attendance.php
  composer.json
  composer.phar
  vendor\              (generado por composer)
  php\                 (PHP portable)
    php.exe
    php.ini
    ext\
    ...
```

---

## FASE 1: PHP Portable

### Paso 1.1 - Descargar PHP portable
Descargar desde `https://windows.php.net/download/` la version:
**PHP 8.3 - VS16 x64 Thread Safe - Zip**

Extraer el contenido en `C:\ExpertosIP\biometric\php\`

### Paso 1.2 - Configurar php.ini
Renombrar `php.ini-production` a `php.ini` y habilitar las extensiones:

```ini
extension_dir = "C:\ExpertosIP\biometric\php\ext"
extension=sockets
extension=openssl
```

### Paso 1.3 - Verificar sockets
```powershell
C:\ExpertosIP\biometric\php\php.exe -m | findstr sockets
```
Debe mostrar: `sockets`

---

## FASE 2: Instalar dependencias (rats/zkteco)

### Paso 2.1 - Descargar composer.phar
Descargar `composer.phar` desde `https://getcomposer.org/download/` y copiarlo en `C:\ExpertosIP\biometric\`

### Paso 2.2 - Instalar dependencias
```powershell
cd C:\ExpertosIP\biometric
C:\ExpertosIP\biometric\php\php.exe composer.phar install
```

Esto genera la carpeta `vendor\` con solo `rats/zkteco`.

---

## FASE 3: Descargar NSSM

Abrir **PowerShell como Administrador** y ejecutar:

```powershell
Invoke-WebRequest -Uri "https://nssm.cc/release/nssm-2.24.zip" -OutFile "C:\nssm.zip"
Expand-Archive "C:\nssm.zip" -DestinationPath "C:\nssm"
```

---

## FASE 4: Instalar como Servicio Windows

Abrir **CMD como Administrador** y ejecutar cada linea por separado:

### Paso 4.1 - Crear el servicio
```cmd
nssm install "BiometricIPAgent" powershell.exe
```

### Paso 4.2 - Configurar el script
```cmd
nssm set "BiometricIPAgent" AppParameters "-ExecutionPolicy Bypass -File C:\ExpertosIP\biometric\sync_devices.ps1"
```

### Paso 4.3 - Carpeta de trabajo
```cmd
nssm set "BiometricIPAgent" AppDirectory "C:\ExpertosIP\biometric"
```

### Paso 4.4 - Nombre visible
```cmd
nssm set "BiometricIPAgent" DisplayName "BiometricIPAgent - Sincronizador de Asistencias ZKTeco"
```

### Paso 4.5 - Inicio automatico
```cmd
nssm set "BiometricIPAgent" Start SERVICE_AUTO_START
```

### Paso 4.6 - Iniciar el servicio
```cmd
nssm start "BiometricIPAgent"
```

Debe responder: `SERVICE_RUNNING`

---

## FASE 5: Verificar el servicio

```cmd
nssm status "BiometricIPAgent"
```

Tambien visible en: `Servicios de Windows` (services.msc) como **BiometricIPAgent**

---

## Comandos utiles

| Accion | Comando |
|--------|---------|
| Ver estado | `nssm status "BiometricIPAgent"` |
| Detener | `nssm stop "BiometricIPAgent"` |
| Reiniciar | `nssm restart "BiometricIPAgent"` |
| Desinstalar | `nssm remove "BiometricIPAgent" confirm` |

---

## Prueba manual (sin servicio)

```powershell
powershell.exe -ExecutionPolicy Bypass -File C:\ExpertosIP\biometric\sync_devices.ps1
```

Presionar `Ctrl+C` para detener.

---

## Configuracion del agente

Editar `sync_devices.ps1` para ajustar:

- `$VPS_URL` - URL del servidor
- `$AGENT_TOKEN` - Token generado en el panel admin (Empresas > icono llave)
- `$INTERVAL_MINS` - Intervalo de sincronizacion en minutos (default: 5)
- `$DEVICES` - Lista de dispositivos ZKTeco en la red local
