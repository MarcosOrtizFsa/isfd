<?php
declare(strict_types=1);

/** Autorización por usuario/módulo. Solo modo 0 tiene excepción global. */
final class PermisosModulos
{
    public const MODULOS = ['home','control','ofertas','noticias','contactos'];
    public const ACCIONES = ['A'=>'Agregar','B'=>'Borrar','M'=>'Modificar','E'=>'Cambiar estado'];
    private array $asignaciones = [];

    public function __construct(private AppDatabase $db) {}

    public function permite(array $usuario, string $modulo, string $accion='acceso'): bool
    {
        if (!in_array($modulo,[...self::MODULOS,'root'],true) || !in_array($accion,['acceso',...array_keys(self::ACCIONES)],true)) return false;
        if ((int)$usuario['mode']===0) return true;
        if ($modulo==='root') return false;
        try {
            $clave=(int)$usuario['id'].':'.$modulo;
            if (!array_key_exists($clave,$this->asignaciones)) {
                $this->asignaciones[$clave]=$this->db->one("SELECT p.* FROM system_02_permisos p
                JOIN system_01_modulos m ON m.id_system_01=p.rela_system_01
                WHERE p.rela_system_03=? AND m.system_01_path_home=?
                AND m.system_01_estado=1 AND m.system_01_onoff='on' ORDER BY p.id_system_02 DESC LIMIT 1",'is',[(int)$usuario['id'],$modulo]);
            }
            $fila=$this->asignaciones[$clave];
            return $fila && (int)($fila['system_02_acceso']??0)===1
                && ($accion==='acceso' || (int)$fila['system_02_'.$accion]===1);
        } catch (mysqli_sql_exception $e) {
            // Una instalación incompleta nunca concede acceso por defecto.
            error_log('Permisos: '.$e->getMessage());
            return false;
        }
    }

    public function guardar(int $usuarioId, array $permisos): void
    {
        $this->db->transaction(function () use ($usuarioId,$permisos): void {
            $usuario=$this->db->one('SELECT system_03_modo FROM system_03_usuarios WHERE id_system_03=? FOR UPDATE','i',[$usuarioId]);
            if (!$usuario || (int)$usuario['system_03_modo']===0) throw new DomainException('Root tiene acceso completo; sus permisos no se editan.');
            $modulos=$this->db->all("SELECT id_system_01,system_01_path_home FROM system_01_modulos WHERE system_01_path_home IN ('home','control','ofertas','noticias','contactos')");
            $validos=array_map('intval',array_column($modulos,'id_system_01'));
            foreach ($permisos as $id=>$valores) {
                if (!in_array((int)$id,$validos,true) || !is_array($valores)) {
                    throw new DomainException('Módulo o permisos inválidos.');
                }
                foreach ($valores as $clave=>$valor) {
                    if (!in_array($clave,['acceso',...array_keys(self::ACCIONES)],true)
                        || !in_array($valor,['0','1',0,1],true)) {
                        throw new DomainException('Permiso inválido.');
                    }
                }
            }
            foreach ($modulos as $modulo) {
                $id=(int)$modulo['id_system_01'];$valores=$permisos[$id]??[];
                $acceso=(int)($valores['acceso']??0)===1?1:0;
                $acciones=[];
                foreach (array_keys(self::ACCIONES) as $accion) {
                    $habilitada=$acceso && $modulo['system_01_path_home']!=='home'
                        && (int)($valores[$accion]??0)===1;
                    $acciones[]=$habilitada?1:0;
                }
                // La tabla heredada puede tener duplicados: reemplaza solo la asignación editada.
                $this->db->execute('DELETE FROM system_02_permisos WHERE rela_system_03=? AND rela_system_01=?','ii',[$usuarioId,$id]);
                $this->db->insert('INSERT INTO system_02_permisos (rela_system_03,rela_system_01,system_02_acceso,system_02_A,system_02_B,system_02_M,system_02_E,system_02_V,system_02_S,system_02_D,system_02_I,system_02_C) VALUES (?,?,?,?,?,?,?,0,0,0,0,0)','iiiiiii',[$usuarioId,$id,$acceso,...$acciones]);
            }
        });
        $this->asignaciones=[];
    }
}

function app_can(string $modulo, string $accion='acceso'): bool
{
    // Una consulta por módulo y petición, aunque el listado tenga muchas filas.
    static $permisos;
    $permisos??=new PermisosModulos(app_db());
    $usuario=app_user();
    return $usuario!==null && $permisos->permite($usuario,$modulo,$accion);
}

function app_require_permission(string $modulo, string|array $acciones='acceso'): array
{
    $usuario=app_require_login();
    foreach ((array)$acciones as $accion) {
        if (!app_can($modulo,$accion)) {
            http_response_code(403);
            exit('No tenés permiso para realizar esta acción en el módulo.');
        }
    }
    return $usuario;
}

/** El permiso M no permite publicar ni cambiar estados por un POST modificado. */
function app_guard_state(string $modulo,string $campo,mixed $actual): void
{
    if (app_can($modulo,'E')) return;
    if (isset($_POST[$campo]) && (string)$_POST[$campo] !== (string)$actual) app_require_permission($modulo,'E');
    $_POST[$campo]=(string)$actual;
}

function app_can_manage_user(array $actual,array $objetivo): bool
{
    return $actual['mode']===0 || ((int)$objetivo['id_system_03']!==$actual['id']
        && (int)$objetivo['system_03_modo']!==0 && (int)$objetivo['rela_system_07']>$actual['role_id']);
}

/** Atributo visual; la autorización efectiva se repite en el endpoint. */
function app_permission_attrs(string $modulo, string|array $acciones): string
{
    foreach ((array)$acciones as $accion) if (!app_can($modulo,$accion)) return 'disabled aria-disabled="true"';
    return '';
}
