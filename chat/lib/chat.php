<?php
declare(strict_types=1);
require_once __DIR__ . '/../../lib/app.php';

final class ChatError extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 422) { parent::__construct($message); }
}

/** Datos y operaciones del chat. Las cuentas nunca conceden acceso a mipanel. */
final class Chat
{
    public const REGLAS_VERSION = '2026-11-03';
    public function __construct(private AppDatabase $db) {}

    public function usuario(int $id): ?array
    {
        return $this->db->one('SELECT id,usuario,avatar_id,bloqueado,suspendido_hasta FROM chat_usuarios WHERE id=?', 'i', [$id]);
    }

    public function permitir(array $user, bool $escritura = false): void
    {
        if ($user['bloqueado']) throw new ChatError('Tu cuenta está suspendida. Contactá a la institución para solicitar una revisión.', 403);
        if ($escritura && $user['suspendido_hasta'] && strtotime($user['suspendido_hasta']) > time()) {
            throw new ChatError('Podrás volver a escribir a las ' . date('H:i', strtotime($user['suspendido_hasta'])) . '. Revisá las reglas de convivencia.', 403);
        }
    }

    /** Contador atómico persistente, compartido entre sesiones del mismo origen. */
    public function limitar(string $key, int $max, int $seconds): void
    {
        $hash = hash('sha256', $key . ':' . (int) floor(time() / $seconds));
        $this->db->execute('INSERT INTO chat_limites (clave,intentos,vence_at) VALUES (?,1,DATE_ADD(NOW(),INTERVAL ? SECOND)) ON DUPLICATE KEY UPDATE intentos=intentos+1', 'si', [$hash,$seconds]);
        if ((int)$this->db->one('SELECT intentos FROM chat_limites WHERE clave=?', 's', [$hash])['intentos'] > $max) throw new ChatError('Demasiados intentos. Esperá unos minutos antes de volver a intentar.', 429);
        $this->db->execute('DELETE FROM chat_limites WHERE vence_at<NOW() LIMIT 100');
    }

    public function registrar(array $input): int
    {
        $name = strtolower(trim((string)($input['usuario'] ?? '')));
        $email = strtolower(trim((string)($input['email'] ?? '')));
        $password = (string)($input['password'] ?? '');
        if (!preg_match('/^[a-z][a-z0-9_]{2,23}$/D', $name)) throw new ChatError('El usuario debe tener entre 3 y 24 letras, números o guion bajo, y comenzar con una letra.');
        if (strlen($email)>190 || !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new ChatError('Ingresá un correo electrónico válido.');
        if (strlen($password)<10 || strlen($password)>72) throw new ChatError('Usá una contraseña de entre 10 y 72 bytes (hasta 72 caracteres simples).');
        if (($input['reglas'] ?? '') !== '1') throw new ChatError('Debés aceptar las reglas de convivencia.');
        try {
            return $this->db->insert('INSERT INTO chat_usuarios (email,usuario,password_hash,reglas_version,reglas_aceptadas_at) VALUES (?,?,?,?,NOW())', 'ssss', [$email,$name,password_hash($password,PASSWORD_DEFAULT),self::REGLAS_VERSION]);
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode()===1062) throw new ChatError('El correo o nombre de usuario ya está registrado.',409);
            throw $e;
        }
    }

