# Preinscripciones institucionales

## Uso y despliegue

- Importar `database/migrations/020_preinscripciones_up.sql` en la base de producción seleccionada. Es repetible, no incluye credenciales, DEFINER ni cambios a registros existentes.
- Subir `preinscripcion/` y los archivos modificados de `lib/portal_institucional.php`, `scripts/portal.js`, `sistema/api/captcha.php`, las vistas de Home y los módulos Contactos y Configuración de dominio.
- En Configuración de dominio → Enlaces e imágenes institucionales, dejar vacía la URL de preinscripción para utilizar el formulario propio. Una URL configurada mantiene el formulario externo.
- En Ofertas, publicar la carrera y seleccionar **Inscripción abierta**. No se crean estados nuevos ni se modifican las fechas de convocatoria automáticamente.
- Contactos → Preinscripciones permite consultar el resumen anual por carrera, filtrar, paginar y registrar estados/notas internas.

## Flujo

Desde la portada se selecciona la carrera. Desde su detalle se preselecciona mediante `?oferta=ID`. El servidor vuelve a validar que esté publicada, no eliminada, con inscripción abierta y dentro de una rama activa del catálogo al enviar.

El formulario solicita nombre, apellido, DNI argentino (7 u 8 dígitos sin puntos), correo y celular (10 dígitos). Usa sesión, CSRF, captcha propio, campo trampa y límite de cinco altas por IP/hora. No requiere cuenta del chat. Una solicitud no equivale a una vacante confirmada.

Se guarda una solicitud por DNI, carrera y **año de recepción**. Un reenvío conserva los datos originales, sin exponerlos al solicitante. El año no representa una convocatoria ni el ciclo de cursada. Reaperturas, correcciones o cancelaciones se coordinan con la institución; no se reemplazan desde el formulario público.

`portal_preinscripciones` conserva el título de la oferta al recibirla y el consentimiento; `portal_preinscripcion_historial` conserva los cambios de estado y las notas con su administrador. No se envían correos automáticos en esta etapa. Los permisos administrativos son los mismos de Contactos (modo 0).

Validación: `C:/xampp/php/php.exe tests/preinscripciones_test.php` crea y elimina una base efímera, sin modificar los datos reales.
