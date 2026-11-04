# WebSocket local con respaldo AJAX

Implementado con PHP, Ratchet 0.4.4 y ReactPHP. `chat/lib/actions.php` centraliza las operaciones de ambos transportes. La capa WebSocket no publica mensajes por su cuenta ni evita permisos, moderación, límites o claves de idempotencia.

## Iniciar en Windows / XAMPP

Apache y MySQL deben estar activos. Desde la carpeta del proyecto:

```powershell
php database/migrar_chat.php
composer install --working-dir=chat/server --no-dev --prefer-dist --no-interaction --no-scripts
./bin/chat_websocket.ps1 start
./bin/chat_websocket.ps1 status
```

El comando inicia PHP en segundo plano y registra su PID verificado. Para detenerlo:

```powershell
./bin/chat_websocket.ps1 stop
```

Para ejecutarlo en una terminal de forma explícita:

```powershell
php chat/server/run.php
```

No usar ambos métodos a la vez: comparten el puerto 8087. El arranque no se configura automáticamente al iniciar Windows. Si reiniciás el equipo, ejecutá `start` nuevamente. Los registros están en `chat/server/server.log` y `error.log`; vendor, PID y logs no se versionan. `composer.lock` sí se conserva.

Si ya está instalada la migración 018, alcanza con `php database/migrar_chat_websocket.php` para agregar la 019.

## Direcciones y configuración

En localhost se detecta automáticamente `ws://localhost:8087` o `ws://127.0.0.1:8087`. El servidor escucha **solo en 127.0.0.1**, no se expone a otras computadoras de la red.

Variables opcionales en el `.env` privado de la raíz o en el entorno del proceso:

```dotenv
CHAT_WS_LISTEN=127.0.0.1:8087
CHAT_WS_ORIGINS=http://localhost,http://127.0.0.1
CHAT_WS_URL=ws://localhost:8087
```

Los orígenes se comparan completos, incluyendo protocolo y puerto. Si Apache usa otro puerto, agregá por ejemplo `http://localhost:8080`. `CHAT_WS_URL` vacía desactiva WebSocket y conserva AJAX. Reiniciar el servidor después de cambiar su configuración.

**Producción:** no copiar la URL localhost. Debe configurarse una URL `wss://` del servidor real, con proxy inverso y certificado válidos, y `CHAT_WS_ORIGINS=https://isfd.com.ar`. La página HTTPS rechaza `ws://` para evitar contenido mixto. Sin URL de producción no intenta conectarse al localhost del visitante. El proceso debe ejecutarse bajo un supervisor provisto por el hosting/VPS. Esta configuración de infraestructura aún no se realizó en producción.

## Conexión, seguridad y eventos

1. Login y registro continúan por HTTPS/HTTP local con sesión PHP y CSRF.
2. `POST api.php` con `action=ws-token` entrega una credencial aleatoria de un uso, válida por 60 segundos para abrir la conexión. No contiene contraseña ni ID de sesión; solo su hash se almacena en BD.
3. El navegador conecta y manda la credencial en el primer mensaje, nunca en la URL. El servidor exige un origen permitido, autentica la cuenta y consume la credencial atómicamente.
4. Cada solicitud utiliza la identidad autenticada del socket y consulta si la credencial sigue vigente. No acepta un usuario enviado por el cliente. Las conexiones duran hasta una hora y se renuevan con una credencial nueva.
5. Logout y cambio de cuenta revocan las credenciales de esa sesión. Las expulsiones se comprueban al operar y en la verificación periódica de conexiones.
6. Las operaciones por HTTP o WebSocket incrementan un contador de revisión. El proceso lo consulta cada 500 ms para detectar también cambios provenientes de AJAX o moderación. No consulta el historial completo de cada usuario en ese ciclo.
7. El servidor emite avisos sin contenido privado; cada cliente recupera sus mensajes/avisos por el socket con permisos normales. El mantenimiento de presencia y recuperación de brechas ocurre cada 20 segundos.

Hay timeout de autenticación de 5 segundos, comprobación de conexiones, límite de 200 sockets y límite por conexión de 80 solicitudes cada 10 segundos, además de los límites de negocio existentes. Estos valores son protecciones, no una garantía de capacidad: las consultas MySQL actuales son síncronas y hay que probar carga antes de una exposición grande.

`chat/server/.htaccess` impide el acceso web al servidor, dependencias y registros. Solo se inicia por PHP CLI. No modificar archivos dentro de vendor.

## Caída y reconexión

- El chat funciona por AJAX durante el primer intento de conexión.
- Al autenticar WebSocket muestra **En tiempo real · WebSocket** y deja de consultar periódicamente por HTTP.
- Si el socket se corta o una respuesta tarda más de 5 segundos, muestra **Conexión alternativa · AJAX**.
- Una solicitud en vuelo se reintenta por HTTP con los mismos parámetros. El envío conserva `client_id`, de modo que una confirmación perdida no duplica el mensaje.
- Los rechazos de permisos o validación no se reintentan por AJAX para intentar eludir controles.
- La reconexión espera 1, 2, 4, 8, 16 y luego hasta 30 segundos entre intentos. Al volver, recupera los mensajes desde el último ID, sin recargar la página.
- Al cerrar sesión, se cancelan conexiones, reconexiones y solicitudes pendientes; no se reenvían escrituras pendientes con otra cuenta.

## Pruebas

```powershell
php tests/chat_test.php
node tests/chat_transport_test.cjs
node tests/chat_ws_integration.cjs
```

La primera usa una base temporal aislada, incluye las migraciones 018/019 y prueba credenciales de un uso, vencimiento, revocación y contrato compartido. La segunda simula cortes, timeouts, reconexión, errores y cierre de sesión en el transporte JavaScript. La tercera requiere Apache, MySQL y WebSocket activos; crea dos cuentas ficticias `wstest_*`, prueba entrega real, menciones, rechazo de origen/token, idempotencia HTTP/WebSocket y revocación. Retira sus cuentas y mensajes en `finally` mediante `chat_ws_cleanup.php`.

Para probar a mano, abrir una sala, detener con `stop`, enviar un mensaje por AJAX y volver a iniciar con `start`. El indicador debe volver a WebSocket sin recargar y el mensaje debe seguir apareciendo una sola vez.

Bibliotecas: [Ratchet](https://github.com/ratchetphp/Ratchet) y [ReactPHP EventLoop](https://reactphp.org/event-loop/).
