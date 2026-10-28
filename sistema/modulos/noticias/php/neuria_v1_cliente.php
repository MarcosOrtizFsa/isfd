<?php
declare(strict_types=1);
require_once __DIR__.'/neuria_v1_config.php';

final class NeuriaV1Cliente
{
    private array $config;
    public function __construct(?array $config=null){$this->config=$config??neuria_v1_configuracion();if(isset($this->config['url']))$this->config['url']=preg_replace('#/api/v1/?$#i','',rtrim((string)$this->config['url'],'/'));}
    public function estaConfigurado():bool{return(bool)($this->config['configurada']??false);}
    public function limite():int{return(int)($this->config['limit']??50);}
    public function identidad():array{return $this->solicitar('/api/v1/me');}
    public function crearSolicitud(array $payload):array{return $this->solicitar('/api/v1/requests',[],'POST',$payload);}
    public function solicitud(string $uuid):array{return preg_match('/^[a-zA-Z0-9-]{8,64}$/',$uuid)?$this->solicitar('/api/v1/requests/'.rawurlencode($uuid)):$this->error('invalid_uuid',422);}
    public function contenidos(?string $cursor=null,?int $limit=null,array $filtros=[]):array{return $this->solicitar('/api/v1/contents',array_filter(['cursor'=>$cursor,'limit'=>$limit??$this->limite(),'channel_id'=>$filtros['channel_id']??null,'available_from'=>$filtros['available_from']??null,'id'=>$filtros['id']??null],static fn($v)=>$v!==null&&$v!==''));}
    public function contenido(string $uuid):array{return preg_match('/^[a-zA-Z0-9-]{8,64}$/',$uuid)?$this->solicitar('/api/v1/contents/'.rawurlencode($uuid)):$this->error('invalid_uuid',422);}
    public function cambios(?string $cursor=null,?int $limit=null,?string $channelId=null):array{return $this->solicitar('/api/v1/changes',array_filter(['cursor'=>$cursor,'limit'=>$limit??$this->limite(),'channel_id'=>$channelId],static fn($v)=>$v!==null&&$v!==''));}
    private function solicitar(string $ruta,array $parametros=[],string $metodo='GET',?array $payload=null):array
    {
        if(!$this->estaConfigurado())return $this->error('not_configured',0);
        $base=(string)$this->config['url'];$host=strtolower((string)parse_url($base,PHP_URL_HOST));$local=in_array($host,['localhost','127.0.0.1','::1'],true);
        if(!$local&&strtolower((string)parse_url($base,PHP_URL_SCHEME))!=='https')return $this->error('https_required',0);
        $url=$base.$ruta.($parametros?'?'.http_build_query($parametros,'','&',PHP_QUERY_RFC3986):'');$max=max(0,(int)($this->config['max_retries']??2));
        for($intento=0;$intento<=$max;$intento++){
            $headers=['Authorization: Bearer '.$this->config['token'],'Accept: application/json'];
            if($payload!==null)$headers[]='Content-Type: application/json';
            $curl=curl_init($url);curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>min(10,(int)$this->config['timeout']),CURLOPT_TIMEOUT=>(int)$this->config['timeout'],CURLOPT_SSL_VERIFYPEER=>!$local,CURLOPT_SSL_VERIFYHOST=>$local?0:2,CURLOPT_HTTPHEADER=>$headers,CURLOPT_USERAGENT=>'ISFD-Neuria-Sync/2.0',CURLOPT_CUSTOMREQUEST=>$metodo]);
            if($payload!==null)curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            $body=curl_exec($curl);$http=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);$connection=curl_errno($curl)!==0||$body===false;curl_close($curl);
            $temporary=$connection||in_array($http,[429,500,502,503,504],true);if($temporary&&$intento<$max){usleep((int)(200000*(2**$intento)));continue;}
            if($connection)return $this->error('connection_error',0,$intento+1);
            if($http<200||$http>=300){$map=[401=>'invalid_credentials',403=>'forbidden',404=>'not_found',422=>'invalid_request',429=>'rate_limit_exceeded'];return $this->error($map[$http]??'temporary_error',$http,$intento+1);}
            $json=json_decode((string)$body,true);if(!is_array($json))return $this->error('invalid_response',$http,$intento+1);
            return ['ok'=>true,'data'=>$json['data']??$json,'meta'=>is_array($json['meta']??null)?$json['meta']:[],'http_code'=>$http,'error'=>null,'attempts'=>$intento+1];
        }
        return $this->error('temporary_error',0,$max+1);
    }
    private function error(string $error,int $http,int $attempts=0):array{return ['ok'=>false,'data'=>null,'meta'=>[],'http_code'=>$http,'error'=>$error,'attempts'=>$attempts];}
}
