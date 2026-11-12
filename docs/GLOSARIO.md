# Glosario institucional

En la portada, **El Instituto → Glosario** abre una ventana modal responsive con palabras y descripciones en orden alfabético. Solo se incluyen registros visibles y no eliminados; sin términos se muestra un aviso.

Root administra cada entrada desde **Módulos internos → Configuración de dominio → Glosario institucional**, debajo de Autoridades. Puede agregar, editar, ocultar y eliminar. Palabra y descripción son obligatorias; se admite texto plano con saltos de línea. La eliminación es lógica. Las acciones se registran en auditoría y una revisión evita sobrescribir cambios concurrentes.

## Despliegue

1. Ejecutar database/migrations/022_glosario_up.sql en la base de producción seleccionada. Es repetible, no tiene DEFINER ni credenciales y conserva el contenido existente.
2. Subir lib/glosario.php.
3. Subir los tres archivos sistema/modulos/root/php/app_glosario*.php y app_dominio_config.php.
4. Subir sistema/modulos/home/php/home.php y sistema/modulos/home/templates/portal_home.html.
5. Cargar las palabras reales desde el panel. No se insertan definiciones de demostración.

El guardado usa sesión Root, CSRF, consultas preparadas y transacción. Los valores se escapan al mostrarlos. Si falta la tabla, la portada continúa funcionando y el glosario informa que no está disponible.

## Carga inicial de contenidos de IA

La lista aportada por el responsable contiene 94 términos. Se conserva el texto de las definiciones; Perceptrón y Pipeline se separaron porque compartían una línea, y se incluyó K-means aunque no tenía viñeta. Las letras de agrupación no se guardan como entradas: el listado público ordena por palabra.

- Datos de referencia: [glosario_ia.json](../database/seeds/glosario_ia.json).
- Carga para phpMyAdmin: [glosario_ia.sql](../database/seeds/glosario_ia.sql), después de la migración 022.

El SQL inserta los términos visibles y elimina únicamente la prueba con palabra AUX-IA y descripción «Marca del equipo de trabajo.». Puede repetirse: compara por palabra y no sobrescribe entradas existentes, incluso si se editaron, ocultaron o eliminaron lógicamente desde el panel. No cambia la estructura de la tabla.
