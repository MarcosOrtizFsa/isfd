# Integración Neuria API v1

Revisado: 4 de noviembre de 2026. NEURIA es el servicio central de producción y distribución asistida por IA; ISFD mantiene la edición, autorización de publicación y persistencia local. El backend Laravel 13 de NEURIA pertenece a otro proyecto. No hay un SDK de modelo generativo conectado directamente a la portada ni envío de conversaciones del chat a NEURIA.

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

## Recuperación de solicitudes en ERROR

`sincronizarPendientes()` vuelve a consultar los registros ERROR. Un reintento en NEURIA puede llevarlos a PENDIENTE, INVESTIGANDO, GENERANDO o COMPLETADA. La actualización conserva UUID, actualiza estado/progreso y limpia `error_message` cuando el nuevo estado ya no es ERROR. Si deja de ser terminal, limpia `completed_at`.

Al completarse, descarga los contenidos de destinos completados. Los UUID únicos y la importación transaccional evitan duplicados; una descarga fallida revierte la actualización para que pueda reintentarse. COMPLETADA, PARCIALMENTE_COMPLETADA y CANCELADA se excluyen de la consulta periódica actual.

## Texto y revisión humana

El cuerpo recibido de NEURIA se conserva como texto plano, sin agregar envoltorios `<p>` al guardar. El editor mantiene separaciones entre párrafos y la vista pública prepara su presentación. La sincronización conserva originales remotos y no reemplaza silenciosamente la versión editada por el administrador.

La generación asistida no confirma exactitud, pertinencia ni autorización institucional. El responsable humano revisa datos, fuentes y redacción antes de activar la noticia. Esta IA operativa es distinta del agente Codex utilizado para desarrollar el sitio.

## Diagnóstico desde Noticias

`app_neuria_status.php` consulta `GET /api/v1/me` al ingresar al módulo y al pulsar Volver a comprobar. Usa timeout máximo de cinco segundos, sin reintentos; muestra conexión/autenticación, no ejecución de cron ni éxito de generación. El diagnóstico no modifica los cursores.

El endpoint requiere modo 0, 1 o 2 y CSRF; no entrega tokens ni respuesta cruda. Trata una credencial desactivada como integración desactivada. El cliente general mantiene fallback al entorno cuando no obtiene una configuración activa/descifrable de BD: revisar entorno y tareas programadas si se necesita detener toda comunicación.

## Endpoints consumidos

`GET /api/v1/me`, `GET /api/v1/contents`, `GET /api/v1/contents/{uuid}`, `GET /api/v1/changes`, `POST /api/v1/requests` y `GET /api/v1/requests/{uuid}`. Autenticación Bearer desde PHP. Scopes utilizados durante la integración: `content:read`, `requests:write`. El contrato interno real de cada respuesta debe validarse con el proveedor; no enviar secretos del proyecto en descripciones ni URLs iniciales.
