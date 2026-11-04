# Chat institucional

Aplicación en `/chat/`, integrada al proyecto PHP/MySQL. No utiliza la API de NEURIA ni cambia el login del administrador. Las cuentas públicas usan `chat_usuarios` y la clave de sesión `chat_user_id`; no crean usuarios de mipanel. Se reutilizan conexión, escape HTML, sesiones y CSRF de `lib/app.php`.

## Instalación y despliegue

1. Respaldar la base de producción.
2. Importar `database/migrations/018_chat_institucional_up.sql` y luego `019_chat_websocket_up.sql` en **isfdcom_data_base**. Son repetibles, conservan registros y no necesitan procedimientos ni DEFINER. Crean tablas, cinco salas, 512 avatares y credenciales temporales para WebSocket.
3. Subir la carpeta `chat/` completa, incluyendo `chat/lib/.htaccess`.
4. Actualizar `lib/portal_institucional.php`, `scripts/portal.js`, `css/portal.css` y `sistema/modulos/root/php/app_home.php`.
5. Conservar los archivos privados y la conexión de producción. AJAX no requiere procesos adicionales. Para activar WebSocket, seguir `CHAT_WEBSOCKET.md`; mientras no se configure la URL WSS de producción, se usará AJAX.
6. Abrir `/chat/`, registrar dos cuentas, elegir avatares, entrar a la misma sala y probar mensajes, respuestas y menciones. Revisar desde root **Modulos internos → Administrar chat institucional**.

En local: `C:\xampp\php\php.exe database/migrar_chat.php`. Acceso: `http://localhost/isfd.com.ar/chat/`. PHP 8.1+, mysqli/mysqlnd, mbstring, sesiones y MySQL/MariaDB con InnoDB; mismas dependencias del proyecto. jQuery y el Bootstrap del panel administrativo se cargan desde los CDN usados por el sitio.

El botón flotante antes destinado a WhatsApp abre una ventana con nombre propio y reutiliza esa ventana. Si el navegador no permite ventanas, se conserva el enlace en otra pestaña. Los contactos de WhatsApp del pie no cambian. El botón mantiene su posición actual, abajo a la derecha, según lo acordado.

## Flujo y estructura

- Registro → aceptación de reglas → avatar → salas → conversación.
- Usuarios únicos, contraseña con `password_hash`, email privado. Se admite login con email o usuario. El email no se verifica en esta versión y no hay recuperación automática de contraseña: no se envía correo.
- Cinco salas sembradas por la migración. Sus nombres y descripciones están en `chat_salas`; no hay ABM de salas en esta primera versión.
- `chat_mensajes`: texto plano, respuesta dentro de la misma sala, clave de envío única por usuario. Ante una respuesta de red perdida, el cliente conserva la clave y un reintento no duplica el mensaje.
- `chat_notificaciones`: menciones y respuestas, una por destinatario/mensaje; lectura autorizada por propietario.
- `chat_presencias`: actividad en los últimos 60 segundos. Las pestañas ocultas dejan de consultar. El conteo es aproximado y representa una sala por cuenta, no sesiones individuales.
- Historial paginado por ID, 50 mensajes por consulta. Sonido opcional habilitado por interacción del usuario.
- `chat_avatares`: SVG propios generados con combinaciones de color, forma, marca y número. La reserva exclusiva se garantiza con índice UNIQUE en el usuario, incluso ante selecciones simultáneas. Cada cuenta conserva su avatar al desconectarse y libera el anterior al cambiarlo. Hay 512 avatares; ampliar el generador y catálogo juntos si se necesitan más cuentas con avatar.

## Actualización y capacidad

WebSocket autenticado como transporte principal cuando está configurado y disponible. Las operaciones usan el mismo servicio que AJAX. Los cambios generan avisos y consultas incrementales por el socket; cada 20 segundos se renueva la presencia y se recuperan posibles brechas, también por WebSocket. Al fallar la conexión, AJAX consulta cada 3 segundos mientras la pestaña está visible. La reconexión tiene espera progresiva hasta 30 segundos y detiene el polling HTTP al recuperarse. No hay cron necesario. No se ha certificado capacidad para una cantidad concreta de participantes; probar la concurrencia esperada en el hosting.

## Moderación y seguridad

Los textos se muestran como texto, nunca HTML. Cada escritura exige CSRF y cuenta habilitada. La sesión se regenera al autenticar. Las claves del panel y del chat están separadas; cerrar el chat no cierra mipanel. El administrador del chat debe estar autenticado como root.

Controles locales conservadores para amenazas directas y agresiones explícitas dirigidas mediante @usuario; expresiones educativas/citadas quedan fuera de estas reglas. **No es un clasificador de IA ni garantiza reconocer contexto, discriminación o acoso**: las evasiones y los falsos positivos requieren reportes y revisión humana. No se mandan conversaciones a proveedores externos.

- Detección: rechaza el mensaje, registra su texto y motivo, advierte.
- Tres detecciones no descartadas en una hora: pausa automática de 10 minutos, revisable.
- Spam: intervalo mínimo de 2 segundos y bloqueo de mensajes idénticos durante 60 segundos; máximo 30 intentos de envío por minuto.
- Reportes: hasta 10 cada 10 minutos; un reporte por mensaje/denunciante.
- Autenticación: 8 intentos por nombre de cuenta cada 15 minutos y 300 por IP, para admitir Wi-Fi compartido. Registro: 120 por IP/hora. Los contadores son atómicos y persisten en BD. Ajustar esos límites al contexto de la exposición.
- Root puede advertir, retirar mensajes, pausar 24 horas, expulsar, rehabilitar o descartar reportes. Toda decisión guarda administrador, motivo y fecha en `chat_auditoria`. Las advertencias se muestran en el perfil del participante. La expulsión permanente nunca es automática.

La moderación oculta mensajes conservándolos para auditoría. Las vistas abiertas reciben los IDs retirados en la siguiente consulta. Los reportes incluyen contexto previo para revisión. No se publica el correo ni las credenciales de los participantes.

Los mensajes, cuentas, reportes e historial se conservan sin purga automática en esta primera versión. Antes de un uso permanente, definir institucionalmente plazos de conservación, recuperación de cuentas y procedimiento de revisión de sanciones. Para la exposición conviene disponer de un moderador durante toda la actividad.

## Pruebas

`C:\xampp\php\php.exe tests/chat_test.php` crea una base aleatoria `isfd_chat_test_*`, prueba migración repetible, cuentas, contraseñas, avatares exclusivos, presencia, mensajes idempotentes, respuestas, cursores, menciones, moderación, sanciones, auditoría y límites; finalmente elimina únicamente esa base de prueba. Requiere permisos locales CREATE/DROP DATABASE. No ejecutarlo en producción.
