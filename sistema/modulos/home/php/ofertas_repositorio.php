<?php
declare(strict_types=1);

final class OfertasRepositorio
{
    public function __construct(private AppDatabase $db) {}

    public function catalogos(): array
    {
        $rows = $this->db->all('SELECT grupo,codigo,nombre FROM portal_catalogos WHERE activo=1 ORDER BY grupo,orden,nombre');
        $result = [];
        foreach ($rows as $row) $result[$row['grupo']][] = ['codigo'=>$row['codigo'],'nombre'=>$row['nombre']];
        return $result;
    }

    public function listar(array $filters): array
    {
        $where = ['o.publicada=1']; $types=''; $params=[];
        foreach (['nivel'=>'n','tipo'=>'t','turno'=>'tu','estado'=>'e'] as $key=>$alias) {
            if (!empty($filters[$key])) { $where[]="$alias.codigo=?"; $types.='s'; $params[]=$filters[$key]; }
        }
        if (!empty($filters['q'])) { $where[]='(o.titulo LIKE ? OR o.resumen LIKE ? OR n.nombre LIKE ?)'; $term='%'.$filters['q'].'%'; $types.='sss'; array_push($params,$term,$term,$term); }
        if (isset($filters['destacada']) && $filters['destacada'] !== '') { $where[]='o.destacada=?'; $types.='i'; $params[]=(int)$filters['destacada']; }
        $page=max(1,(int)($filters['page']?:1)); $perPage=max(1,min(24,(int)($filters['per_page']?:6))); $offset=($page-1)*$perPage;
        $joins=' FROM portal_ofertas_academicas o JOIN portal_catalogos n ON n.id=o.nivel_id JOIN portal_catalogos t ON t.id=o.tipo_id JOIN portal_catalogos tu ON tu.id=o.turno_id JOIN portal_catalogos e ON e.id=o.estado_id ';
        $condition=' WHERE '.implode(' AND ',$where);
        $total=(int)($this->db->one('SELECT COUNT(*) total'.$joins.$condition,$types,$params)['total']??0);
        $rows=$this->db->all('SELECT o.id,o.slug,o.titulo,o.resumen,o.imagen_url,o.destacada,n.codigo nivel,n.nombre nivel_nombre,t.codigo tipo,t.nombre tipo_nombre,tu.codigo turno,tu.nombre turno_nombre,e.codigo estado,e.nombre estado_nombre'.$joins.$condition.' ORDER BY o.destacada DESC,o.orden,o.titulo LIMIT '.$perPage.' OFFSET '.$offset,$types,$params);
        return ['items'=>$rows,'meta'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total,'last_page'=>(int)max(1,ceil($total/$perPage))]];
    }

    public function buscarPorSlug(string $slug): ?array
    {
        return $this->db->one("SELECT o.*,n.codigo nivel,n.nombre nivel_nombre,t.codigo tipo,t.nombre tipo_nombre,tu.codigo turno,tu.nombre turno_nombre,e.codigo estado,e.nombre estado_nombre FROM portal_ofertas_academicas o JOIN portal_catalogos n ON n.id=o.nivel_id JOIN portal_catalogos t ON t.id=o.tipo_id JOIN portal_catalogos tu ON tu.id=o.turno_id JOIN portal_catalogos e ON e.id=o.estado_id WHERE o.publicada=1 AND o.slug=? LIMIT 1",'s',[$slug]);
    }
}

