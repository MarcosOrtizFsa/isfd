<?php
declare(strict_types=1);
require __DIR__.'/lib/chat.php';
header('Cache-Control: no-store');
header('X-Frame-Options: SAMEORIGIN');
?>
<!doctype html>
<html lang="es-AR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?=app_escape(app_csrf_token())?>">
    <title>Comunidad · Félix Atilio Cabrera</title>
    <link rel="icon" href="../image/logo.png">
    <link rel="stylesheet" href="assets/chat.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="./"><img src="../image/logo.png" alt="Escudo del instituto"><span>FÉLIX A. CABRERA<small>COMUNIDAD · CHAT INSTITUCIONAL</small></span></a>
    <nav aria-label="Navegación"><button class="text-button" data-dialog="rules-dialog">Convivencia</button><a href="../" target="_blank" rel="noopener">Ir al portal ↗</a></nav>
</header>
<div id="status" class="status" role="status" aria-live="polite"></div>
<main>
    <section id="welcome" class="welcome" hidden>
        <div class="welcome-story">
            <span class="eyebrow">UN ESPACIO PARA ENCONTRARNOS</span>
            <h1>Las buenas ideas <br>empiezan con una <br><em>conversación.</em></h1>
            <p>Conectate con estudiantes, docentes y visitantes. Compartí lo que sabés, preguntá y descubrí tu próxima comunidad.</p>
            <div class="mascot-line"><img src="mascota.php?id=1" alt="Búho de Inglés"><img src="mascota.php?id=2" alt="Robot de Software"><img src="mascota.php?id=5" alt="Robot de Inteligencia Artificial"></div>
            <p class="institution">Instituto Superior de Formación Docente Continua y Técnica<br><strong>«Félix Atilio Cabrera»</strong> · Formosa</p>
        </div>
        <div class="auth-card">
            <span class="eyebrow">TU COMUNIDAD TE ESPERA</span><h2>¡Qué bueno verte!</h2>
            <div class="auth-tabs"><button id="login-tab" class="selected" type="button">Ingresar</button><button id="register-tab" type="button">Crear cuenta</button></div>
            <form id="login-form">
                <label for="login-user">Usuario o correo</label><input id="login-user" name="usuario" autocomplete="username" maxlength="190" placeholder="Tu usuario o correo electrónico" required>
                <label for="login-password">Contraseña</label><input id="login-password" name="password" type="password" autocomplete="current-password" maxlength="72" required>
                <button class="primary full" type="submit">Entrar a la comunidad →</button>
            </form>
            <form id="register-form" hidden>
                <label for="register-email">Correo electrónico</label><input id="register-email" name="email" type="email" autocomplete="email" maxlength="190" placeholder="vos@ejemplo.com" required>
                <label for="register-user">Nombre de usuario</label><input id="register-user" name="usuario" autocomplete="username" pattern="[a-zA-Z][a-zA-Z0-9_]{2,23}" maxlength="24" placeholder="Ej.: julia_cabrera" aria-describedby="username-help" required><small id="username-help">Entre 3 y 24 letras, números o guion bajo. Empieza con una letra.</small>
                <label for="register-password">Contraseña</label><input id="register-password" name="password" type="password" autocomplete="new-password" minlength="10" maxlength="72" placeholder="Al menos 10 caracteres" required>
                <label class="check"><input type="checkbox" name="reglas" value="1" required> Acepto las reglas de convivencia y que mis mensajes sean visibles para los participantes y moderadores.</label>
                <button class="text-button" data-dialog="rules-dialog" type="button">Leer las reglas de convivencia</button>
                <button class="primary full" type="submit">Crear mi cuenta →</button>
            </form>
            <p class="privacy">Este chat conecta personas. Las mascotas representan a las salas; no son asistentes de IA. Evitá compartir datos personales en los mensajes.</p>
        </div>
    </section>

    <section id="community" hidden>
        <div class="community-head"><div><span class="eyebrow">APRENDER TAMBIÉN ES COMPARTIR</span><h1>Hola, <span id="greeting"></span><span class="gold">.</span></h1><p>Elegí tu espacio y sumate a la conversación.</p></div><div class="profile-actions"><button id="avatar-open" class="profile"><img id="my-avatar" alt="Mi avatar"><span>Mi avatar</span></button><button id="notifications-open" class="secondary">Avisos <span id="notification-count">0</span></button><button id="logout" class="text-button">Salir</button></div></div>
        <div id="account-notices" class="account-notices"></div>
        <section id="lobby"><div class="section-label"><h2>Encontrá tu sala</h2><span>5 espacios · una comunidad</span></div><div id="rooms" class="rooms"></div><p class="lobby-note">Conversaciones entre personas, ideas que nos acercan. Elegí una sala según tus intereses.</p></section>
        <section id="conversation" class="conversation" hidden>
            <div class="chat-main">
                <header class="room-header"><button id="back-rooms" class="secondary" aria-label="Volver a las salas">← Salas</button><div><h2 id="room-title"></h2><small id="connection-status">Conectando…</small></div><section class="room-tools"><button id="room-notifications-open" class="text-button">Avisos <span id="room-notification-count">0</span></button><button id="sound-toggle" class="text-button" aria-pressed="false">Sonido: no</button></section></header>
                <details class="mobile-online"><summary>Participantes conectados <span id="mobile-online-count"></span></summary><div id="mobile-online-list"></div></details>
                <div id="messages" class="messages" aria-label="Mensajes de la sala"><button id="older" class="text-button">Ver mensajes anteriores</button><div id="message-list" role="log" aria-live="polite" aria-relevant="additions"></div></div>
                <button id="new-messages" class="new-messages" hidden>↓ Nuevos mensajes</button>
                <form id="message-form" class="composer">
                    <div id="reply-preview" hidden><span></span><button type="button" class="text-button" id="reply-cancel" aria-label="Cancelar respuesta">✕</button></div>
                    <label class="sr-only" for="message-text">Tu mensaje</label><div class="compose-row"><textarea id="message-text" maxlength="2000" rows="2" placeholder="Escribí algo… Usá @usuario para mencionar" required></textarea><button type="submit" class="primary">Enviar ↑</button></div>
                    <small>Enter para enviar · Shift + Enter para una nueva línea · <span id="characters">0</span>/2000</small>
                </form>
            </div>
            <aside class="room-aside"><span class="eyebrow">EN ESTA SALA</span><h3>Conectados <span id="online-count"></span></h3><div id="online"></div><div class="aside-tip"><strong>Un buen lugar empieza por vos.</strong><p>Escuchá, compartí y respetá. Si algo no está bien, podés reportar el mensaje.</p><button class="text-button" data-dialog="rules-dialog">Ver convivencia →</button></div></aside>
        </section>
    </section>
