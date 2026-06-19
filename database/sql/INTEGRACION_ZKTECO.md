# Integración ZKTeco MB160 — BiometricIP

## Resumen

Integración del dispositivo biométrico ZKTeco MB160 con la plataforma BiometricIP para sincronización de registros de asistencia mediante protocolo UDP.

---

## 1. Requisitos

- PHP 8.x con extensión `sockets` habilitada
- Laravel 13.x
- Dispositivo ZKTeco MB160 accesible en red (local o pública)
- Base de datos tenant ya configurada

---

## 2. Instalación de la librería

```bash
cd biometricip_api
composer require rats/zkteco
```

### Habilitar extensión sockets en PHP

Editar el `php.ini` correspondiente:

```bash
# Buscar la ubicación del php.ini activo
php --ini

# En el php.ini, descomentar la línea:
extension=sockets
```

Reiniciar el servidor PHP/Laravel después del cambio.

### Parche de namespace (librería rats/zkteco)

La librería tiene un bug con namespaces en PHP 8.x. Aplicar estos cambios manualmente en los archivos vendor:

**`vendor/rats/zkteco/src/Lib/ZKTeco.php`**
```php
// Cambiar estas líneas:
$this->_zkclient = \socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
\socket_set_option($this->_zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);
\socket_sendto(...)
@\socket_recvfrom(...)
```

**`vendor/rats/zkteco/src/Lib/Helper/Connect.php`**
```php
\socket_sendto(...)
@\socket_recvfrom(...)
```

**`vendor/rats/zkteco/src/Lib/Helper/Util.php`**
```php
@\socket_recvfrom(...)
```

> **Nota:** Agregar `\` antes de todas las funciones `socket_*` para forzar el namespace global.

---

## 3. Base de datos

Ejecutar el script SQL en la BD **tenant** de cada empresa:

```bash
# MySQL
USE biometricip_1;  -- reemplazar con el nombre del tenant
SOURCE database/sql/11_dispositivos_biometricos.sql;
```

El script crea:
- `tbl_dispositivos_biometricos` — dispositivos registrados
- `tbl_sync_logs` — historial de sincronizaciones
- Modifica `tbl_registros_asistencia` — agrega columnas `dispositivo_id`, `uid_dispositivo` y el valor `dispositivo` al enum `metodo`

---

## 4. Archivos creados

| Archivo | Descripción |
|---------|-------------|
| `database/sql/11_dispositivos_biometricos.sql` | Script SQL para tablas |
| `app/Services/ZKTecoService.php` | Servicio de comunicación con el dispositivo |
| `app/Models/DispositivoBiometrico.php` | Modelo Eloquent de dispositivos |
| `app/Models/SyncLog.php` | Modelo Eloquent de logs de sync |
| `app/Http/Controllers/Api/DeviceController.php` | Controller API REST |
| `resources/views/admin/dispositivos/index.blade.php` | Vista web de gestión |

---

## 5. Endpoints API

Todos requieren autenticación `Bearer token` + rol `admin`.

| Método | Ruta | Descripción |
|--------|------|-------------|
| `POST` | `/api/devices/ping` | Probar conexión a una IP sin registrar |
| `GET` | `/api/devices` | Listar dispositivos |
| `POST` | `/api/devices` | Registrar dispositivo (prueba conexión automáticamente) |
| `GET` | `/api/devices/{id}` | Ver dispositivo |
| `PUT` | `/api/devices/{id}` | Actualizar dispositivo |
| `DELETE` | `/api/devices/{id}` | Eliminar dispositivo |
| `GET` | `/api/devices/{id}/test` | Probar conexión de dispositivo registrado |
| `GET` | `/api/devices/{id}/users` | Ver usuarios del dispositivo cruzados con cédulas |
| `POST` | `/api/devices/{id}/sync` | Sincronizar asistencias al sistema |
| `POST` | `/api/devices/{id}/clear` | Vaciar registros del dispositivo |
| `GET` | `/api/devices/{id}/sync-history` | Historial de sincronizaciones |

---

## 6. Lógica de sincronización

1. Se descargan **todos** los registros del dispositivo
2. Por cada registro se busca en `users` un empleado cuya `cedula` coincida con el `id` del registro
3. Si hay match → se crea el registro en `tbl_registros_asistencia` con `metodo = 'dispositivo'`
4. Si no hay match → se cuenta como `sin_usuario` y se omite
5. Duplicados se detectan por `uid_dispositivo` — no se insertan dos veces
6. Las coordenadas se toman de la sede vinculada al dispositivo
7. El tipo `entrada/salida` se determina por el campo `type` del dispositivo (`0 = entrada`, `1 = salida`)

---

## 7. Vinculación empleados ↔ dispositivo

Para que la sincronización funcione, la **cédula del empleado** en BiometricIP debe coincidir con el **ID del usuario** en el dispositivo ZKTeco.

**Desde la interfaz web:**
1. Ir a `Administración → Dispositivos`
2. Click en ícono de usuarios (👥) del dispositivo
3. Los usuarios sin vincular muestran botón **"Vincular"**
4. Buscar el empleado por nombre y asignarle la cédula del dispositivo

**Desde el módulo de Empleados:**
- Editar el empleado y ingresar su cédula coincidiendo con el ID del dispositivo

---

## 8. Interfaz web

Acceder en: `http://[servidor]/admin/dispositivos`

