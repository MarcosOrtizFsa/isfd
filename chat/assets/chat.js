(function ($) {
    'use strict';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let user = null, rooms = [], room = 0, generation = 0, latest = 0, oldest = 0;
    let polling = false, reply = 0, reportedMessage = 0, avatars = [], avatarPage = 0;
    let notifications = [], seenNotifications = new Set(), firstNotifications = true;
    let sound = false, audioContext, pendingSend = null;
    let lastRefresh = 0, refreshQueued = false;
    const messageCache = new Map();
    const show = (selector, visible) => $(selector).prop('hidden', !visible);
    const notice = (text, error = false) => $('#status').text(text).toggleClass('error', error).toggleClass('visible', !!text);
    const avatarUrl = id => 'avatar.php?id=' + (Number(id) || 1);
    const time = value => (value || '').slice(11, 16);
    const http = (action, data = {}, write = false) => $.ajax({
        url: 'api.php', method: write ? 'POST' : 'GET', dataType: 'json', timeout: 15000,
        data: Object.assign({action}, data, write ? {csrf_token: csrf} : {})
    });
    const transport = new ChatTransport(http, {
        onMode: mode => $('#connection-status').text(mode === 'ws' ? 'En tiempo real · WebSocket' : 'Conexión alternativa · AJAX'),
        onChange: () => tick(true)
    });
    const request = (action, data = {}, write = false) => transport.request(action, data, write);
    function failure(xhr) {
        notice(xhr.responseJSON?.message || 'No hay conexión. Conservamos tu mensaje; podés volver a intentar.', true);
    }
    function dialog(id) { document.getElementById(id).showModal(); }
    $(document).on('click', '[data-dialog]', function () { dialog(this.dataset.dialog); });
    $('.dialog-close').on('click', function () { this.closest('dialog').close(); });
    $('#login-tab, #register-tab').on('click', function () {
        const register = this.id === 'register-tab';
        show('#register-form', register); show('#login-form', !register);
        $('#register-tab').toggleClass('selected', register); $('#login-tab').toggleClass('selected', !register);
    });
    $('#login-form, #register-form').on('submit', async function (event) {
        event.preventDefault();
        const button = $(this).find('[type=submit]').prop('disabled', true);
        const data = Object.fromEntries(new FormData(this));
        try {
            await request(this.id === 'login-form' ? 'login' : 'register', data, true);
            this.reset(); notice(''); await refreshState();
            if (!user.avatar_id) await openAvatars();
        } catch (e) { failure(e); }
        finally { button.prop('disabled', false); }
    });
    $('#logout').on('click', async function () {
        transport.stop();
        try {
            await request('logout', {}, true); user = null; room = 0; generation++;
            document.querySelectorAll('dialog[open]').forEach(d => d.close());
            firstNotifications = true; seenNotifications.clear();
            $('#community').removeClass('chat-active');
            show('#conversation', false); show('#lobby', true);
            show('#community', false); show('#welcome', true); notice('');
        } catch (e) { failure(e); }
    });
    function renderRooms() {
        const grid = $('#rooms').empty();
        rooms.forEach(r => {
            const card = $('<article class="room-card">').appendTo(grid);
            $('<div class="room-art">').append($('<img>').attr({src: 'mascota.php?id=' + r.id, alt: 'Mascota de ' + r.nombre})).appendTo(card);
            $('<span class="room-count">').text(r.conectados + ' en línea').appendTo(card);
            $('<h3>').text(r.nombre).appendTo(card);
            $('<p>').text(r.descripcion).appendTo(card);
            $('<button class="room-enter">').text('Entrar a la sala ↗').on('click', () => enterRoom(Number(r.id))).appendTo(card);
        });
    }
    function renderNotifications(list) {
        notifications = list;
        $('#notification-count, #room-notification-count').text(list.length);
        if (!firstNotifications && list.some(n => !seenNotifications.has(Number(n.id))) && sound) {
            try {
                const oscillator = audioContext.createOscillator(), gain = audioContext.createGain();
                oscillator.connect(gain); gain.connect(audioContext.destination);
                gain.gain.value = 0.04; oscillator.frequency.value = 660;
                oscillator.start(); oscillator.stop(audioContext.currentTime + 0.12);
            } catch (_) { /* El navegador puede bloquear audio en segundo plano. */ }
        }
        firstNotifications = false;
        list.forEach(n => seenNotifications.add(Number(n.id)));
        const container = $('#notifications').empty();
        if (!list.length) $('<p>').text('Todavía no tenés menciones ni respuestas pendientes.').appendTo(container);
        list.forEach(n => {
            const button = $('<button class="notification">').append($('<strong>').text('@' + n.usuario), $('<span>').text(n.texto));
            button.on('click', async () => {
                try {
                    await request('read', {id:n.id}, true);
                    document.getElementById('notifications-dialog').close();
                    await enterRoom(Number(n.sala_id));
                    // Una mención antigua puede quedar fuera de los últimos 50 mensajes.
                    while (oldest > Number(n.mensaje_id) && room === Number(n.sala_id)) {
                        const result = await request('messages', {room, before:oldest});
                        if (!result.messages.length) break;
                        renderMessages(result.messages, true);
                    }
                    document.getElementById('message-' + n.mensaje_id)?.scrollIntoView({block:'center', behavior:'smooth'});
                } catch (e) { failure(e); }
            });
            container.append(button);
        });
    }
    async function refreshState() {
        const current = generation;
        const data = await request('state', {room});
        if (current !== generation) return;
        user = data.user;
        show('#welcome', !user); show('#community', !!user);
        if (!user) { transport.stop(); return; }
        transport.start(Number(user.id));
        $('#greeting').text(user.usuario); $('#my-avatar').attr('src', avatarUrl(user.avatar_id));
        rooms = data.rooms;
        if (!room) renderRooms();
        renderNotifications(data.notifications);
        const alerts = $('#account-notices').empty();
        if (!user.avatar_id) $('<button class="secondary">').text('Elegí un avatar para comenzar a conversar →').on('click', openAvatars).appendTo(alerts);
        if (user.suspendido_hasta && new Date(user.suspendido_hasta.replace(' ','T')) > new Date()) $('<p>').text('Pausa de escritura hasta ' + user.suspendido_hasta + '. Si necesitás una revisión, contactá a la institución.').appendTo(alerts);
        data.notices.forEach(n => $('<p>').text('Moderación · ' + n.accion + ': ' + n.motivo).appendTo(alerts));
        const online = $('#online').empty(), mobileOnline = $('#mobile-online-list').empty();
        $('#online-count, #mobile-online-count').text('(' + data.online.length + ')');
        data.online.forEach(u => {
            [online, mobileOnline].forEach(container => {
                $('<button class="online-user">').append($('<img>').attr({src:avatarUrl(u.avatar_id),alt:''}), $('<span>').text('@' + u.usuario))
                    .on('click', () => { const input = $('#message-text'); input.val(input.val() + '@' + u.usuario + ' ').trigger('input').trigger('focus'); }).appendTo(container);
            });
        });
        $('#connection-status').text(transport.mode === 'ws' ? 'En tiempo real · WebSocket' : 'Conexión alternativa · AJAX');
    }
    async function enterRoom(id) {
        if (!user.avatar_id) { await openAvatars(); return; }
        room = id; generation++; latest = 0; oldest = 0; reply = 0; pendingSend = null;
        $('#community').addClass('chat-active');
        messageCache.clear(); $('#message-list').empty(); $('#message-text').val('').trigger('input');
        show('#reply-preview', false); show('#lobby', false); show('#conversation', true); show('#older', true); show('#new-messages', false);
        $('#room-title').text(rooms.find(r => Number(r.id) === id)?.nombre || 'Sala');
        try { await refreshState(); await refreshMessages(); } catch (e) { failure(e); }
    }
    $('#back-rooms').on('click', function () {
        room = 0; generation++; $('#community').removeClass('chat-active'); show('#conversation', false); show('#lobby', true); refreshState().catch(failure);
    });
    function messageNode(m) {
        const node = $('<article class="message">').attr('id', 'message-' + m.id).toggleClass('mine', Number(m.usuario_id) === Number(user.id));
        node.append($('<img class="message-avatar">').attr({src:avatarUrl(m.avatar_id),alt:''}));
        const bubble = $('<div class="message-bubble">').appendTo(node);
        $('<header>').append($('<strong>').text('@' + m.usuario), $('<time>').attr('datetime', m.created_at.replace(' ', 'T')).text(time(m.created_at))).appendTo(bubble);
        if (m.respuesta_id) $('<blockquote>').text('@' + m.respuesta_usuario + ': ' + m.respuesta_texto).appendTo(bubble);
        // Siempre texto: ningún mensaje ni nombre se interpreta como HTML.
        $('<p class="message-text">').text(m.texto).appendTo(bubble);
        if (!Number(m.oculto)) {
            const actions = $('<div class="message-actions">').appendTo(bubble);
            $('<button type="button">').text('Responder').on('click', () => {
                reply = Number(m.id); $('#reply-preview span').text('A @' + m.usuario + ': ' + m.texto.slice(0,120)); show('#reply-preview', true); $('#message-text').trigger('focus');
            }).appendTo(actions);
            $('<button type="button">').text('Reportar').on('click', () => {reportedMessage = Number(m.id); $('#report-form')[0].reset(); dialog('report-dialog');}).appendTo(actions);
        }
        return node;
    }
    function renderMessages(messages, prepend = false) {
        const list = $('#message-list'), fragment = document.createDocumentFragment();
        messages.forEach(m => {
            const id = Number(m.id);
            if (messageCache.has(id)) return;
            messageCache.set(id, m); latest = Math.max(latest,id); oldest = oldest ? Math.min(oldest,id) : id;
            fragment.appendChild(messageNode(m)[0]);
        });
        if (prepend) list.prepend(fragment); else list.append(fragment);
    }
    async function refreshMessages() {
        if (!room) return;
        const current = generation, initial = !latest;
        const data = await request('messages', {room, after:latest, visible_from:oldest});
        if (current !== generation) return;
        const box = $('#messages')[0], atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 100;
        renderMessages(data.messages);
        data.hidden.forEach(id => {
            const m = messageCache.get(Number(id));
            if (m && !Number(m.oculto)) {
                m.oculto = 1; m.texto = 'Mensaje retirado por moderación.';
                $('#message-' + id).replaceWith(messageNode(m));
                messageCache.forEach(child => {
                    if (Number(child.respuesta_id) === Number(id)) $('#message-' + child.id + ' blockquote').text('Mensaje retirado.');
                });
            }
        });
        if (initial || atBottom) box.scrollTop = box.scrollHeight;
        else if (data.messages.length) show('#new-messages', true);
        if (initial) show('#older', data.messages.length === 50);
    }
    $('#older').on('click', async function () {
        const current = generation, button = $(this).prop('disabled',true), box = $('#messages')[0], height = box.scrollHeight;
        try {
            const data = await request('messages', {room, before:oldest});
            if (current !== generation) return;
            renderMessages(data.messages,true); box.scrollTop += box.scrollHeight-height; show('#older',data.messages.length === 50);
        } catch (e) { failure(e); } finally {button.prop('disabled',false);}
    });
    $('#new-messages').on('click', function () { $('#messages')[0].scrollTop = $('#messages')[0].scrollHeight; show(this,false); });
    $('#reply-cancel').on('click', () => {reply = 0; show('#reply-preview',false);});
    $('#message-text').on('input', function () {$('#characters').text(this.value.length);});
    $('#message-text').on('keydown', function (e) {if(e.key==='Enter' && !e.shiftKey && !e.originalEvent.isComposing) {e.preventDefault(); $('#message-form').trigger('submit');}});
    $('#message-form').on('submit', async function (e) {
        e.preventDefault(); const button = $(this).find('[type=submit]'); if(button.prop('disabled')) return;
        const text = $('#message-text').val().trim(); if (!text) return;
        button.prop('disabled',true);
        // Conservar la misma clave ante una respuesta perdida evita publicaciones duplicadas.
        const signature = JSON.stringify([room,reply,text]);
        if (!pendingSend || pendingSend.signature !== signature) pendingSend = {signature,id:crypto.randomUUID ? crypto.randomUUID() : Date.now() + '-' + Math.random().toString(36).slice(2)};
        const current = generation;
        try {
            await request('send',{room,text,reply,client_id:pendingSend.id},true);
            if (current !== generation) return;
            pendingSend=null;
            if ($('#message-text').val().trim() === text) $('#message-text').val('').trigger('input');
            reply=0; show('#reply-preview',false); notice(''); await refreshMessages();
            $('#messages')[0].scrollTop = $('#messages')[0].scrollHeight;
        } catch (error) {failure(error);} finally {button.prop('disabled',false);}
    });
    $('#report-form').on('submit', async function (e) {
        e.preventDefault(); const button=$(this).find('[type=submit]').prop('disabled',true);
        try {await request('report',{message:reportedMessage,reason:$('#report-reason').val()},true); document.getElementById('report-dialog').close(); notice('Reporte enviado. El administrador revisará el mensaje.');}
        catch (e) {failure(e);} finally {button.prop('disabled',false);}
    });
    async function openAvatars() {
        try {const data=await request('avatars'); avatars=data.avatars; renderAvatars(); if(!document.getElementById('avatar-dialog').open) dialog('avatar-dialog');}
        catch(e) {failure(e);}
    }
    function renderAvatars() {
        const grid=$('#avatar-grid').empty();
        avatars.slice(avatarPage*32,(avatarPage+1)*32).forEach(a => {
            const occupied = a.propietario && Number(a.propietario)!==Number(user.id);
            $('<button class="avatar-option">').prop('disabled',!!occupied).attr({'aria-label':'Avatar '+a.id+(occupied?' ocupado':''),'aria-pressed':Number(user.avatar_id)===Number(a.id)})
                .append($('<img>').attr({src:avatarUrl(a.id),alt:'',loading:'lazy'}),$('<small>').text(occupied?'Ocupado':'#'+a.id))
                .on('click',async () => {
                    try {await request('avatar',{avatar:a.id},true); document.getElementById('avatar-dialog').close(); await refreshState(); notice('Avatar actualizado.');}
                    catch(e) {failure(e); await openAvatars();}
                }).appendTo(grid);
        });
        $('#avatars-page').text((avatarPage+1)+' / '+Math.ceil(avatars.length/32));
        $('#avatars-prev').prop('disabled',avatarPage===0); $('#avatars-next').prop('disabled',(avatarPage+1)*32>=avatars.length);
    }
    $('#avatar-open').on('click',openAvatars);
    $('#avatars-prev').on('click',()=>{avatarPage--;renderAvatars();}); $('#avatars-next').on('click',()=>{avatarPage++;renderAvatars();});
    $('#notifications-open, #room-notifications-open').on('click',()=>dialog('notifications-dialog'));
    $('#sound-toggle').on('click',function () {
        sound=!sound;
        if(sound) {try {audioContext=audioContext || new (window.AudioContext || window.webkitAudioContext)(); audioContext.resume();} catch(_){sound=false;}}
        $(this).text('Sonido: '+(sound?'sí':'no')).attr('aria-pressed',String(sound));
    });
    // En WebSocket se actualiza por eventos; cada 20 s se renueva presencia y se recuperan brechas.
    // AJAX mantiene sus 3 s solo mientras el socket está caído. Nunca solapar ciclos.
    async function tick(force = false) {
        if(document.hidden || !user) return;
        if(polling) { if(force) refreshQueued = true; return; }
        if(!force && transport.mode === 'ws' && Date.now() - lastRefresh < 20000) return;
        polling=true;
        try {await refreshState(); await refreshMessages();}
        catch(e) {$('#connection-status').text('Sin conexión · reintentando…'); failure(e);}
        finally {
            polling=false; lastRefresh=Date.now();
            if(refreshQueued) { refreshQueued=false; setTimeout(() => tick(true),100); }
        }
    }
    refreshState().catch(failure);
    setInterval(tick,3000);
    document.addEventListener('visibilitychange',()=>{if(!document.hidden) tick(true);});
})(jQuery);
