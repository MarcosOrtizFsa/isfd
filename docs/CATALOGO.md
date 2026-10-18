# Catálogo de ofertas académicas

## Modelo

El catálogo separa tres conceptos: `catalogo_niveles` contiene Educación Básica y Educación Superior; `catalogo_categorias` forma un árbol editable; y `ofertas` contiene las propuestas concretas, vinculadas únicamente con categorías hoja activas.

Cada categoría guarda una ruta materializada como `/3/10/`. Una búsqueda por prefijo obtiene una categoría y todos sus descendientes sin depender de una cantidad fija de uniones. Las ofertas admiten varios turnos y áreas mediante `oferta_turno` y `oferta_area`.

## Migraciones

En una base nueva se ejecutan, en orden:

```text
database/2026_10_17_portal_academico.sql
database/migrations/002_catalogo_ofertas_up.sql
database/migrations/003_ofertas_superiores_demo_up.sql
```

Para revertir la segunda migración se ejecuta `database/migrations/002_catalogo_ofertas_down.sql`. El `up` migra las consultas existentes al nuevo `ofertas.id`; el `down` vuelve a asociarlas con las ofertas DEMO anteriores.

## Reglas de integridad

- Una hija siempre pertenece al mismo nivel que su padre.
- La profundidad máxima es tres.
- Una categoría con hijas no puede admitir ofertas.
- Una oferta solo se asocia con una categoría activa y con `admite_ofertas = 1`.
- La jerarquía no se modifica con `UPDATE` directo: se usa `catalogo_mover_categoria`.
- El movimiento impide ciclos y recalcula ruta y profundidad de todo el subárbol.
- Las bajas de ofertas son lógicas mediante `deleted_at`.
- Las consultas conservan `ip_hash`; no se guarda la dirección IP en texto plano.

Las reglas críticas se validan con triggers para cubrir el futuro administrador, importaciones y tareas programadas. El movimiento se concentra en un procedimiento porque afecta varias filas de forma atómica.

## Agregar una categoría

Los slugs son únicos, en minúsculas, sin tildes y separados con guiones. Para una raíz, el segundo parámetro es `NULL`:

```sql
SET @nivel = (SELECT id FROM catalogo_niveles WHERE codigo = 'superior');
CALL catalogo_crear_categoria(@nivel, NULL, 'extension-academica', 'Extensión Académica', 1, 60);
```

Para agregar una hija, el padre debe tener `admite_ofertas = 0`:

```sql
SET @padre = (SELECT id FROM catalogo_categorias WHERE slug = 'formacion-continua');
CALL catalogo_crear_categoria(@nivel, @padre, 'seminarios', 'Seminarios', 1, 40);
```

## Mover una categoría

```sql
SET @categoria = (SELECT id FROM catalogo_categorias WHERE slug = 'seminarios');
SET @nuevo_padre = (SELECT id FROM catalogo_categorias WHERE slug = 'formacion-docente');
CALL catalogo_mover_categoria(@categoria, @nuevo_padre);
```

El procedimiento rechaza padres de otro nivel, categorías hoja, ciclos y movimientos que superen tres niveles.

## Consultas para filtros

### Categoría y descendientes

```sql
SELECT v.*
FROM v_catalogo_ofertas v
JOIN catalogo_categorias seleccionada ON seleccionada.slug = 'formacion-continua'
WHERE v.categoria_ruta LIKE CONCAT(seleccionada.ruta, '%');
```

### Filtros combinados

```sql
SELECT DISTINCT v.*
FROM v_catalogo_ofertas v
JOIN oferta_turno ot ON ot.oferta_id = v.id
JOIN turnos t ON t.id = ot.turno_id
JOIN oferta_area oa ON oa.oferta_id = v.id
JOIN areas_conocimiento a ON a.id = oa.area_id
JOIN catalogo_categorias seleccionada ON seleccionada.slug = 'formacion-docente'
WHERE v.nivel_codigo = 'superior'
  AND v.categoria_ruta LIKE CONCAT(seleccionada.ruta, '%')
  AND v.modalidad_codigo = 'presencial'
  AND t.codigo = 'noche'
  AND a.slug = 'idiomas'
  AND v.estado_oferta = 'inscripcion_abierta';
```

### Búsqueda FULLTEXT con filtros

```sql
SELECT v.*
FROM v_catalogo_ofertas v
JOIN ofertas o ON o.id = v.id
WHERE MATCH(o.titulo, o.resumen, o.descripcion, o.titulo_otorgado)
      AGAINST('+ingles +docencia' IN BOOLEAN MODE)
  AND v.nivel_codigo = 'superior'
  AND v.modalidad_codigo = 'presencial';
```

### Conteos por faceta

```sql
SELECT nivel_codigo, nivel_nombre, COUNT(*) cantidad
FROM v_catalogo_ofertas GROUP BY nivel_codigo, nivel_nombre;

SELECT c.id, c.slug, c.nombre, COUNT(v.id) cantidad
FROM catalogo_categorias c
LEFT JOIN v_catalogo_ofertas v ON v.categoria_ruta LIKE CONCAT(c.ruta, '%')
WHERE c.activo = 1 GROUP BY c.id, c.slug, c.nombre;

SELECT t.codigo, t.nombre, COUNT(DISTINCT v.id) cantidad
FROM turnos t
LEFT JOIN oferta_turno ot ON ot.turno_id = t.id
LEFT JOIN v_catalogo_ofertas v ON v.id = ot.oferta_id
WHERE t.activo = 1 GROUP BY t.id, t.codigo, t.nombre;
```

## API de lectura

`GET /api/ofertas-academicas` acepta `q`, `nivel`, `categoria`, `modalidad`, `turno`, `area`, `estado`, `destacada`, `page` y `per_page`. El filtro `categoria` incluye descendientes. `tipo` continúa como alias temporal; los valores históricos `inicial`, `primario` y `secundario` recibidos en `nivel` también se traducen a categorías.

`GET /api/catalogo/arbol` devuelve niveles y categorías activas con `children` y conteo publicado acumulado por nodo.

## Decisiones

- Se conserva `portal_consultas` por compatibilidad con la bandeja transaccional.
- El nuevo catálogo usa `BIGINT UNSIGNED`; también se amplió la FK `oferta_id` de consultas.
- `edicion` es `NOT NULL DEFAULT ''` para que la unicidad anual funcione con una sola edición.
- La vista prioriza una convocatoria abierta y, si no existe, la programada más próxima.
- Las ofertas iniciales llevan el prefijo `demo-` y no son información oficial.
- La instalación local usa MariaDB 10.4 de XAMPP; el SQL evita incompatibilidades y conserva el diseño previsto para MySQL 8.x.
