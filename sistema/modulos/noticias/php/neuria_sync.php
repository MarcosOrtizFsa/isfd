<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
require_once __DIR__.'/neuria_v1_cliente.php';
require_once __DIR__.'/../../home/php/neuria_sanitizador.php';

final class NeuriaSincronizador
{
    public function __construct(private AppDatabase $db,private NeuriaV1Cliente $client){}

    public function sincronizarTodo():array{return ['contenidos'=>$this->sincronizarContenidos(),'cambios'=>$this->sincronizarCambios()];}
    public function sincronizarContenidos():array{return $this->paginar('contenidos',fn($cursor)=>$this->client->contenidos($cursor),fn($item,&$stats)=>$this->importar($item,$stats));}
    public function sincronizarCambios():array{return $this->paginar('cambios',fn($cursor)=>$this->client->cambios($cursor),fn($item,&$stats)=>$this->procesarCambio($item,$stats));}
    public function importarContenido(array $item):array{$stats=['recibidos'=>1,'importados'=>0,'omitidos'=>0,'errores'=>0];$this->importar($item,$stats);return $stats;}

    private function paginar(string $flujo,callable $solicitar,callable $procesar):array
    {
        $estado=$this->db->one('SELECT cursor_valor FROM neuria_sincronizaciones WHERE flujo=?','s',[$flujo]);$cursor=$estado['cursor_valor']??null;$stats=['recibidos'=>0,'importados'=>0,'omitidos'=>0,'errores'=>0];
        try{
            do{
                $respuesta=$solicitar($cursor);if(!$respuesta['ok'])throw new RuntimeException('Neuria: '.$respuesta['error'].' (HTTP '.$respuesta['http_code'].')');
                $items=$respuesta['data'];if(!is_array($items))throw new RuntimeException('Neuria devolvio una pagina invalida.');
                $meta=$respuesta['meta'];$next=isset($meta['next_cursor'])?(string)$meta['next_cursor']:null;$more=(bool)($meta['has_more']??false);
                $this->db->transaction(function()use($items,$procesar,$flujo,$next,&$stats){foreach($items as $item){$stats['recibidos']++;try{$procesar($item,$stats);}catch(Throwable $e){$stats['errores']++;throw $e;}}$this->guardarEstado($flujo,$next,$stats,null);});
                $cursor=$next;if($more&&($cursor===null||$cursor===''))throw new RuntimeException('Neuria indico mas paginas sin entregar cursor.');
            }while($more);
            return ['ok'=>true]+$stats;
        }catch(Throwable $error){$this->guardarEstado($flujo,$cursor,$stats,mb_substr($error->getMessage(),0,500));return ['ok'=>false,'error'=>$error->getMessage()]+$stats;}
    }

    private function importar(mixed $item,array &$stats):void
    {
        $data=$this->normalizar($item);$exists=$this->db->one('SELECT id FROM noticias WHERE neuria_uuid=?','s',[$data['uuid']]);
        if($exists){$this->actualizarOriginal((int)$exists['id'],$data);$stats['omitidos']++;return;}
        // El cuerpo se conserva exactamente como lo entrega NEURIA. La conversión
        // a HTML seguro ocurre solamente cuando se publica en la portada.
        $this->db->insert("INSERT INTO noticias (neuria_uuid,origen,neuria_estado,neuria_bloqueada,neuria_titulo,neuria_bajada,neuria_contenido,neuria_categoria,neuria_etiquetas,neuria_fuentes,neuria_canal,neuria_generado_at,neuria_disponible_at,neuria_original,neuria_importado_at,neuria_sincronizado_at,titulo,bajada,contenido,seccion,estado) VALUES (?,'neuria',?,0,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),?,?,?,?,'pendiente')",'ssssssssssssssss',[$data['uuid'],$data['estado'],$data['titulo'],$data['bajada'],$data['contenido'],$data['categoria'],$data['etiquetas'],$data['fuentes'],$data['canal'],$data['generado_at'],$data['disponible_at'],$data['original'],$data['titulo'],$data['bajada'],$data['contenido'],$data['categoria']]);$stats['importados']++;
    }

