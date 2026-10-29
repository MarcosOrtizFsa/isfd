# Revisión de ofertas del sitio de referencia

Fuente: https://galeanosantiago.github.io/FaC/carreras.html?id=1 y su archivo assets/js/carreras.js. Revisión: 29/10/2026. Los identificadores válidos son del 1 al 6; los restantes vuelven a mostrar Inglés.

| ID fuente | Oferta | Acción |
|---|---|---|
| 1 | Profesorado en Inglés | Completar existente; conservar imagen propia |
| 2 | Tecnicatura Superior en Desarrollo de Software | Completar existente |
| 3 | Tecnicatura Superior en Administración de Sistemas y Redes | Crear en borrador |
| 4 | Tecnicatura Superior en Administración de Empresas con Orientación a PyMES | Crear en borrador |
| 5 | Profesorado en Economía | Completar el Profesorado de Educación Secundaria en Economía, conservando su denominación y estado |
| 6 | Profesorado en Ciencias de la Educación | Crear en borrador |

Se adaptaron las descripciones y los resúmenes. «A quién está dirigido» es una síntesis editorial orientativa de las descripciones, no un requisito oficial. Los plazos de noviembre a enero de la web no tienen año: no se importaron como convocatorias ni se habilitaron inscripciones. No hay requisitos documentales, denominaciones oficiales de títulos ni cargas horarias totales verificables: quedan por confirmar.

La modalidad de las nuevas ofertas se cargó provisionalmente como presencial, siguiendo el catálogo local y la referencia a horario e institución. Confirmar antes de publicar. Las ofertas existentes conservan modalidad, slugs, convocatorias y estados. Las otras ofertas demostrativas del catálogo no se modificaron.

## Planes

Se transcribieron las imágenes `planingles.png` (4 años), `plansoft.png` (3 años) y `planredes.png` (3 años). Las materias de cada año quedaron en el ABM; no se sumaron cargas horarias ni se completaron materias ausentes por deducción. Se conservaron las denominaciones del material, con ajustes menores de acentuación y presentación.

**Error de la fuente:** PyMES, Economía y Ciencias de la Educación enlazan también a `plansoft.png`. No se les asignó ese plan. Hay que obtener sus planes correctos.

## Archivos e importación

Las imágenes de portada y los tres planes originales se guardan en `uploads/ofertas/referencia-fac/`. Revisar vigencia y pertinencia de las imágenes antes de publicación definitiva. La imagen ya cargada para Inglés se conserva.

El conjunto de datos está en `database/seeds/ofertas_referencia_fac.json`. El comando `php database/importar_ofertas_fac.php` realiza la operación en una transacción, crea primero un respaldo JSON de ofertas y planes en `config_private/` y registra los IDs finales allí. El marcador impide repetir la importación sobre una revisión posterior. No ejecutar contra producción sin revisar previamente sus registros e imágenes.
