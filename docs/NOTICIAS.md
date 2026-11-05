# Módulo Noticias y flujo con NEURIA

Actualizado: 4 de noviembre de 2026; incluye el editor separado de adjuntos de `50de00d`.

## Fuente editorial y publicación

La portada lee noticias activas de la BD local, respeta su fecha de publicación y excluye bajas lógicas. No consulta NEURIA para construir el listado público. La disponibilidad de la API no debe bloquear la lectura de noticias ya publicadas.

La integración vigente utiliza `NeuriaV1Cliente`, `NeuriaSincronizador` y `NeuriaSolicitudes`. Los contenidos remotos se importan pendientes, identificados por UUID; los campos originales remotos se conservan aparte de la versión editorial local. El flujo antiguo de revisión mediante `neuria_id` y `NeuriaCliente` se conserva por compatibilidad, pero no describe el listado actual de Noticias.

```text
NEURIA / producción IA → sincronización → noticia local pendiente
                                          ↓ revisión humana
                                       activa → portada
                                          ↕
                                      suspendida
```

Una importación no autoriza publicación automática. Los eventos remotos de suspensión/retiro bloquean la publicación; la reactivación vuelve a revisión. Ver [NEURIA_V1.md](NEURIA_V1.md).

## Editor en dos etapas

1. `app_form.php` presenta título, bajada, texto completo, volanta, sección, fecha/hora y estado.
2. `app_action.php` guarda solo el contenido editorial y devuelve el ID local.
3. `js/editor.js` mantiene abierto el editor y habilita `#news-attachments-panel`.
4. `app_adjuntos.php` presenta paneles independientes para imágenes, documentos y video.
5. `app_adjuntos_action.php` procesa cada operación; `js/adjuntos.js` recarga únicamente el panel de adjuntos.

No es necesario guardar el texto nuevamente después de adjuntar. Guardar texto no borra el video ni los archivos. Cambiar adjuntos conserva las modificaciones editoriales aún no guardadas; cerrar el editor sí puede perder esos cambios.

Los contenidos de NEURIA se editan/guardan como texto plano mediante `neuria_texto_plano_editor()`, conservando párrafos. Las notas locales admiten HTML editorial básico validado por `neuria_sanitizar_html()`. No insertar HTML del proveedor sin validación.

## Archivos y video

- Imágenes: JPEG, PNG, WebP y GIF; hasta 8 MB por archivo. Vista previa local antes de subir y miniatura del archivo guardado.
- Documentos: PDF, DOC, DOCX, XLS y XLSX; hasta 15 MB. Enlaces independientes para abrir/descargar.
- Máximo diez archivos por petición. También rigen los límites PHP del servidor y del proxy.
- Validación de MIME real con `fileinfo`; reconocimiento de ZIP Office con `ZipArchive` cuando corresponde. No confiar en la extensión declarada por el navegador.
- Ruta `uploads/noticias/YYYY/MM`, nombres aleatorios, metadatos en `noticia_archivos`.
- Los nuevos archivos se agregan al final del orden. La primera imagen disponible es la portada de la nota.
- La transacción y la limpieza de archivos movidos evitan conservar archivos parciales cuando falla una carga.
- Retirar un archivo marca `deleted_at`. No existe restauración individual en la interfaz ni purga automática de binarios.
- Un video YouTube por noticia, conservado en `noticias.youtube_codigo`. Admite URL/código validado, vista previa, guardar y quitar por separado. No admite iframe arbitrario ni otros proveedores en este panel.

La protección del directorio de noticias debe conservar su `.htaccess` específico al desplegar. Esta separación de paneles utiliza las tablas existentes y **no requiere una migración nueva**.

## Endpoints administrativos

Rutas relativas a `sistema/modulos/noticias/php/`:

| Archivo | Operaciones |
|---|---|
| `app_action.php` | Guardar texto, cambiar estado, eliminar/restaurar noticia. |
| `app_adjuntos.php` | Fragmento HTML de adjuntos de una noticia guardada. |
| `app_adjuntos_action.php` | `upload`, `file_remove`, `video_save`, `video_remove`; recibe `noticia_id`. |
| `app_neuria_action.php` | Solicitar y sincronizar trabajos de NEURIA. |
| `app_neuria_status.php` | Diagnóstico breve autenticado de la conexión. |

Autorización: modos 0, 1 y 2; mutaciones con CSRF. El retiro de un archivo verifica pertenencia a la noticia. No pueden adjuntarse archivos a una nota inexistente o eliminada. `adjuntos_soporte.php` concentra normalización de YouTube, validación de archivos y persistencia de uploads.

## Indicador de conexión

Se ejecuta por AJAX al entrar a Noticias y mediante Volver a comprobar. Consulta `/api/v1/me` con timeout máximo de cinco segundos, sin reintentos, liberando la sesión durante la espera. Muestra conectada, sin configurar, desactivada o conexión no verificada. No publica tokens, respuesta cruda ni URL privada; no sustituye la sincronización editorial.

## Pruebas y despliegue

- `php tests/noticias_test.php`: estructura e integración local.
- `php tests/noticias_adjuntos_test.php`: prueba HTTP contra localhost, crea una nota pendiente identificada, comprueba guardado separado, PNG/PDF/DOCX/XLSX, video, vistas previas y conservación de adjuntos; retira la nota y sus archivos en `finally`. No usar en producción. La auditoría de las acciones puede conservar trazas de la prueba.
- `node --check sistema/modulos/noticias/js/editor.js` y `adjuntos.js`.

Para desplegar el cambio de adjuntos: subir `sistema/modulos/noticias/php/` y `sistema/modulos/noticias/js/`. Conservar uploads y configuración privada. Recargar el navegador después de actualizar JavaScript.
