<?php
declare(strict_types=1);
require_once __DIR__.'/realtime.php';

/** Mismo contrato para AJAX y WebSocket. La identidad siempre la obtiene el transporte. */
final class ChatActions
{
    public function __construct(private AppDatabase $db) {}

    public function ejecutar(int $userId,string $action,array $input): array
    {
        $chat=new Chat($this->db);
        $user=$chat->usuario($userId);
        if (!$user) throw new ChatError('Iniciá sesión para acceder al chat.',401);
        $chat->permitir($user);
        $room=max(0,(int)($input['room']??0));
        switch ($action) {
            case 'state':
                $chat->presencia($userId,$room);
                return ['ok'=>true,'user'=>$user,'rooms'=>$chat->salas(),'notifications'=>$chat->notificaciones($userId),'notices'=>$chat->avisos($userId),'online'=>$room?$chat->conectados($room):[]];
            case 'avatars': return ['ok'=>true,'avatars'=>$chat->avatares()];
            case 'avatar':
                $chat->avatar($userId,(int)($input['avatar']??0));
                $result=['user'=>$chat->usuario($userId)];
                break;
            case 'messages':
                $messages=$chat->mensajes($room,max(0,(int)($input['after']??0)),max(0,(int)($input['before']??0)));
                $hidden=$this->db->all('SELECT id FROM chat_mensajes WHERE sala_id=? AND oculto=1 AND id>=? ORDER BY id LIMIT 500','ii',[$room,max(0,(int)($input['visible_from']??0))]);
                return ['ok'=>true,'messages'=>$messages,'hidden'=>array_column($hidden,'id')];
            case 'send':
                $chat->limitar('send:'.$userId,30,60);
                try { $result=$chat->enviar($userId,$room,(string)($input['text']??''),(int)($input['reply']??0),(string)($input['client_id']??'')); }
                finally { (new ChatRealtime($this->db))->notificar(); }
                return ['ok'=>true]+$result;
            case 'read':
                $this->db->execute('UPDATE chat_notificaciones SET leida=1 WHERE id=? AND usuario_id=?','ii',[(int)($input['id']??0),$userId]);
                $result=[];
                break;
            case 'report':
                $chat->limitar('report:'.$userId,10,600);
                $chat->reportar($userId,(int)($input['message']??0),(string)($input['reason']??''));
                $result=[];
                break;
            default: throw new ChatError('Acción no disponible.',404);
        }
        (new ChatRealtime($this->db))->notificar();
        return ['ok'=>true]+$result;
    }
}
