# Registro de acciones administrativas

Disponible en **Módulos internos → Registro de acciones**, exclusivamente para Root. Se reutiliza `system_05_logistica`: no hay que crear otra tabla ni reimportar la base.

## Consulta

La pantalla ofrece filtros por fecha inicial/final, administrador, módulo, tipo A/B/M/E y búsqueda textual. Inicialmente consulta los últimos 30 días. Para consultar todo el historial se pueden vaciar las fechas. Se muestran 50 registros por página, ordenados por fecha, hora e identificador descendentes. El detalle se expande en la misma fila.

Se muestran ID de registro, fecha/hora, administrador y su ID, rol registrado, módulo, acción, detalle y resultado. Los nombres visibles de cuentas, roles y módulos son los actuales; los identificadores históricos se conservan aunque un registro relacionado ya no exista. No hay botones ni endpoints para modificar o eliminar el historial.

## Eventos

Los eventos nuevos usan `A:`, `B:`, `M:` o `E:` como prefijo de `system_05_accion`; el nombre completo se mantiene dentro de los 22 caracteres disponibles. Los registros previos conservan su descripción original: aparecen en “Todas” y por búsqueda, pero no se reclasifican automáticamente.

| Área | Registro |
|---|---|
| Control | Alta/edición de usuarios, cambios de estado y asignaciones de permisos. |
| Ofertas | Alta/edición, publicación, archivo/restauración y cambios de estado de convocatoria. |
| Categorías | Alta/edición, baja y activación/desactivación. |
| Noticias | Alta/edición, estado, archivo/restauración, carga/retiro de adjuntos y video. |
| NEURIA | Solicitud creada, solicitud ya existente y resumen de sincronización (actualizadas, importadas, errores). |
| Contactos | Atención, respuesta, reapertura y seguimiento/estado de preinscripciones. |
| Chat | Acción de moderación con ID de participante y reporte; conserva además `chat_auditoria`. |
| Módulos internos | Cambios en módulos, dominio, contenido institucional, autoridades y configuración de NEURIA. |
| Sesiones | Inicio correcto y cierre de sesión. |

Un guardado que cambia además un estado puede generar un evento M y otro E. Los adjuntos se clasifican por la operación realizada; cargar un archivo es A en la bitácora aunque el permiso funcional requerido para editar adjuntos sea M. La bitácora describe operaciones, no sustituye el control de permisos.

Se registra después de una operación aceptada. Un rechazo por permisos/validación no se registra como cambio exitoso. Las sincronizaciones se registran como ejecución resumida, no como una auditoría individual de todos los contenidos remotos. Los trabajos automáticos sin sesión administrativa no se atribuyen a un administrador.

No se vuelcan formularios completos, contraseñas, tokens ni respuestas privadas de la API. Se registra si la contraseña fue actualizada, nunca su valor. Los historiales especializados de contenido institucional, autoridades, chat y preinscripciones siguen conservando su información propia.

## Implementación y mantenimiento

- `lib/app.php`: función común `app_log()`, identidad de sesión revalidada y resolución del módulo mediante la ruta del servidor cuando no se pasa ID explícito.
- `lib/auditoria.php`: lectura paginada, validación de fechas y filtros con consultas preparadas.
- `sistema/modulos/root/php/app_auditoria.php`: control Root en servidor, salida escapada y recarga AJAX.
- Los endpoints llaman al registro tras la operación. La auditoría y el cambio de negocio no constituyen una transacción única en todos los módulos: una indisponibilidad de BD al registrar requiere revisión técnica del cambio antes de reintentar.
- Es una bitácora operativa, no un registro criptográfico inalterable. Una cuenta con escritura directa en BD puede modificarla. Conservar respaldos y restringir el acceso a la base.
- No se implementa purga automática. Revisar crecimiento e índices según el volumen real antes de definir una política de archivo.

## Despliegue y pruebas

Subir `lib/app.php`, `lib/auditoria.php`, `chat/admin.php`, `sistema/php/app_logout.php` y los módulos Control, Ofertas, Noticias, Contactos y Root actualizados. No reemplazar la conexión privada ni credenciales de producción. No requiere nueva migración.

`tests/permisos_test.php` verifica eventos A/B/M/E con operaciones temporales, filtros, paginación, escape HTML y acceso denegado a Director. Limpia las cuentas y registros de prueba; las asignaciones efectuadas por Root dejan su evento. `tests/noticias_adjuntos_test.php` verifica que siga funcionando el flujo de texto y adjuntos. Ejecutar únicamente en desarrollo.
