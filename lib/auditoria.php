<?php
declare(strict_types=1);

/** Consulta de la bitácora existente; no ofrece operaciones de edición o borrado. */
final class Auditoria
{
    public const TIPOS = ['A'=>'Alta', 'B'=>'Baja', 'M'=>'Modificación', 'E'=>'Estado'];
    public const POR_PAGINA = 50;

    public function __construct(private AppDatabase $db) {}

    public function consultar(array $entrada): array
    {
        $filtros = [
            'desde'=>$this->fecha($entrada['desde'] ?? date('Y-m-d', strtotime('-30 days'))),
            'hasta'=>$this->fecha($entrada['hasta'] ?? date('Y-m-d')),
            'usuario'=>max(0, (int)($entrada['usuario'] ?? 0)),
            'modulo'=>max(0, (int)($entrada['modulo'] ?? 0)),
            'tipo'=>is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : '',
            'q'=>mb_substr(trim(is_string($entrada['q'] ?? null) ? $entrada['q'] : ''), 0, 120),
        ];
        if ($filtros['desde'] && $filtros['hasta'] && $filtros['desde'] > $filtros['hasta']) {
            throw new InvalidArgumentException('La fecha inicial debe ser anterior a la final.');
        }
        if (!isset(self::TIPOS[$filtros['tipo']])) $filtros['tipo'] = '';
        $where=[]; $types=''; $params=[];
        foreach (['desde'=>'>=', 'hasta'=>'<='] as $clave=>$operador) {
            if ($filtros[$clave] !== '') {
                $where[]='l.system_05_fecha '.$operador.' ?'; $types.='s'; $params[]=$filtros[$clave];
            }
        }
        foreach (['usuario'=>'rela_system_03', 'modulo'=>'rela_system_01'] as $clave=>$columna) {
            if ($filtros[$clave]) {
                $where[]='l.'.$columna.'=?'; $types.='i'; $params[]=$filtros[$clave];
            }
        }
        if ($filtros['tipo'] !== '') {
            $where[]='l.system_05_accion LIKE ?'; $types.='s'; $params[]=$filtros['tipo'].':%';
        }
        if ($filtros['q'] !== '') {
            $where[]='(l.system_05_accion LIKE ? OR l.system_05_detalles LIKE ? OR l.system_05_mensaje LIKE ?)';
            $types.='sss'; array_push($params, ...array_fill(0, 3, '%'.$filtros['q'].'%'));
        }
        $condition=$where ? ' WHERE '.implode(' AND ', $where) : '';
        $total=(int)$this->db->one('SELECT COUNT(*) n FROM system_05_logistica l'.$condition, $types, $params)['n'];
        $paginas=max(1, (int)ceil($total/self::POR_PAGINA));
        $pagina=min($paginas, max(1, (int)($entrada['pagina'] ?? 1)));
        // Los nombres actuales complementan los ID históricos, incluso si la cuenta fue eliminada.
        $filas=$this->db->all("SELECT l.*,u.system_03_usuario,
            (SELECT CONCAT_WS(' ',p.system_04_nombre,p.system_04_apellido) FROM system_04_perfil p
             WHERE p.rela_system_03=l.rela_system_03 ORDER BY p.id_system_04 LIMIT 1) administrador,
            r.system_07_nombre rol,m.system_01_modulo modulo
            FROM system_05_logistica l
            LEFT JOIN system_03_usuarios u ON u.id_system_03=l.rela_system_03
            LEFT JOIN system_07_privilegios r ON r.id_system_07=l.rela_system_07
            LEFT JOIN system_01_modulos m ON m.id_system_01=l.rela_system_01".$condition.
            ' ORDER BY l.system_05_fecha DESC,l.system_05_hora DESC,l.id_system_05 DESC LIMIT ? OFFSET ?',
            $types.'ii', [...$params, self::POR_PAGINA, ($pagina-1)*self::POR_PAGINA]);
        return compact('filtros','filas','total','pagina','paginas');
    }

    private function fecha(mixed $valor): string
    {
        if ($valor==='') return '';
        if (!is_string($valor)) throw new InvalidArgumentException('Fecha inválida.');
        $fecha=DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        if (!$fecha || $fecha->format('Y-m-d')!==$valor) throw new InvalidArgumentException('Fecha inválida.');
        return $valor;
    }
}
