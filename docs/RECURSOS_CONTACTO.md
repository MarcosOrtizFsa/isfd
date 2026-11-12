# Recursos de contacto: QR y enlaces

La portada muestra un pasador debajo de los datos de contacto. Cada tarjeta tiene tema, imagen y botón para abrir una URL en una ventana separada; si el navegador bloquea el popup, el enlace funciona en otra pestaña. Escanear un QR abre el contenido codificado en la imagen, que debe coincidir con el enlace configurado.

Desde **Módulos internos → Configuración de dominio → Recursos de contacto: QR y enlaces**, Root puede agregar, editar, reemplazar la imagen, ordenar, ocultar y eliminar recursos. Se admite una imagen por recurso (PNG/JPG/WebP, hasta 5 MB y 12 megapíxeles) con vista previa. El tema y la URL web absoluta son obligatorios.

La imagen se reencodifica como PNG sin reducir dimensiones ni recortarla. El sistema no genera ni interpreta códigos QR. Preferir PNG con margen blanco y comprobar el escaneo con un celular antes de publicar.

El pasador no avanza automáticamente. Con un solo recurso no muestra flechas; sin recursos visibles el bloque desaparece. Los enlaces no se cargan en un iframe ni se consultan desde el servidor.

## Datos y controles

- Tabla institucional_recursos_contacto, migración 023.
- Sesión exclusiva de Root y CSRF en guardados.
- Validación del MIME real y de URLs HTTP/HTTPS sin credenciales.
- Eliminación lógica, control de revisión concurrente y auditoría ABME en transacción.
- Si el guardado falla, se elimina solo el archivo nuevo. Imágenes reemplazadas o de recursos eliminados se conservan; no existe limpieza automática.
- La ausencia de la tabla no interrumpe la portada.

## Producción

1. Ejecutar database/migrations/023_recursos_contacto_up.sql en la base de producción. Es repetible y no incluye DEFINER.
2. Subir lib/recursos_contacto.php.
3. Subir sistema/modulos/root/php/app_recursos_contacto.php, app_recurso_contacto_form.php, app_recurso_contacto_action.php y app_dominio_config.php.
4. Subir sistema/modulos/home/php/home.php y sistema/modulos/home/templates/portal_home.html.
5. Subir css/portal.css, scripts/portal.js y sistema/templates/app_index.html.
6. Si cargaste imágenes localmente, copiar uploads/recursos_contacto/ y migrar los registros correspondientes, o cargar los recursos desde el panel de producción. La migración solo crea la tabla.

El directorio uploads/recursos_contacto se crea al subir la primera imagen; PHP necesita permiso de escritura en uploads. Estos archivos de integración también incluyen el glosario de la etapa anterior: desplegar primero la migración 022 y sus archivos si todavía no están en producción.

## Verificación local

Ejecutar tests/recursos_contacto_test.php con PHP CLI y Apache activo. Utiliza recursos y archivos temporales que elimina al finalizar. No envía solicitudes a los destinos externos.
