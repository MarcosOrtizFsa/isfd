# Importación y despliegue en producción

Actualizado: 4 de noviembre de 2026; código de referencia `50de00d`.

## Antes de importar

Seleccionar la base **isfdcom_data_base** en phpMyAdmin. La base local de desarrollo es **isfd_formosa**. No cambiar estos nombres dentro de un volcado sin revisar también vistas y dependencias.

Respaldar BD y archivos cargados, conservar la configuración privada y registrar qué migraciones están aplicadas. Las migraciones de este repositorio amplían un sistema existente: no incluyen una instalación completa de las tablas históricas `system_*`. Una instalación desde cero necesita además ese esquema base validado.

No reimportar toda la BD local sobre producción para actualizar funciones. Las semillas pueden agregar datos demostrativos o cambiar contenidos. No asumir que todos los SQL son repetibles: inspeccionar cada `ALTER` y comprobar previamente columnas/tablas existentes.

## Orden y propósito de los SQL

Los archivos `up` están en `database/migrations/`, salvo la base inicial indicada. Ejecutar solo los faltantes y resolver cada error antes de continuar con sus dependencias.

| Paso | Archivo | Propósito |
|---|---|---|
| Inicial | `database/2026_10_17_portal_academico.sql` | Catálogo anterior, consultas y bandeja transaccional. |
| 002 | `002_catalogo_ofertas_up.sql` | Catálogo normalizado y migración de relaciones. |
| 003 | `003_ofertas_superiores_demo_up.sql` | Ofertas de demostración; revisar su conveniencia y contenido. |
| 004 | `004_modulo_ofertas_admin_up.sql` | Registro/permisos de Ofertas. |
| 005 | `005_noticias_admin_up.sql` | Noticias, archivos y módulo. |
| 006 | `006_vista_catalogo_phpmyadmin_up.sql` | Vistas compatibles con el recorrido de phpMyAdmin. |
| 007 | `007_catalogo_integridad_manual_up.sql` | Triggers y procedimientos; paso manual sujeto a permisos del hosting. |
| 008 | `008_neuria_v1_sync_up.sql` | Campos y cursores de sincronización API v1. |
| 009 | `009_neuria_solicitudes_ia_up.sql` | Solicitudes IA y seguimiento remoto. |
| 010 | `010_integraciones_credenciales_up.sql` | Credenciales cifradas y diagnóstico de integración. |
| 011 | `011_ofertas_multimedia_up.sql` | Multimedia de ofertas. |
| 012 | `012_ofertas_plan_estudios_up.sql` | Plan de estudios. |
| 013 | `013_modulo_contactos_up.sql` | Módulo Contactos y seguimiento de consultas. |
| 014 | `014_contenido_institucional_up.sql` | Secciones, versiones y valores institucionales. |
| 015 | `015_autoridades_up.sql` | Autoridades e historial. |
| 016 | `016_autoridades_video_url_up.sql` | Enlaces de saludo de distintas plataformas. |
| 017 | `017_videos_institucionales_up.sql` | Soporte de videos institucionales versionados. |
| 018 | `018_chat_institucional_up.sql` | Tablas de chat, salas y avatares iniciales. |
| 019 | `019_chat_websocket_up.sql` | Tokens y revisión de transporte WebSocket. |
| 020 | `020_preinscripciones_up.sql` | Preinscripciones e historial de gestión. |
| 021 | `021_permisos_modulos_up.sql` | Acceso explícito por usuario; ejecutar antes de subir el nuevo control de permisos. Root debe asignar accesos a las cuentas no root. |

El paso 007 puede dejarse para el final: una restricción de rutinas no debe impedir instalar el resto de las tablas. No declarar `DEFINER=root@localhost`; crear los objetos con el usuario de producción. Si faltan permisos de triggers/rutinas, solicitarlos al proveedor y ejecutar después solo ese paso.

Las tablas de contenido institucional no garantizan por sí solas la carga inicial de todos los textos: revisar las herramientas y semillas de `migrar_institucional.php`, `migrar_autoridades.php`, `seeds/` y la guía de su dominio. `ACTUALIZACION_PRODUCCION_011_017.sql` es un paquete histórico con contenido preparado para una actualización concreta; **no combinarlo ciegamente con 011–017 ni reutilizarlo para sobrescribir contenido vigente**.

