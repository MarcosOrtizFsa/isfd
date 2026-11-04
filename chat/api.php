<?php
declare(strict_types=1);
require __DIR__ . '/lib/actions.php';
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

try {
    $chat=new Chat(app_db());
    $action=(string)($_GET['action'] ?? $_POST['action'] ?? 'state');
    $read=['state','messages','avatars'];
    if (!in_array($action,$read,true)) {
        if ($_SERVER['REQUEST_METHOD']!=='POST') throw new ChatError('Método no permitido.',405);
        $token=(string)($_POST['csrf_token'] ?? '');
        if ($token==='' || !hash_equals(app_csrf_token(),$token)) throw new ChatError('La sesión venció. Recargá la página.',403);
    }
    $userId=(int)($_SESSION['chat_user_id'] ?? 0);
    if (in_array($action,['login','register'],true)) {
        // Margen para una exposición con Wi-Fi compartido; el límite por cuenta sigue siendo estricto.
        $chat->limitar('auth:'.($_SERVER['REMOTE_ADDR'] ?? 'local'),300,900);
        $chat->limitar('account:'.strtolower(trim((string)($_POST['usuario']??''))),8,900);
        if ($action==='register') {
            $chat->limitar('register:'.($_SERVER['REMOTE_ADDR'] ?? 'local'),120,3600);
            $userId=$chat->registrar($_POST);
        } else $userId=$chat->ingresar((string)($_POST['usuario']??''),(string)($_POST['password']??''));
        (new ChatRealtime(app_db()))->revocar(session_id());
        session_regenerate_id(true);
        $_SESSION['chat_user_id']=$userId;
        app_json(['ok'=>true,'user'=>$chat->usuario($userId)]);
    }
    if ($action==='logout') {
        (new ChatRealtime(app_db()))->revocar(session_id());
        unset($_SESSION['chat_user_id']);
        if ($userId) app_db()->execute('DELETE FROM chat_presencias WHERE usuario_id=?','i',[$userId]);
        app_json(['ok'=>true]);
    }
    $user=$chat->usuario($userId);
    if (!$user) {
        if ($action==='state') app_json(['ok'=>true,'user'=>null]);
        throw new ChatError('Iniciá sesión para acceder al chat.',401);
    }
    $chat->permitir($user);
    if ($action==='ws-token') {
        $chat->limitar('ws-token:'.$userId,60,300);
        $config=chat_ws_config();
        app_json(['ok'=>true,'url'=>$config['url'],'token'=>$config['url']!==''?(new ChatRealtime(app_db()))->emitir($userId,session_id()):null]);
    }
    // Las consultas de actualización no retienen el bloqueo de sesión de PHP.
    session_write_close();
    app_json((new ChatActions(app_db()))->ejecutar($userId,$action,$_SERVER['REQUEST_METHOD']==='POST'?$_POST:$_GET));
} catch (ChatError $e) { app_json(['ok'=>false,'message'=>$e->getMessage()],$e->status); }
catch (Throwable $e) {
    error_log('Chat: '.$e->getMessage());
    app_json(['ok'=>false,'message'=>'No pudimos completar la solicitud. Volvé a intentar en unos instantes.'],500);
}