Funcionalidades disponibles:
- **Probar IP** — verificar conectividad antes de registrar
- **Nuevo Dispositivo** — registrar y vincular a una sede
- **Sync** — descargar todas las marcaciones del dispositivo
- **Vaciar** — limpiar registros del dispositivo (no afecta la BD)
- **Test conexión** — verificar estado del dispositivo registrado
- **Ver usuarios** — listar usuarios del dispositivo y vincularlos con empleados
- **Historial Sync** — ver logs de sincronizaciones anteriores
- **Editar / Eliminar** — gestión del dispositivo

---

## 9. Configuración en producción

### Opción A: Servidor en la misma red (recomendado)

No requiere configuración adicional. Registrar el dispositivo con su IP local.

```
Servidor Laravel (192.168.1.x) → MB160 (10.1.40.23:4370)
```

### Opción B: Servidor VPS en la nube

El MB160 está en la red local de la empresa y el servidor en internet.

**Pasos en el router de la empresa:**
1. Acceder al panel de administración del router
2. Configurar **Port Forwarding**:
   - Puerto externo: `4370`
   - Protocolo: `UDP`
   - IP destino: IP local del MB160 (ej. `10.1.40.23`)
   - Puerto destino: `4370`

**En BiometricIP:**
1. Editar el dispositivo
2. Cambiar la IP por la **IP pública** de la empresa
3. Puerto: `4370`

```
VPS Laravel ──internet──▶ IP pública empresa:4370 ──▶ MB160 (10.1.40.23:4370)
```

> **Advertencia:** El protocolo UDP sobre internet puede ser inestable. Si hay timeouts frecuentes, considerar implementar el modo PUSH del MB160 (el dispositivo envía las marcaciones al servidor por HTTP en tiempo real).

---

## 10. Troubleshooting

| Error | Causa | Solución |
|-------|-------|----------|
| `Call to undefined function socket_create()` | Extensión `sockets` no habilitada | Habilitar en `php.ini` y reiniciar servidor |
| `No se pudo conectar` | IP/puerto incorrecto o dispositivo apagado | Verificar IP, puerto 4370 UDP y conectividad |
| `Sin usuario en sistema: N` | Cédulas no registradas en BiometricIP | Vincular empleados desde la vista de dispositivos |
| Tipo siempre `salida` | Campo `state` en lugar de `type` | El MB160 usa `type`: `0=entrada`, `1=salida` |
| Registros en BD central en vez de tenant | Consulta en BD equivocada | Los registros van en `biometricip_{empresa_id}` |

---

## 11. Notas importantes

- La librería `rats/zkteco` puede requerir reaplicar el parche de namespace después de `composer update`
- Los registros del dispositivo **no se borran automáticamente** al sincronizar
- El botón **"Vaciar"** borra los registros del dispositivo pero **no** los de BiometricIP
- La sincronización es idempotente — ejecutarla varias veces no duplica registros
- SweetAlert2 se carga desde CDN en el layout admin para los diálogos de confirmación
