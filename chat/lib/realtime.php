<?php
declare(strict_types=1);
require_once __DIR__.'/chat.php';

/** Configuración exclusiva del transporte; no depende de la integración NEURIA. */
function chat_ws_config(): array
{
    static $config;
    if ($config!==null) return $config;
    $values=[];
    $file=dirname(__DIR__,2).'/.env';
    if (is_readable($file)) foreach (file($file,FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^\s*(CHAT_WS_[A-Z_]+)\s*=\s*(.*?)\s*$/',$line,$match)) $values[$match[1]]=trim($match[2],"\"'");
    }
    $get=static fn(string $key,string $default):string => getenv($key)!==false ? (string)getenv($key) : ($values[$key]??$default);
    $host=parse_url('http://'.($_SERVER['HTTP_HOST']??''),PHP_URL_HOST);
    $local=in_array($host,['localhost','127.0.0.1'],true);
    $https=!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off';
    $url=$get('CHAT_WS_URL',$local && !$https?'ws://'.$host.':8087':'');
    // Un portal HTTPS nunca intenta abrir un socket sin cifrar.
    if ($url!=='' && (!in_array(parse_url($url,PHP_URL_SCHEME),['ws','wss'],true) || ($https && !str_starts_with($url,'wss://')))) $url='';
    return $config=[
        'url'=>$url,
        'listen'=>$get('CHAT_WS_LISTEN','127.0.0.1:8087'),
        'origins'=>array_filter(array_map('trim',explode(',',$get('CHAT_WS_ORIGINS','http://localhost,http://127.0.0.1')))),
    ];
}

final class ChatRealtime
{
    public function __construct(private AppDatabase $db) {}

    /** Credencial de un solo uso: el servidor almacena únicamente su hash. */
    public function emitir(int $user,string $session): string
    {
        $token=bin2hex(random_bytes(32));
        $this->db->execute('DELETE FROM chat_ws_tokens WHERE valid_until<NOW() OR (consumed=0 AND expires_at<NOW())');
        $this->db->insert('INSERT INTO chat_ws_tokens (token_hash,usuario_id,session_hash,expires_at,valid_until) VALUES (?,?,?,DATE_ADD(NOW(),INTERVAL 60 SECOND),DATE_ADD(NOW(),INTERVAL 1 HOUR))','sis',[hash('sha256',$token),$user,hash('sha256',$session)]);
        return $token;
    }

    public function consumir(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D',$token)) throw new ChatError('Credencial de conexión inválida.',401);
        $hash=hash('sha256',$token);
        return $this->db->transaction(function() use($hash) {
            $row=$this->db->one('SELECT * FROM chat_ws_tokens WHERE token_hash=? AND consumed=0 AND expires_at>NOW() FOR UPDATE','s',[$hash]);
            if (!$row) throw new ChatError('La credencial de conexión venció o ya se utilizó.',401);
            $this->db->execute('UPDATE chat_ws_tokens SET consumed=1 WHERE token_hash=?','s',[$hash]);
            return ['user'=>(int)$row['usuario_id'],'hash'=>$hash];
        });
    }

    public function vigente(string $hash,int $user): bool
    {
        return $this->db->one('SELECT usuario_id FROM chat_ws_tokens WHERE token_hash=? AND usuario_id=? AND consumed=1 AND valid_until>NOW()','si',[$hash,$user])!==null;
    }

    public function revocar(string $session): void
    {
        try { $this->db->execute('DELETE FROM chat_ws_tokens WHERE session_hash=?','s',[hash('sha256',$session)]); }
        catch (mysqli_sql_exception $e) { if ($e->getCode()!==1146) throw $e; }
    }

    public function revision(): int
    {
        return (int)$this->db->one('SELECT revision FROM chat_ws_revision WHERE id=1')['revision'];
    }

    public function notificar(): void
    {
        // HTTP sigue funcionando si aún no se instaló la migración opcional WebSocket.
        try { $this->db->execute('UPDATE chat_ws_revision SET revision=revision+1 WHERE id=1'); }
        catch (mysqli_sql_exception $e) { if ($e->getCode()!==1146) throw $e; }
    }
}
