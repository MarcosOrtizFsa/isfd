# Videos de Historia institucional

En Configuración de dominio, debajo de los textos de Historia institucional, la sección «Historia institucional · Videos» permite agregar título y URL, ordenar con flechas, ocultar y quitar videos. Guardar publica la lista completa; se exige año y motivo y se conserva la versión previa en el historial institucional.

La portada mantiene un único iframe. Anterior y Siguiente recorren circularmente los videos visibles; al cambiar se destruye el reproductor anterior y se detiene su audio. No hay reproducción ni avance automáticos. Con un solo video no hay flechas; sin videos se conserva la imagen institucional. Se admite YouTube, HeyGen y Vimeo mediante el normalizador compartido con autoridades.

Producción: importar `database/migrations/017_videos_institucionales_up.sql` antes de desplegar. No requiere nuevas tablas: la sección y sus revisiones utilizan institucional_secciones, institucional_versiones e institucional_valores. La lista inicial está vacía; no se reutilizan saludos personales como videos institucionales.

Verificación: `php tests/videos_institucionales_test.php`.
