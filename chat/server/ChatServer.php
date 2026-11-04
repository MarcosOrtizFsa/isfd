<?php
declare(strict_types=1);

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;

/** Transporte únicamente: las operaciones y permisos viven en ChatActions. */
final class ChatServer implements MessageComponentInterface
{
    private SplObjectStorage $connections;
    private ChatRealtime $realtime;
    private ChatActions $actions;
    private int $revision;

    public function __construct(private AppDatabase $db,private LoopInterface $loop,private array $origins)
    {
        $this->connections=new SplObjectStorage();
        $this->realtime=new ChatRealtime($db);
        $this->actions=new ChatActions($db);
        $this->revision=$this->realtime->revision();
        // Una consulta global detecta también mensajes AJAX y decisiones del administrador.
        $loop->addPeriodicTimer(0.5,function():void {
            try {
                $revision=$this->realtime->revision();
                if ($revision!==$this->revision) { $this->revision=$revision; $this->avisar(); }
            } catch (Throwable $e) { $this->detenerPorError($e); }
        });
        $loop->addPeriodicTimer(15,function():void {
            foreach ($this->connections as $conn) {
                $state=$this->connections[$conn];
                try {
                    if (!$state['user'] || !$this->realtime->vigente($state['hash'],$state['user']) || microtime(true)-$state['last']>75) { $conn->close(); continue; }
                    $user=(new Chat($this->db))->usuario($state['user']);
                    if (!$user || $user['bloqueado']) $conn->close();
                } catch (Throwable $e) { $this->detenerPorError($e); return; }
            }
        });
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $origin=$conn->httpRequest->getHeaderLine('Origin');
        if (!in_array($origin,$this->origins,true) || $conn->httpRequest->getUri()->getPath()!=='/' || count($this->connections)>=200) { $conn->close(); return; }
        $this->connections[$conn]=['user'=>0,'hash'=>'','last'=>microtime(true),'window'=>time(),'count'=>0,'room'=>null];
        // Un socket sin autenticación no puede mantener recursos indefinidamente.
        $this->loop->addTimer(5,function() use($conn):void {
            if ($this->connections->contains($conn) && !$this->connections[$conn]['user']) $conn->close();
        });
    }

    public function onMessage(ConnectionInterface $from,$msg)
    {
        if (!$this->connections->contains($from)) { $from->close(); return; }
        $state=$this->connections[$from];
        $now=time();
        if ($now-$state['window']>=10) { $state['window']=$now; $state['count']=0; }
        $state['count']++;
        $state['last']=microtime(true);
        $this->connections[$from]=$state;
        if (strlen((string)$msg)>16384 || $state['count']>80) { $from->close(); return; }
        $id=null;
        try {
            $input=json_decode((string)$msg,true,32,JSON_THROW_ON_ERROR);
            if (!is_array($input)) throw new ChatError('Formato inválido.');
            if (!$state['user']) {
                if (($input['type']??'')!=='auth') throw new ChatError('Autenticación requerida.',401);
                $credential=$this->realtime->consumir((string)($input['token']??''));
                $chat=new Chat($this->db);
                $user=$chat->usuario($credential['user']);
                if (!$user) throw new ChatError('Cuenta inexistente.',401);
                $chat->permitir($user);
                $state['user']=$credential['user']; $state['hash']=$credential['hash'];
                $this->connections[$from]=$state;
                $this->send($from,['type'=>'ready']);
                return;
            }
            if (!$this->realtime->vigente($state['hash'],$state['user'])) throw new ChatError('La conexión venció. Volvé a conectar.',401);
            $id=$input['id']??null;
            if (!is_int($id) || $id<1 || ($input['type']??'')!=='request' || !is_array($input['data']??null)) throw new ChatError('Solicitud inválida.');
            $action=(string)($input['action']??'');
            $result=$this->actions->ejecutar($state['user'],$action,$input['data']);
            $this->send($from,['type'=>'response','id'=>$id,'status'=>200,'data'=>$result]);
            if ($action==='state') {
                $room=(int)($input['data']['room']??0);
                if ($room!==$state['room']) {
                    $state['room']=$room; $this->connections[$from]=$state;
                    $this->avisar();
                }
            }
            if (in_array($action,['send','avatar','read','report'],true)) {
                $this->revision=$this->realtime->revision();
                $this->avisar();
            }
        } catch (ChatError $e) {
            $this->send($from,['type'=>'response','id'=>$id,'status'=>$e->status,'data'=>['ok'=>false,'message'=>$e->getMessage()]]);
            if (!$state['user'] || $e->status===401 || ($e->status===403 && str_contains($e->getMessage(),'cuenta'))) $from->close();
        } catch (JsonException $e) { $from->close(); }
        catch (Throwable $e) {
            error_log('WebSocket: '.$e->getMessage());
            $this->send($from,['type'=>'response','id'=>$id,'status'=>500,'data'=>['ok'=>false,'message'=>'No se pudo procesar la solicitud.']]);
            $from->close();
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        if ($this->connections->contains($conn)) $this->connections->detach($conn);
        // No eliminar presencia: el mismo usuario puede estar conectado por AJAX u otra pestaña.
    }

    public function onError(ConnectionInterface $conn,Exception $e)
    {
        error_log('WebSocket conexión: '.$e->getMessage());
        $conn->close();
    }

    private function avisar(): void
    {
        foreach ($this->connections as $conn) if ($this->connections[$conn]['user']) $this->send($conn,['type'=>'changed']);
    }

    private function send(ConnectionInterface $conn,array $data): void
    {
        $conn->send(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    }

    private function detenerPorError(Throwable $error): void
    {
        // Si cae la BD, cerrar conexiones permite que todos pasen a AJAX sin aparentar conexión sana.
        error_log('WebSocket detenido: '.$error->getMessage());
        foreach ($this->connections as $conn) $conn->close();
        $this->loop->stop();
    }
}
