<?php
declare(strict_types=1);

/** Acceso de solo lectura al catálogo normalizado. */
final class OfertasRepositorio
{
    private const CATEGORIAS_LEGACY = [
        'inicial'=>'nivel-inicial', 'primario'=>'nivel-primario',
        'secundario'=>'nivel-secundario', 'profesorado'=>'profesorados',
        'tecnicatura'=>'tecnicaturas-superiores', 'orientacion'=>'ciclo-orientado',
    ];

    public function __construct(private AppDatabase $db) {}

    /** Mantiene las claves esperadas por los controles actuales de la portada. */
    public function catalogos(): array
    {
        return [
            'tipo'=>$this->db->all('SELECT slug codigo,nombre FROM catalogo_categorias WHERE activo=1 AND admite_ofertas=1 ORDER BY orden,nombre'),
            'turno'=>$this->db->all('SELECT codigo,nombre FROM turnos WHERE activo=1 ORDER BY orden,nombre'),
            'estado'=>[
                ['codigo'=>'proximamente','nombre'=>'Próximamente'],
                ['codigo'=>'inscripcion_abierta','nombre'=>'Inscripción abierta'],
                ['codigo'=>'en_curso','nombre'=>'En curso'],
                ['codigo'=>'a_termino','nombre'=>'A término'],
                ['codigo'=>'cerrada','nombre'=>'Cerrada'],
            ],
        ];
    }

