# Módulo Noticias y flujo con Neuria

## Objetivo

`Noticias` es la capa editorial del portal. Reúne la producción propia y las propuestas entregadas por Neuria, pero ninguna propuesta externa se publica automáticamente.

La portada lee las noticias activas desde la base local. Las propuestas de Neuria solo llegan al sitio público después de que un administrador las revise, importe y active.

## Flujo editorial

```text
Neuria → OFERTA (solo lectura) → revisión del administrador
       → copia local PENDIENTE → ACTIVA → publicación en portada
                                  ↕
                              SUSPENDIDA
```

1. El listado consulta el cliente existente `NeuriaCliente`.
2. Los elementos externos no importados se presentan con estado visual `Oferta`.
3. `Revisar` abre el mismo formulario utilizado para editar una noticia local.
4. Al guardar, se crea una fila en `noticias`, con `origen = neuria`, `neuria_id` único y estado obligatorio `pendiente`.
5. Desde ese momento desaparece de las ofertas y se administra como noticia local.
6. Las noticias propias nacen directamente en la base local.

La credencial de Neuria permanece en el servidor. El módulo reutiliza el cliente, caché y normalización existentes; no duplica autenticación ni expone secretos.

## Datos editoriales

- Título, bajada y texto completo.
- Volanta y sección.
- Fecha y hora de publicación.
- Estado: pendiente, activa o suspendida.
- Código o URL de YouTube, normalizado al identificador de 11 caracteres.
- Imágenes y documentos adjuntos.

El HTML editorial se procesa con `neuria_sanitizar_html()` antes de persistirlo. Scripts, iframes, eventos y protocolos inseguros no se almacenan.

## Archivos

Los archivos se guardan bajo `uploads/noticias/YYYY/MM` con nombres aleatorios. El nombre original se conserva solo como metadato.

- Imágenes: JPEG, PNG, WebP y GIF, hasta 8 MB.
- Adjuntos: PDF, DOC, DOCX, XLS y XLSX, hasta 15 MB.
- La validación se realiza por MIME real mediante `fileinfo`, no por la extensión enviada.
- `.htaccess` deshabilita índices y ejecución de scripts.
- Retirar un archivo o una noticia es una baja lógica.

## Persistencia

- `noticias`: contenido editorial, procedencia, estado y programación.
- `noticia_archivos`: imágenes y adjuntos relacionados.
- `neuria_id` es único y evita importar dos veces la misma oferta.
- `deleted_at` implementa bajas recuperables.

La migración es `database/migrations/005_noticias_admin_up.sql`; su reversión es `005_noticias_admin_down.sql`.

## Decisiones

- `Oferta` no es un estado persistido: describe un elemento remoto que todavía no pertenece al portal.
- Toda importación de Neuria queda pendiente, incluso si el navegador intenta enviar otro estado.
- Una caída de Neuria no bloquea el trabajo con noticias locales.
- No se modificó el cliente de Neuria: la portada cambió únicamente su fuente editorial y ahora consulta la base local.