## Actualización desde el código 964ca0f

Si la BD de ese entorno ya tenía aplicados los pasos anteriores, las nuevas migraciones son únicamente:

1. `018_chat_institucional_up.sql`.
2. `019_chat_websocket_up.sql`.
3. `020_preinscripciones_up.sql`.

El ABM de categorías, su filtro público, el indicador de NEURIA y la separación de adjuntos de Noticias no necesitan SQL adicional.

Archivos/carpetas cambiados en el recorrido hasta `50de00d`:

- `chat/`, incluyendo archivos ocultos de protección; no copiar PID ni logs locales.
- `preinscripcion/`.
- `sistema/modulos/contactos/`, `home/`, `noticias/`, `ofertas/`, `root/`.
- `css/portal.css`, `scripts/portal.js`, `lib/portal_institucional.php`, `sistema/api/captcha.php`.

Si el portal ya recibió la preinscripción y solo falta el último cambio editorial, subir `sistema/modulos/noticias/php/` y `sistema/modulos/noticias/js/`. No ejecutar SQL por ese cambio.

La carga de carpetas debe reemplazar código conservando los archivos de usuarios. No borrar `uploads/` para actualizar. Los tests, documentos, scripts de Windows y volcados no son necesarios para servir el portal.

## Configuración y procesos

- Conservar `lib/mysql_conect.php` y `.env` de producción. No sobreescribirlos con copias de localhost.
- No subir `config_private/neuria.local.php` ni tokens en ejemplos. Mantener respaldo privado de `ISFD_CREDENTIAL_KEY` junto con el procedimiento de recuperación de credenciales cifradas.
- Verificar `mysqli/mysqlnd`, cURL, mbstring, OpenSSL, DOM, iconv, GD, fileinfo y ZipArchive, y los límites de uploads del PHP web. CLI puede usar otro `php.ini`.
- AJAX del chat funciona sin proceso WebSocket. Dejar `CHAT_WS_URL=` mientras no se configure WSS.
- WebSocket necesita Composer, proceso PHP persistente, supervisor y proxy WSS/TLS. Seguir [CHAT_WEBSOCKET.md](../docs/CHAT_WEBSOCKET.md). Habilitar SSH no equivale a tener esas capacidades.
- Las tareas de NEURIA requieren registrar el cron de forma explícita; copiar el script no lo programa.

## Verificación posterior

1. Abrir portada y comprobar imágenes, filtros y enlaces.
2. Entrar a mipanel, abrir Noticias, comprobar NEURIA y editar una nota de prueba controlada.
3. Guardar texto y adjuntar imagen/documento/video; volver a guardar texto y comprobar que se conservan.
4. Revisar una oferta abierta y otra cerrada: el botón destacado de preinscripción corresponde solo a la abierta.
5. Comprobar captcha y formulario propio cuando la URL externa esté vacía; revisar recepción en Contactos con datos de prueba acordados.
6. Abrir chat, comprobar registro/login y transporte; revisar moderación desde Contactos con root.
7. Revisar logs PHP/Apache y errores AJAX. Recargar con Ctrl+F5 si se actualizó CSS/JS.

## Incidencias y recuperación

- Error 1227: revisar DEFINER ajeno o permisos de rutinas; no resolverlo reimportando todo.
- Mezcla de collations: revisar las columnas/expresiones comparadas y el SQL específico; no convertir indiscriminadamente toda la base sin respaldo.
- Tabla faltante: identificar la migración que la crea; no asumir que una importación detenida terminó las siguientes sentencias.
- Clave foránea faltante en un `ALTER`: comprobar el nombre real/estado previo de la estructura.
- Error de carga: verificar permisos de `uploads`, MIME y límites PHP/servidor.

Registrar fecha, respaldo, commit, SQL ejecutado, resultado y verificación por cada despliegue. Si se revierte código, comprobar compatibilidad con la estructura actual; no usar scripts `down` sobre datos de producción de manera automática.

## Permisos por usuario (021)

Ver [despliegue de permisos](../docs/PERMISOS.md#despliegue). No requiere reimportar tablas completas. Después de ejecutar 021 y actualizar archivos, Root asigna permisos desde Usuarios → escudo/candado.