    public function ingresar(string $name, string $password): int
    {
        $name = strtolower(trim($name));
        $row = $this->db->one('SELECT * FROM chat_usuarios WHERE usuario=? OR email=? LIMIT 1','ss',[$name,$name]);
        // Hash constante para que los usuarios inexistentes también ejecuten password_verify.
        $valid = password_verify($password,$row['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (!$row || !$valid) throw new ChatError('Usuario o contraseña incorrectos.',401);
        $this->permitir($row);
        return (int)$row['id'];
    }

    public function avatares(): array
    {
        return $this->db->all('SELECT a.id,u.id AS propietario FROM chat_avatares a LEFT JOIN chat_usuarios u ON u.avatar_id=a.id ORDER BY a.id');
    }

    public function avatar(int $user, int $avatar): void
    {
        if (!$this->db->one('SELECT id FROM chat_avatares WHERE id=?','i',[$avatar])) throw new ChatError('Avatar inválido.');
        try { $this->db->execute('UPDATE chat_usuarios SET avatar_id=? WHERE id=?','ii',[$avatar,$user]); }
        catch (mysqli_sql_exception $e) { if ($e->getCode()===1062) throw new ChatError('Otra persona eligió ese avatar. Seleccioná uno disponible.',409); throw $e; }
    }

    public function sala(int $id): array
    {
        $room = $this->db->one('SELECT * FROM chat_salas WHERE id=? AND activa=1','i',[$id]);
        if (!$room) throw new ChatError('La sala no está disponible.',404);
        return $room;
    }

    public function presencia(int $user, int $room): void
    {
        if ($room) $this->sala($room);
        $this->db->execute('INSERT INTO chat_presencias (usuario_id,sala_id,visto_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE sala_id=VALUES(sala_id),visto_at=NOW()','ii',[$user,$room?:null]);
    }

    public function salas(): array
    {
        return $this->db->all('SELECT s.*,(SELECT COUNT(*) FROM chat_presencias p JOIN chat_usuarios u ON u.id=p.usuario_id WHERE p.sala_id=s.id AND p.visto_at>DATE_SUB(NOW(),INTERVAL 60 SECOND) AND u.bloqueado=0) conectados FROM chat_salas s WHERE activa=1 ORDER BY s.id');
    }

    public function mensajes(int $room, int $after, int $before): array
    {
        $this->sala($room);
        $where = 'm.sala_id=?'; $params=[$room]; $types='i';
        if ($before>0) { $where.=' AND m.id<?'; $params[]=$before; $types.='i'; }
        elseif ($after>0) { $where.=' AND m.id>?'; $params[]=$after; $types.='i'; }
        $order = $after>0 && !$before ? 'ASC' : 'DESC';
        $rows = $this->db->all("SELECT m.id,m.sala_id,m.usuario_id,m.respuesta_id,IF(m.oculto=1,'Mensaje retirado por moderación.',m.texto) texto,m.oculto,m.created_at,u.usuario,u.avatar_id,IF(r.oculto=1,'Mensaje retirado.',LEFT(r.texto,180)) respuesta_texto,ru.usuario respuesta_usuario FROM chat_mensajes m JOIN chat_usuarios u ON u.id=m.usuario_id LEFT JOIN chat_mensajes r ON r.id=m.respuesta_id LEFT JOIN chat_usuarios ru ON ru.id=r.usuario_id WHERE $where ORDER BY m.id $order LIMIT 50",$types,$params);
        return $order==='DESC' ? array_reverse($rows) : $rows;
    }

    public function conectados(int $room): array
    {
        return $this->db->all('SELECT u.id,u.usuario,u.avatar_id FROM chat_presencias p JOIN chat_usuarios u ON u.id=p.usuario_id WHERE p.sala_id=? AND p.visto_at>DATE_SUB(NOW(),INTERVAL 60 SECOND) AND u.bloqueado=0 ORDER BY u.usuario LIMIT 100','i',[$room]);
    }

    /** Reglas conservadoras: nunca sancionar una palabra aislada o una cita educativa. */
    public static function moderar(string $text): ?string
    {
        $normalized=mb_strtolower($text);
        if (preg_match('/(analiz|ejemplo|cita|signific|denunci|me dijo|me dijeron|no digas|no hay que)/u',$normalized)) return null;
        if (preg_match('/\bte voy a (matar|violar|golpear)\b/u',$normalized)) return 'Posible amenaza directa. El mensaje requiere revisión humana.';
        if (preg_match('/@[a-z][a-z0-9_]{2,23}\s+(?:sos|eres)\s+(?:un[a]?\s+)?(?:idiota|imbécil|estúpido|estúpida)\b/u',$normalized)) return 'Posible agresión dirigida a un participante. Reformulá el mensaje.';
        return null;
    }

    public function enviar(int $user, int $room, string $text, int $reply, string $clientId): array
    {
        $text=trim($text);
        if ($text==='' || mb_strlen($text)>2000) throw new ChatError('Escribí entre 1 y 2000 caracteres.');
        if (!preg_match('/^[a-zA-Z0-9-]{16,36}$/D',$clientId)) throw new ChatError('Identificador de envío inválido.');
        $this->sala($room);
        // El bloqueo por usuario serializa envíos y sanciones; client_id vuelve idempotente el reintento.
        $result=$this->db->transaction(function() use($user,$room,$text,$reply,$clientId) {
            $account=$this->db->one('SELECT * FROM chat_usuarios WHERE id=? FOR UPDATE','i',[$user]);
            if (!$account) throw new ChatError('Iniciá sesión.',401);
            $this->permitir($account,true);
            if (!$account['avatar_id']) throw new ChatError('Elegí tu avatar antes de escribir.');
            $existing=$this->db->one('SELECT id FROM chat_mensajes WHERE usuario_id=? AND client_id=?','is',[$user,$clientId]);
            if ($existing) return ['id'=>(int)$existing['id']];
            $last=$this->db->one('SELECT texto,created_at FROM chat_mensajes WHERE usuario_id=? ORDER BY id DESC LIMIT 1','i',[$user]);
            if ($last && time()-strtotime($last['created_at'])<2) throw new ChatError('Esperá dos segundos entre mensajes.',429);
            if ($last && $last['texto']===$text && time()-strtotime($last['created_at'])<60) throw new ChatError('Ese mensaje ya se envió. Evitá repetirlo.',429);
            if ($reply && !$this->db->one('SELECT id FROM chat_mensajes WHERE id=? AND sala_id=? AND oculto=0','ii',[$reply,$room])) throw new ChatError('El mensaje al que respondés ya no está disponible.');
            $reason=self::moderar($text);
            if ($reason) {
                $this->db->insert("INSERT INTO chat_reportes (usuario_id,texto,motivo,origen) VALUES (?,?,?,'automatico')",'iss',[$user,$text,$reason]);
                $count=(int)$this->db->one("SELECT COUNT(*) n FROM chat_reportes WHERE usuario_id=? AND origen='automatico' AND estado<>'descartado' AND created_at>DATE_SUB(NOW(),INTERVAL 1 HOUR)",'i',[$user])['n'];
                if ($count>=3) $this->db->execute('UPDATE chat_usuarios SET suspendido_hasta=DATE_ADD(NOW(),INTERVAL 10 MINUTE) WHERE id=?','i',[$user]);
                return ['advertencia'=>$reason.($count>=3?' Se aplica una pausa de 10 minutos, revisable por un administrador.':' El mensaje no fue publicado.')];
            }
            $id=$this->db->insert('INSERT INTO chat_mensajes (sala_id,usuario_id,respuesta_id,texto,client_id) VALUES (?,?,?,?,?)','iiiss',[$room,$user,$reply?:null,$text,$clientId]);
            preg_match_all('/(?<![a-z0-9_])@([a-z][a-z0-9_]{2,23})\b/i',$text,$matches);
            $targets=[];
            foreach (array_unique(array_map('strtolower',$matches[1])) as $name) {
                $target=$this->db->one('SELECT id FROM chat_usuarios WHERE usuario=? AND bloqueado=0','s',[$name]);
                if ($target) $targets[]=(int)$target['id'];
            }
            if ($reply) $targets[]=(int)$this->db->one('SELECT usuario_id FROM chat_mensajes WHERE id=?','i',[$reply])['usuario_id'];
            foreach (array_unique($targets) as $target) if ($target!==$user) $this->db->execute('INSERT IGNORE INTO chat_notificaciones (usuario_id,mensaje_id) VALUES (?,?)','ii',[$target,$id]);
            return ['id'=>$id];
        });
        if (isset($result['advertencia'])) throw new ChatError($result['advertencia']);
        return $result;
    }

    public function notificaciones(int $user): array
    {
        return $this->db->all("SELECT n.id,n.mensaje_id,m.sala_id,u.usuario,LEFT(m.texto,160) texto FROM chat_notificaciones n JOIN chat_mensajes m ON m.id=n.mensaje_id JOIN chat_usuarios u ON u.id=m.usuario_id WHERE n.usuario_id=? AND n.leida=0 AND m.oculto=0 ORDER BY n.id DESC LIMIT 30",'i',[$user]);
    }

    public function reportar(int $user, int $message, string $reason): void
    {
        $reason=trim($reason);
        if (mb_strlen($reason)<5 || mb_strlen($reason)>500) throw new ChatError('Describí el motivo entre 5 y 500 caracteres.');
        $row=$this->db->one('SELECT * FROM chat_mensajes WHERE id=? AND oculto=0','i',[$message]);
        if (!$row) throw new ChatError('Mensaje no disponible.',404);
        $this->db->execute("INSERT IGNORE INTO chat_reportes (mensaje_id,usuario_id,denunciante_id,texto,motivo,origen) VALUES (?,?,?,?,?,'participante')",'iiiss',[$message,$row['usuario_id'],$user,$row['texto'],$reason]);
    }

    public function moderacion(int $admin, int $user, int $report, string $action, string $reason): void
    {
        if (!in_array($action,['advertir','suspender','expulsar','rehabilitar','ocultar','descartar'],true)) throw new ChatError('Acción inválida.');
        if (mb_strlen(trim($reason))<5 || mb_strlen($reason)>500) throw new ChatError('Indicá el motivo de la decisión (5 a 500 caracteres).');
        $this->db->transaction(function() use($admin,$user,$report,$action,$reason) {
            if (!$this->db->one('SELECT id FROM chat_usuarios WHERE id=? FOR UPDATE','i',[$user])) throw new ChatError('Usuario inexistente.');
            $row=$report ? $this->db->one('SELECT * FROM chat_reportes WHERE id=? AND usuario_id=?','ii',[$report,$user]):null;
            if ($report && !$row) throw new ChatError('Reporte inválido.');
            if ($action==='suspender') $this->db->execute('UPDATE chat_usuarios SET suspendido_hasta=DATE_ADD(NOW(),INTERVAL 1 DAY) WHERE id=?','i',[$user]);
            if ($action==='expulsar') $this->db->execute('UPDATE chat_usuarios SET bloqueado=1 WHERE id=?','i',[$user]);
            if ($action==='rehabilitar') $this->db->execute('UPDATE chat_usuarios SET bloqueado=0,suspendido_hasta=NULL WHERE id=?','i',[$user]);
            if ($action==='ocultar') {
                if (!$row || !$row['mensaje_id']) throw new ChatError('Este reporte no corresponde a un mensaje publicado.');
                $this->db->execute('UPDATE chat_mensajes SET oculto=1 WHERE id=?','i',[$row['mensaje_id']]);
            }
            if ($row) $this->db->execute('UPDATE chat_reportes SET estado=? WHERE id=?','si',[$action==='descartar'?'descartado':'revisado',$report]);
            $this->db->insert('INSERT INTO chat_auditoria (administrador_id,usuario_id,reporte_id,accion,motivo) VALUES (?,?,?,?,?)','iiiss',[$admin,$user,$report?:null,$action,trim($reason)]);
        });
    }

    public function avisos(int $user): array
    {
        return $this->db->all('SELECT id,accion,motivo,created_at FROM chat_auditoria WHERE usuario_id=? ORDER BY id DESC LIMIT 3','i',[$user]);
    }
}
