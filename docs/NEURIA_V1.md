# Integración Neuria API v1

La API se consulta únicamente desde el servidor. El sincronizador importa cada
contenido en `noticias` como `pendiente`; la portada sigue leyendo la base local
y no depende de la disponibilidad de Neuria.

## Separación editorial

- Los campos `neuria_*` conservan el original, UUID y estado remoto.
- Los campos editoriales habituales contienen la versión administrada por ISFD.
- Una sincronización nunca sobrescribe silenciosamente la edición local.

## Configuración

Copiar `.env.example` como `.env` y completar, sin versionarlo:

```text
NEURIA_API_URL=https://URL-DE-NEURIA
NEURIA_API_TOKEN=TOKEN-DEL-MEDIO
NEURIA_API_TIMEOUT=30
NEURIA_SYNC_LIMIT=50
ISFD_CREDENTIAL_KEY=CLAVE-MAESTRA-ALEATORIA-DE-32-O-MAS-CARACTERES
```

El desarrollador root también puede administrar URL, token, timeout y límite
desde `Módulos → Credenciales Neuria`. El token se cifra con AES-256-GCM usando
`ISFD_CREDENTIAL_KEY`, nunca se vuelve a mostrar y la configuración de base de
datos tiene prioridad sobre las variables `NEURIA_API_*`.

Ejecutar `database/migrations/008_neuria_v1_sync_up.sql` antes del primer uso.

## Comandos manuales

```text
php bin/neuria_sync.php me
php bin/neuria_sync.php contents
php bin/neuria_sync.php changes
php bin/neuria_sync.php all
```

## Solicitudes con Agente IA

Desde Noticias, el botón `AGENTE IA` crea una solicitud mediante
`POST /api/v1/requests`. La tabla `neuria_requests` conserva UUID, estado,
progreso, payloads y errores. El seguimiento puede ejecutarse manualmente desde
el panel o por consola:

```text
php cron/sincronizar_neuria.php
```

Cuando Neuria informa un destino completado, ISFD descarga el `content_id` y lo
incorpora como noticia `pendiente`, con revisión editorial `pendiente`. El UUID
único evita importaciones duplicadas y la edición local permanece independiente.

Los cursores avanzan dentro de la transacción que procesa cada página y el
comando usa un bloqueo para impedir ejecuciones simultáneas.

## Cron sugerido (no activado)

```text
*/2 * * * * /usr/local/bin/php /RUTA-DEL-SITIO/cron/sincronizar_neuria.php >> /RUTA-PRIVADA/neuria-requests.log 2>&1
*/5 * * * * /usr/local/bin/php /RUTA-DEL-SITIO/bin/neuria_sync.php all >> /RUTA-PRIVADA/neuria-sync.log 2>&1
```

No activar el cron hasta validar manualmente identidad, permisos e importación.

## Eventos

- `CONTENT_AVAILABLE`: importa el UUID inexistente.
- `CONTENT_SUSPENDED`: bloquea y suspende la publicación.
- `CONTENT_REACTIVATED`: desbloquea y vuelve a pendiente.
- `CONTENT_WITHDRAWN`: bloquea y conserva el registro suspendido.

Las pruebas de `tests/neuria_v1_sync_test.php` usan exclusivamente el mock local.