    public function listar(array $filters): array
    {
        $where=['1=1']; $types=''; $params=[];
        $nivel=$this->slug((string)($filters['nivel']??''));
        $categoria=$this->slug((string)($filters['categoria']??''));
        $tipo=$this->slug((string)($filters['tipo']??''));
        if ($nivel!=='' && isset(self::CATEGORIAS_LEGACY[$nivel])) {
            $categoria=self::CATEGORIAS_LEGACY[$nivel]; $nivel='';
        }
        if ($categoria==='' && $tipo!=='') $categoria=self::CATEGORIAS_LEGACY[$tipo]??$tipo;
        if ($nivel!=='') { $where[]='v.nivel_codigo=?'; $types.='s'; $params[]=$nivel; }
        if ($categoria!=='') {
            $where[]='v.categoria_ruta LIKE CONCAT((SELECT ruta FROM catalogo_categorias WHERE slug=? AND activo=1 LIMIT 1), "%")';
            $types.='s'; $params[]=$categoria;
        }
        foreach (['modalidad'=>'modalidad_codigo','estado'=>'estado_oferta'] as $key=>$column) {
            $value=$this->slug((string)($filters[$key]??''));
            if ($value!=='') { $where[]="v.$column=?"; $types.='s'; $params[]=$value; }
        }
        $turno=$this->slug((string)($filters['turno']??''));
        if ($turno!=='') {
            $where[]='EXISTS (SELECT 1 FROM oferta_turno ot JOIN turnos t ON t.id=ot.turno_id WHERE ot.oferta_id=v.id AND t.codigo=?)';
            $types.='s'; $params[]=$turno;
        }
        $area=$this->slug((string)($filters['area']??''));
        if ($area!=='') {
            $where[]='EXISTS (SELECT 1 FROM oferta_area oa JOIN areas_conocimiento a ON a.id=oa.area_id WHERE oa.oferta_id=v.id AND a.slug=?)';
            $types.='s'; $params[]=$area;
        }
        $query=trim((string)($filters['q']??''));
        if ($query!=='') {
            $where[]='(v.titulo LIKE ? OR v.resumen LIKE ? OR v.descripcion LIKE ? OR v.titulo_otorgado LIKE ? OR v.categoria_nombre LIKE ?)';
            $term='%'.$query.'%'; $types.='sssss'; array_push($params,$term,$term,$term,$term,$term);
        }
        if (isset($filters['destacada']) && $filters['destacada']!=='') {
            $where[]='v.destacada=?'; $types.='i'; $params[]=(int)$filters['destacada'];
        }
        $pageValue=$filters['page']??'';
        $perPageValue=$filters['per_page']??'';
        $page=max(1,$pageValue===''?1:(int)$pageValue);
        $perPage=max(1,min(24,$perPageValue===''?6:(int)$perPageValue));
        $offset=($page-1)*$perPage;
        $condition=' WHERE '.implode(' AND ',$where);
        $total=(int)($this->db->one('SELECT COUNT(*) total FROM v_catalogo_ofertas v'.$condition,$types,$params)['total']??0);
        $rows=$this->db->all($this->selectBase().$condition.' ORDER BY v.destacada DESC,v.orden,v.titulo LIMIT '.$perPage.' OFFSET '.$offset,$types,$params);
        return ['items'=>$rows,'meta'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total,'last_page'=>(int)max(1,ceil($total/$perPage))]];
    }

    public function buscarPorSlug(string $slug): ?array
    {
        return $this->db->one($this->selectBase().' WHERE v.slug=? LIMIT 1','s',[$slug]);
    }

    /** Árbol activo con conteos que incluyen todas las categorías descendientes. */
    public function arbol(): array
    {
        $levels=$this->db->all('SELECT id,codigo,nombre,descripcion,orden FROM catalogo_niveles WHERE activo=1 ORDER BY orden,nombre');
        $categories=$this->db->all("SELECT c.id,c.nivel_id,c.parent_id,c.slug,c.nombre,c.descripcion,c.profundidad,c.ruta,c.admite_ofertas,c.icono,c.imagen_url,c.orden,
            (SELECT COUNT(*) FROM v_catalogo_ofertas v WHERE v.categoria_ruta LIKE CONCAT(c.ruta,'%')) ofertas_count
            FROM catalogo_categorias c WHERE c.activo=1 ORDER BY c.nivel_id,c.profundidad,c.orden,c.nombre");
        $byParent=[];
        foreach ($categories as $category) {
            $key=$category['parent_id']===null?'root-'.$category['nivel_id']:(string)$category['parent_id'];
            $byParent[$key][]=$category;
        }
        $build=function(string $key) use (&$build,&$byParent):array {
            $nodes=[];
            foreach ($byParent[$key]??[] as $category) {
                $category['id']=(int)$category['id'];
                $category['nivel_id']=(int)$category['nivel_id'];
                $category['parent_id']=$category['parent_id']===null?null:(int)$category['parent_id'];
                $category['profundidad']=(int)$category['profundidad'];
                $category['admite_ofertas']=(bool)$category['admite_ofertas'];
                $category['ofertas_count']=(int)$category['ofertas_count'];
                $category['children']=$build((string)$category['id']);
                $nodes[]=$category;
            }
            return $nodes;
        };
        foreach ($levels as &$level) {
            $level['id']=(int)$level['id'];
            $level['categorias']=$build('root-'.$level['id']);
            $level['ofertas_count']=array_sum(array_column($level['categorias'],'ofertas_count'));
        }
        unset($level);
        return $levels;
    }

    private function selectBase(): string
    {
        return "SELECT v.*,v.nivel_codigo nivel,v.categoria_slug tipo,v.categoria_nombre tipo_nombre,
            v.estado_oferta estado,
            CASE v.estado_oferta WHEN 'inscripcion_abierta' THEN 'Inscripción abierta' WHEN 'en_curso' THEN 'En curso' WHEN 'a_termino' THEN 'A término' WHEN 'cerrada' THEN 'Cerrada' ELSE 'Próximamente' END estado_nombre,
            COALESCE((SELECT GROUP_CONCAT(t.codigo ORDER BY t.orden SEPARATOR ',') FROM oferta_turno ot JOIN turnos t ON t.id=ot.turno_id WHERE ot.oferta_id=v.id),'') turno,
            COALESCE((SELECT GROUP_CONCAT(t.nombre ORDER BY t.orden SEPARATOR ', ') FROM oferta_turno ot JOIN turnos t ON t.id=ot.turno_id WHERE ot.oferta_id=v.id),'Sin turno específico') turno_nombre,
            COALESCE((SELECT GROUP_CONCAT(a.slug ORDER BY a.orden SEPARATOR ',') FROM oferta_area oa JOIN areas_conocimiento a ON a.id=oa.area_id WHERE oa.oferta_id=v.id),'') areas
            FROM v_catalogo_ofertas v";
    }

    private function slug(string $value): string
    {
        return preg_replace('/[^a-z0-9_-]/','',strtolower($value))??'';
    }
}