    private function procesarCambio(mixed $evento,array &$stats):void
    {
        if(!is_array($evento))throw new RuntimeException('Evento de cambio invalido.');$tipo=strtoupper((string)($evento['type']??$evento['event']??$evento['evento']??''));$uuid=(string)($evento['content_uuid']??$evento['uuid']??($evento['content']['uuid']??''));if($uuid==='')throw new RuntimeException('Evento sin UUID.');
        if($tipo==='CONTENT_AVAILABLE'){$contenido=is_array($evento['content']??null)?['ok'=>true,'data'=>$evento['content']]:$this->client->contenido($uuid);if(!$contenido['ok'])throw new RuntimeException('No se pudo obtener '.$uuid);$this->importar($contenido['data'],$stats);return;}
        $row=$this->db->one('SELECT id,estado FROM noticias WHERE neuria_uuid=?','s',[$uuid]);if(!$row){$stats['omitidos']++;return;}
        if($tipo==='CONTENT_SUSPENDED')$this->db->execute("UPDATE noticias SET neuria_estado='suspended',neuria_bloqueada=1,estado='suspendida',neuria_sincronizado_at=NOW() WHERE id=?",'i',[(int)$row['id']]);
        elseif($tipo==='CONTENT_WITHDRAWN')$this->db->execute("UPDATE noticias SET neuria_estado='withdrawn',neuria_bloqueada=1,estado='suspendida',neuria_sincronizado_at=NOW() WHERE id=?",'i',[(int)$row['id']]);
        elseif($tipo==='CONTENT_REACTIVATED')$this->db->execute("UPDATE noticias SET neuria_estado='available',neuria_bloqueada=0,estado=IF(estado='suspendida','pendiente',estado),neuria_sincronizado_at=NOW() WHERE id=?",'i',[(int)$row['id']]);
        else throw new RuntimeException('Evento no soportado: '.$tipo);
    }

    private function actualizarOriginal(int $id,array $d):void{$this->db->execute('UPDATE noticias SET neuria_estado=?,neuria_titulo=?,neuria_bajada=?,neuria_contenido=?,neuria_categoria=?,neuria_etiquetas=?,neuria_fuentes=?,neuria_canal=?,neuria_generado_at=?,neuria_disponible_at=?,neuria_original=?,neuria_sincronizado_at=NOW() WHERE id=?','sssssssssssi',[$d['estado'],$d['titulo'],$d['bajada'],$d['contenido'],$d['categoria'],$d['etiquetas'],$d['fuentes'],$d['canal'],$d['generado_at'],$d['disponible_at'],$d['original'],$id]);}
    private function normalizar(mixed $item):array
    {
        if(!is_array($item))throw new RuntimeException('Contenido invalido.');$uuid=trim((string)($item['uuid']??$item['id']??''));$titulo=trim((string)($item['title']??$item['titulo']??''));if($uuid===''||$titulo==='')throw new RuntimeException('Contenido sin UUID o titulo.');
        $texto=static function($v):string{if(is_array($v))return trim((string)($v['name']??$v['nombre']??json_encode($v,JSON_UNESCAPED_UNICODE)));return trim((string)$v);};$json=static fn($v)=>json_encode(is_array($v)?$v:($v===null?[]:[$v]),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return ['uuid'=>$uuid,'titulo'=>$titulo,'bajada'=>trim((string)($item['subtitle']??$item['summary']??$item['bajada']??'')),'contenido'=>(string)($item['body']??$item['content']??$item['contenido']??''),'categoria'=>$texto($item['category']??$item['categoria']??''),'etiquetas'=>$json($item['tags']??$item['etiquetas']??[]),'fuentes'=>$json($item['sources']??$item['fuentes']??[]),'canal'=>$texto($item['channel']??$item['channel_id']??$item['canal']??''),'generado_at'=>$this->fecha($item['generated_at']??$item['fecha_generacion']??null),'disponible_at'=>$this->fecha($item['available_from']??$item['fecha_disponibilidad']??null),'estado'=>trim((string)($item['status']??$item['estado']??'available')),'original'=>json_encode($item,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)];
    }
    private function fecha(mixed $v):?string{if(!$v)return null;try{return(new DateTime((string)$v))->format('Y-m-d H:i:s');}catch(Throwable){return null;}}
    private function guardarEstado(string $flujo,?string $cursor,array $s,?string $error):void{$this->db->execute('UPDATE neuria_sincronizaciones SET cursor_valor=?,ultima_ejecucion_at=NOW(),ultimo_exito_at=IF(? IS NULL,NOW(),ultimo_exito_at),ultimo_error=?,recibidos=?,importados=?,omitidos=?,errores=? WHERE flujo=?','sssiiiis',[$cursor,$error,$error,$s['recibidos'],$s['importados'],$s['omitidos'],$s['errores'],$flujo]);}
}