</main>
<footer class="page-footer">Instituto Superior de Formación Docente Continua y Técnica «Félix Atilio Cabrera» <span>Hecho para conectar nuestra comunidad.</span></footer>

<dialog id="rules-dialog"><div class="dialog-head"><h2>Convivir también se aprende</h2><button class="dialog-close" aria-label="Cerrar">✕</button></div><p>Queremos un espacio educativo, abierto y respetuoso.</p><ol><li>Respetá a todos los participantes y sus opiniones.</li><li>No discrimines ni hagas comentarios despectivos.</li><li>Evitá insultos y agresiones dirigidas.</li><li>No amenaces ni acoses a otras personas.</li><li>No envíes spam ni repitas mensajes.</li><li>Conversá sobre la temática de cada sala.</li><li>Compartí conocimientos y ayudá a quienes recién empiezan.</li></ol><p>Los controles automáticos pueden equivocarse. Los reportes son revisados por el administrador; las expulsiones permanentes son decisiones humanas y se pueden revisar con la institución.</p><p>No publiques teléfonos, contraseñas ni información privada. Los mensajes se guardan en la base institucional y pueden ser revisados por moderadores.</p><button class="primary dialog-close">Entendido</button></dialog>
<dialog id="avatar-dialog"><div class="dialog-head"><div><span class="eyebrow">DALE IDENTIDAD A TU PERFIL</span><h2>Elegí tu compañero</h2></div><button class="dialog-close" aria-label="Cerrar">✕</button></div><p>Cada avatar pertenece a una sola cuenta. Los ocupados aparecen deshabilitados.</p><div id="avatar-grid" class="avatar-grid"></div><div class="avatar-pages"><button id="avatars-prev" class="secondary">← Anteriores</button><span id="avatars-page"></span><button id="avatars-next" class="secondary">Siguientes →</button></div></dialog>
<dialog id="notifications-dialog"><div class="dialog-head"><h2>Tus menciones y respuestas</h2><button class="dialog-close" aria-label="Cerrar">✕</button></div><div id="notifications"></div></dialog>
<dialog id="report-dialog"><div class="dialog-head"><h2>Reportar mensaje</h2><button class="dialog-close" aria-label="Cerrar">✕</button></div><form id="report-form"><p>El administrador revisará el contexto antes de tomar una decisión.</p><label for="report-reason">¿Qué ocurrió?</label><textarea id="report-reason" minlength="5" maxlength="500" rows="4" required></textarea><button type="submit" class="primary full">Enviar reporte</button></form></dialog>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="assets/transport.js"></script>
<script src="assets/chat.js"></script>
</body></html>
