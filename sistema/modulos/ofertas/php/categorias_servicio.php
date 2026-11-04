<?php
declare(strict_types=1);

/** ABM del catálogo: conserva las rutas y las relaciones de categorías existentes. */
final class CategoriasServicio
{
    public function __construct(private AppDatabase $db) {}

    public function ejecutar(string $accion, array $datos): int
    {
        return $this->db->transaction(function () use ($accion, $datos): int {
            // Serializa cambios de estructura, incluso al crear la primera categoría.
            $this->db->all('SELECT id FROM catalogo_niveles ORDER BY id FOR UPDATE');
            $filas = $this->db->all('SELECT * FROM catalogo_categorias ORDER BY id FOR UPDATE');
            $categorias = array_column($filas, null, 'id');
            $id = (int)($datos['id'] ?? 0);
            $actual = $categorias[$id] ?? null;
            if ($id && !$actual) throw new DomainException('La categoría ya no existe.');
            if (!in_array($accion, ['save', 'delete', 'toggle'], true)) throw new DomainException('Acción inválida.');
            if ($accion !== 'save') {
                if (!$actual) throw new DomainException('Seleccioná una categoría.');
                if ($accion === 'toggle') {
                    $this->db->execute('UPDATE catalogo_categorias SET activo=? WHERE id=?', 'ii', [1-(int)$actual['activo'], $id]);
                } else {
                    $tieneHijos = array_filter($filas, fn(array $fila): bool => (int)$fila['parent_id'] === $id);
                    $tieneOfertas = $this->db->one('SELECT id FROM ofertas WHERE categoria_id=? LIMIT 1', 'i', [$id]);
                    if ($tieneHijos || $tieneOfertas) throw new DomainException('Tiene subcategorías u ofertas asociadas. Podés desactivarla, pero no eliminarla.');
                    $this->db->execute('DELETE FROM catalogo_categorias WHERE id=?', 'i', [$id]);
                }
                return $id;
            }

            $nombre = trim((string)($datos['nombre'] ?? ''));
            $descripcion = trim((string)($datos['descripcion'] ?? ''));
            $orden = filter_var($datos['orden'] ?? 0, FILTER_VALIDATE_INT);
            if ($nombre === '' || mb_strlen($nombre) > 150) throw new DomainException('Ingresá un nombre de hasta 150 caracteres.');
            if (strlen($descripcion) > 60000) throw new DomainException('La descripción es demasiado extensa.');
            if ($orden === false || $orden < 0 || $orden > 65535) throw new DomainException('El orden debe estar entre 0 y 65535.');
            $activo = ($datos['activo'] ?? '') === '1' ? 1 : 0;
            $admite = ($datos['admite_ofertas'] ?? '') === '1' ? 1 : 0;
            if ($actual) {
                if ($admite && array_filter($filas, fn(array $fila): bool => (int)$fila['parent_id'] === $id)) {
                    throw new DomainException('Una categoría con subcategorías no puede recibir ofertas directamente.');
                }
                if (!$admite && $this->db->one('SELECT id FROM ofertas WHERE categoria_id=? LIMIT 1', 'i', [$id])) {
                    throw new DomainException('La categoría tiene ofertas: debe seguir admitiéndolas.');
                }
                // No cambia el slug ni la jerarquía al editar: mantiene enlaces y rutas existentes.
                $this->db->execute('UPDATE catalogo_categorias SET nombre=?,descripcion=?,orden=?,activo=?,admite_ofertas=? WHERE id=?', 'ssiiii', [$nombre,$descripcion,$orden,$activo,$admite,$id]);
                return $id;
            }

            $nivel = (int)($datos['nivel_id'] ?? 0);
            $padreId = (int)($datos['parent_id'] ?? 0);
            if (!$this->db->one('SELECT id FROM catalogo_niveles WHERE id=? AND activo=1', 'i', [$nivel])) throw new DomainException('Seleccioná un nivel activo.');
            $padre = $categorias[$padreId] ?? null;
            if ($padreId && (!$padre || (int)$padre['nivel_id'] !== $nivel || (int)$padre['admite_ofertas'] || !(int)$padre['activo'])) {
                throw new DomainException('La categoría superior debe estar activa, pertenecer al mismo nivel y no admitir ofertas.');
            }
            $profundidad = $padre ? (int)$padre['profundidad'] + 1 : 1;
            if ($profundidad > 3) throw new DomainException('Se admiten hasta tres niveles de categorías.');
            $base = strtolower((string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre));
            $base = substr(trim((string)preg_replace('/[^a-z0-9]+/', '-', $base), '-'), 0, 100) ?: 'categoria';
            $slug = $base;
            $sufijo = 2;
            $slugs = array_column($filas, 'slug');
            while (in_array($slug, $slugs, true)) $slug = $base.'-'.$sufijo++;
            $id = $this->db->insert('INSERT INTO catalogo_categorias (nivel_id,parent_id,slug,nombre,descripcion,profundidad,ruta,admite_ofertas,orden,activo) VALUES (?,?,?,?,?,?,?, ?,?,?)', 'iisssisiii', [$nivel,$padreId ?: null,$slug,$nombre,$descripcion,$profundidad,'/',$admite,$orden,$activo]);
            // Compatibilidad con los triggers de integridad del catálogo.
            try {
                $this->db->execute('SET @catalogo_movimiento_permitido=1');
                $this->db->execute('UPDATE catalogo_categorias SET ruta=? WHERE id=?', 'si', [($padre['ruta'] ?? '/').$id.'/', $id]);
            } finally {
                $this->db->execute('SET @catalogo_movimiento_permitido=NULL');
            }
            return $id;
        });
    }
}
