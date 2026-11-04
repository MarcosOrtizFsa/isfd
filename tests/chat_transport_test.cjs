const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
let now = 0, sequence = 0;
const timers = new Map(), sockets = [], httpCalls = [];
class FakeSocket {
    constructor(url) {this.url = url; this.sent = []; sockets.push(this);}
    send(data) {this.sent.push(JSON.parse(data));}
    close() {this.closed = true;}
    emit(data) {this.onmessage({data: JSON.stringify(data)});}
}
const context = vm.createContext({
    WebSocket: FakeSocket,
    setTimeout(fn, delay) {const id = ++sequence; timers.set(id, {at: now + delay, fn}); return id;},
    clearTimeout(id) {timers.delete(id);}
});
vm.runInContext(fs.readFileSync('chat/assets/transport.js', 'utf8'), context);
async function flush() {for (let i = 0; i < 8; i++) await Promise.resolve();}
async function advance(ms) {
    now += ms;
    for (const [id, timer] of [...timers]) if (timer.at <= now) {timers.delete(id); timer.fn();}
    await flush();
}
const http = async (action, data, write) => {
    httpCalls.push({action, data, write});
    return action === 'ws-token' ? {url:'ws://localhost:8087',token:'temporary'} : {ok:true,source:'ajax'};
};
(async () => {
    const modes = [];
    const transport = new context.ChatTransport(http, {onMode: mode => modes.push(mode)});
    transport.start(1); await flush();
    assert.equal(transport.mode, 'ajax');
    await transport.request('state');
    assert.equal(httpCalls.at(-1).action, 'state');
    let socket = sockets.at(-1); socket.onopen();
    assert.equal(socket.sent[0].type, 'auth');
    socket.emit({type:'ready'}); assert.equal(transport.mode, 'ws');
    const state = transport.request('state',{room:1});
    const rpc = socket.sent.at(-1);
    socket.emit({type:'response',id:rpc.id,status:200,data:{ok:true,source:'ws'}});
    assert.equal((await state).source, 'ws');
    const denied = transport.request('send',{text:'bloqueado',client_id:'same-key'});
    socket.emit({type:'response',id:socket.sent.at(-1).id,status:403,data:{ok:false,message:'Suspensión'}});
    await assert.rejects(denied, e => e.status === 403);
    assert.notEqual(httpCalls.at(-1).action, 'send');
    const lost = transport.request('send',{text:'Mensaje',client_id:'stable-id'},true);
    socket.onclose();
    assert.equal((await lost).source, 'ajax');
    assert.equal(httpCalls.at(-1).data.client_id, 'stable-id');
    assert.equal(transport.mode, 'ajax');
    await advance(1000);
    socket = sockets.at(-1); socket.onopen(); socket.emit({type:'ready'});
    assert.equal(transport.mode, 'ws');
    const timeout = transport.request('messages',{room:1});
    await advance(5000);
    assert.equal((await timeout).source, 'ajax');
    assert.equal(transport.mode, 'ajax');
    transport.stop(); const count = sockets.length;
    await advance(60000); assert.equal(sockets.length, count);
    transport.start(2); await flush();
    socket = sockets.at(-1); socket.onopen(); socket.emit({type:'ready'});
    const cancelled = transport.request('send',{client_id:'cancelled'},true);
    transport.stop(); await assert.rejects(cancelled);
    assert.notEqual(httpCalls.at(-1).data.client_id, 'cancelled');
    console.log('OK: AJAX inicial, WebSocket autenticado, errores sin reenvío, caída con client_id estable, reconexión, timeout y cierre sin reenvío.');
})().catch(error => {console.error(error); process.exitCode = 1;});
