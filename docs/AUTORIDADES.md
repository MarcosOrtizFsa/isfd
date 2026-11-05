# Autoridades con saludo en video

Actualización multiplataforma: ejecutar también `database/migrations/016_autoridades_video_url_up.sql` en producción. El campo «Enlace del video de saludo» admite YouTube, Vimeo y `https://app.heygen.com/embeds/ID`. Conserva los códigos anteriores de YouTube. Las URLs se normalizan y se validan por proveedor, tanto al guardar como al generar el reproductor; no se acepta HTML arbitrario. El historial incluye la URL del video.

Instalación: ejecutar `php database/migrar_autoridades.php` desde la raíz, antes de desplegar las vistas. Requiere la migración institucional 014. La migración 015 crea fichas e historial; el migrador carga el listado anterior sin borrarlo y evita duplicar personas. Revisar también el paso 016 para los enlaces multiplataforma.

Administración: Módulos internos → Configuración de dominio → Autoridades. Acceso exclusivo de root. Se pueden agregar personas y editar nombre, apellido, cargo, profesión, presentación, año, orden, foto, video y visibilidad. Para un reemplazo se oculta la ficha saliente y se crea otra; el año no programa automáticamente su visibilidad.

Cada guardado registra una instantánea relacionada, usuario, motivo y fecha en `institucional_autoridades_historial`. El administrador permite consultar las revisiones. La revisión enviada evita sobrescribir cambios de otro formulario. No hay borrado destructivo: las personas se ocultan y sus fotos anteriores permanecen disponibles en el historial.

La portada muestra exclusivamente fichas visibles, ordenadas por orden e ID. Usa cinco columnas desde 1200 px, tres desde 768 px, dos desde 576 px y una en teléfonos pequeños. Sin foto muestra iniciales; sin video no muestra reproducción. El iframe se carga al pulsar y se elimina al cerrar el popup. Se admite YouTube normal, Shorts, youtu.be y códigos; el video debe permitir inserción. Si YouTube limita la reproducción, se ofrece el enlace externo.

Fotos: JPG, PNG o WebP, hasta 5 MB y 12 megapíxeles. El servidor verifica el contenido y recodifica a JPEG, con ancho máximo de 1000 px. Requiere GD y escritura en `uploads/autoridades/`.

El primer migrador tomó las autoridades existentes en aquel momento; ese número histórico no describe la nómina vigente. La nómina debe mantenerse desde el administrador con los datos validados por la institución. Revisar nombres y apellidos importados, completar fotos y saludos y ocultar las fichas que dejen de corresponder; no inventar identidades ni videos para completar tarjetas.

Pruebas: `php tests/autoridades_test.php`. Las pruebas de escritura usan tablas temporales de la conexión, sin modificar las fichas reales.
