<?php
declare(strict_types=1);
require_once __DIR__.'/neuria_sync.php';

final class NeuriaSolicitudes
{
    private const TERMINALES=['COMPLETADA','PARCIALMENTE_COMPLETADA','ERROR','CANCELADA'];
    public function __construct(private AppDatabase $db,private NeuriaV1Cliente $client){}

    public function crear(array $entrada,int $userId):array
    {
        $payload=$this->validar($entrada);$bucket=(string)floor(time()/60);$dedupe=hash('sha256',$userId.'|'.$bucket.'|'.json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        $existing=$this->db->one('SELECT * FROM neuria_requests WHERE dedupe_key=?','s',[$dedupe]);if($existing)return ['ok'=>true,'duplicada'=>true,'request'=>$existing];
        $response=$this->client->crearSolicitud($payload);if(!$response['ok'])return ['ok'=>false,'error'=>$response['error'],'http_code'=>$response['http_code']];
        $data=is_array($response['data'])?$response['data']:[];$uuid=trim((string)($data['id']??''));if($uuid==='')return ['ok'=>false,'error'=>'invalid_response','http_code'=>$response['http_code']];
        try{$id=$this->db->insert('INSERT INTO neuria_requests (neuria_request_id,status,progress,requested_title,request_payload,response_payload,dedupe_key,created_by) VALUES (?,?,?,?,?,?,?,?)','ssissssi',[$uuid,(string)($data['status']??'PENDIENTE'),max(0,min(100,(int)($data['progress']??0))),$payload['title'],json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$dedupe,$userId]);}catch(mysqli_sql_exception){$row=$this->db->one('SELECT * FROM neuria_requests WHERE neuria_request_id=? OR dedupe_key=? LIMIT 1','ss',[$uuid,$dedupe]);return ['ok'=>true,'duplicada'=>true,'request'=>$row];}
        return ['ok'=>true,'duplicada'=>false,'request'=>$this->db->one('SELECT * FROM neuria_requests WHERE id=?','i',[$id])];
    }

    public function sincronizarPendientes():array
    {
        $rows=$this->db->all("SELECT * FROM neuria_requests WHERE status NOT IN ('COMPLETADA','PARCIALMENTE_COMPLETADA','ERROR','CANCELADA') ORDER BY id");$stats=['consultadas'=>0,'actualizadas'=>0,'importadas'=>0,'errores'=>0];
        foreach($rows as $row){$stats['consultadas']++;try{$this->sincronizarUna($row,$stats);}catch(Throwable $e){$stats['errores']++;$this->db->execute('UPDATE neuria_requests SET error_message=? WHERE id=?','si',[mb_substr($e->getMessage(),0,500),(int)$row['id']]);}}
        return ['ok'=>$stats['errores']===0]+$stats;
    }

    private function sincronizarUna(array $row,array &$stats):void
    {
        $response=$this->client->solicitud((string)$row['neuria_request_id']);if(!$response['ok'])throw new RuntimeException('API '.$response['error'].' (HTTP '.$response['http_code'].')');$data=is_array($response['data'])?$response['data']:[];$status=strtoupper((string)($data['status']??''));if($status==='')throw new RuntimeException('Respuesta de solicitud sin estado.');$progress=max(0,min(100,(int)($data['progress']??0)));$terminal=in_array($status,self::TERMINALES,true);
        $this->db->transaction(function()use($row,$data,$status,$progress,$terminal,&$stats){
            $this->db->execute('UPDATE neuria_requests SET status=?,progress=?,response_payload=?,error_message=?,completed_at=IF(?,COALESCE(completed_at,NOW()),completed_at) WHERE id=?','sissii',[$status,$progress,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$status==='ERROR'?mb_substr((string)($data['error_message']??'Error informado por Neuria'),0,500):null,$terminal?1:0,(int)$row['id']]);$stats['actualizadas']++;
            if(!in_array($status,['COMPLETADA','PARCIALMENTE_COMPLETADA'],true))return;
            foreach((array)($data['destinations']??[]) as $destination){if(!is_array($destination)||strtoupper((string)($destination['status']??''))!=='COMPLETADA')continue;$contentId=trim((string)($destination['content_id']??''));if($contentId==='')continue;$content=$this->client->contenido($contentId);if(!$content['ok']||!is_array($content['data']))throw new RuntimeException('No se pudo descargar contenido '.$contentId);$result=(new NeuriaSincronizador($this->db,$this->client))->importarContenido($content['data']);$this->db->execute("UPDATE noticias SET neuria_request_id=?,estado_revision='pendiente',imported_at=COALESCE(imported_at,NOW()) WHERE neuria_uuid=?",'ss',[(string)$row['neuria_request_id'],$contentId]);$stats['importadas']+=$result['importados'];}
        });
    }

    private function validar(array $in):array
    {
        $title=trim((string)($in['title']??''));$description=trim((string)($in['description']??''));$instructions=trim((string)($in['specific_instructions']??''));$category=trim((string)($in['category']??''));$priority=strtoupper(trim((string)($in['priority']??'NORMAL')));
        if($title===''||mb_strlen($title)>240||$description===''||mb_strlen($description)>3000)throw new InvalidArgumentException('Tema y descripción son obligatorios.');if(!in_array($priority,['BAJA','NORMAL','ALTA','URGENTE'],true))throw new InvalidArgumentException('Prioridad inválida.');
        $urls=[];foreach((array)($in['initial_urls']??[]) as $url){$url=trim((string)$url);if($url!==''&&filter_var($url,FILTER_VALIDATE_URL))$urls[]=$url;}if(count($urls)>10)throw new InvalidArgumentException('Se permiten hasta 10 URLs iniciales.');
        return ['title'=>$title,'description'=>$description,'specific_instructions'=>mb_substr($instructions,0,3000),'category'=>mb_substr($category,0,160),'priority'=>$priority,'web_search_enabled'=>(bool)($in['web_search_enabled']??false),'initial_urls'=>array_values(array_unique($urls))];
    }
}
