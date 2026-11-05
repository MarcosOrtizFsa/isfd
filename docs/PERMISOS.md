# Accesos y permisos por usuario

Implementado el 5 de noviembre de 2026. Conserva PHP modular, Bootstrap y cargas jQuery/AJAX.

## Asignar permisos

Ingresar como **Root → Usuarios → icono de escudo/candado** de la cuenta. Se abre un panel lateral responsive. Marcar **Acceso** y luego las acciones permitidas; guardar. Quitar Acceso desmarca las acciones. Root tiene permisos implícitos, por lo que no muestra el botón de asignación.

| Opción | Alcance |
|---|---|
| Acceso | Ver el módulo y sus datos administrativos. |
| A | Crear registros. En Noticias también permite solicitar contenido al Agente IA. |
| B | Eliminar/archivar registros o retirar adjuntos según las operaciones disponibles. |
| M | Editar información. En Noticias incluye cargar adjuntos y guardar video. |
| E | Activar, suspender, publicar, restaurar o cambiar estados. |

Solo Root asigna permisos. Director, Administrador, Docente y Alumno requieren asignación explícita. Los roles no son paquetes automáticos de permisos. Control permite a un usuario no root gestionar únicamente cuentas de rol inferior y nunca la propia; tampoco puede asignar un rol igual o superior. Módulos internos y sus configuraciones continúan exclusivos de Root.

Acceso sin acciones permite consultar. Home es público: su casilla solo controla la entrada del menú administrativo. A/B/M/E no se aplican a Home. No se agregaron operaciones inexistentes: por ejemplo, Control conserva edición y cambio de estado, sin un borrado físico de usuarios.

## Reglas particulares

- Guardar un cambio de estado desde el editor también exige E, además de A/M. Una noticia nueva sin E se guarda pendiente; una oferta, en borrador/próximamente.
- Categorías utiliza los mismos permisos de Ofertas.
- Sincronizar solicitudes de NEURIA requiere A+M+E porque puede importar y actualizar contenido y estados. Ver el indicador de conexión solo requiere Acceso a Noticias. Los contratos API y las credenciales no se modifican.
- Contactos: atender/responder/reabrir requiere E; guardar notas de preinscripción requiere M, y modificar su estado requiere además E.
- Chat, dentro de Contactos: advertir/descartar reporte requiere M; retirar mensaje, B; suspender/expulsar/rehabilitar, E. Leer participantes y reportes requiere Acceso.
- Los cambios afectan la siguiente petición del usuario, incluso con sesión abierta. El menú ya dibujado se actualiza al recargar. Una cuenta desactivada no puede seguir usando su sesión.

## Implementación

`lib/permisos.php` centraliza `PermisosModulos`, `app_can()`, `app_require_permission()`, `app_guard_state()` y `app_permission_attrs()`. Todos los endpoints administrativos validan acceso; cada escritura vuelve a validar su acción y CSRF. Los estados se comparan contra la base, no contra valores ocultos enviados por el navegador.

`lib/app.php` revalida identidad, rol, modo y estado desde `system_03_usuarios`. Solo modo 0 tiene excepción global. Se utiliza caché por petición para no consultar permisos por cada botón del listado; no se conserva una autorización obsoleta entre peticiones.

Se reutiliza `system_02_permisos`, relacionada por `rela_system_03` y `rela_system_01`. La migración 021 agrega `system_02_acceso` con valor inicial 0. Se mantienen las columnas históricas; V/S/D/I/C no forman parte de este formulario. Guardar reemplaza las asignaciones administradas dentro de una transacción, bloquea la cuenta y consolida posibles duplicados. La lectura toma la asignación más reciente. El guardado registra `USUARIO_PERMISOS` en la auditoría.

Los antiguos controladores de Control, Ofertas y Root fueron retirados del acceso HTTP (410) porque los reemplazan los archivos `app_*`. Sus operaciones anteriores no deben utilizarse para gestionar permisos.

Para incorporar un nuevo módulo: registrarlo en BD, agregarlo a la lista de módulos admitidos y al formulario/servicio de permisos, proteger cada entrada y acción en PHP, adaptar los botones y ampliar las pruebas. No confiar solamente en ocultar controles.

## Despliegue

1. Respaldar BD y archivos.
2. En la base de producción seleccionada, ejecutar **`database/migrations/021_permisos_modulos_up.sql`**. Es repetible y no contiene DEFINER ni nombre fijo de base.
3. Subir `lib/app.php`, `lib/permisos.php`, `sistema/app_index.php`, los módulos Control, Ofertas, Noticias y Contactos actualizados, los controladores antiguos retirados de Root, `sistema/php/abm.php` y `chat/admin.php`. Incluir JavaScript de Noticias.
4. Ingresar como Root y asignar accesos explícitos antes de entregar cuentas al equipo. La migración no concede permisos masivamente: las cuentas no root existentes comienzan sin acceso hasta asignarlo.
5. Recargar con Ctrl+F5 y probar una cuenta restringida.

La migración ya se ejecutó en localhost. Producción requiere el paso 2. Los archivos privados de conexión y credenciales no se reemplazan.

## Verificación

`C:\xampp\php\php.exe tests/permisos_test.php` prueba por HTTP cuentas temporales, Root, Director, asignación, acceso directo, ABME, escalación de rol, publicación sin E, revocación y desactivación con sesión abierta. No llama a NEURIA. Elimina sus cuentas/noticia al finalizar; el guardado efectuado por Root deja su evento de auditoría.

`tests/noticias_adjuntos_test.php` verifica que las restricciones no alteren el flujo autorizado de texto, imágenes, documentos y video. Ejecutar estas pruebas únicamente en desarrollo.
