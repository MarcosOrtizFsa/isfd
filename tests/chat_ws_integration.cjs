// Prueba HTTP + WebSocket real de localhost. Solo crea cuentas ficticias wstest_*.
const net = require('node:net');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');
const base = 'http://localhost/isfd.com.ar/chat/';
const suffix = Date.now().toString(36);
const names = ['wstest_' + suffix + '_a', 'wstest_' + suffix + '_b'];
const clients = [];

class SocketClient {
    constructor(origin = 'http://localhost') {
        this.queue = []; this.waiters = []; this.buffer = Buffer.alloc(0); this.headers = false;
        this.socket = net.connect(8087, '127.0.0.1'); clients.push(this);
        this.socket.on('connect', () => this.socket.write('GET / HTTP/1.1\r\nHost: localhost:8087\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Version: 13\r\nSec-WebSocket-Key: ' + crypto.randomBytes(16).toString('base64') + '\r\nOrigin: ' + origin + '\r\n\r\n'));
        this.socket.on('error', e => this.deliver({type:'closed',message:e.message}));
        this.socket.on('close', () => this.deliver({type:'closed'}));
        this.socket.on('data', data => this.receive(data));
    }
    receive(data) {
        this.buffer = Buffer.concat([this.buffer, data]);
        if (!this.headers) {
            const end = this.buffer.indexOf('\r\n\r\n'); if (end < 0) return;
            const header = this.buffer.subarray(0,end).toString(); this.buffer = this.buffer.subarray(end+4); this.headers = true;
            this.deliver({type:header.includes('101 Switching')?'open':'closed'});
        }
        while (this.buffer.length >= 2) {
            const opcode = this.buffer[0] & 15; let size = this.buffer[1] & 127, offset = 2;
            if (size === 126) {if (this.buffer.length < 4) return; size = this.buffer.readUInt16BE(2); offset = 4;}
            if (size === 127) {if (this.buffer.length < 10) return; size = Number(this.buffer.readBigUInt64BE(2)); offset = 10;}
            if (this.buffer.length < offset+size) return;
            const payload = this.buffer.subarray(offset,offset+size); this.buffer = this.buffer.subarray(offset+size);
            if (opcode===1) this.deliver(JSON.parse(payload.toString()));
            if (opcode===9) this.frame(payload,10);
            if (opcode===8) {this.socket.end(); this.deliver({type:'closed'});}
        }
    }
    frame(payload, opcode = 1) {
        const mask = crypto.randomBytes(4), long = payload.length >= 126, head = Buffer.alloc(long?4:2);
        head[0] = 128|opcode; head[1] = 128|(long?126:payload.length); if(long) head.writeUInt16BE(payload.length,2);
        const masked = Buffer.from(payload); for(let i=0;i<masked.length;i++) masked[i]^=mask[i%4];
        this.socket.write(Buffer.concat([head,mask,masked]));
    }
    send(data) {this.frame(Buffer.from(JSON.stringify(data)));}
    deliver(message) {
        const index = this.waiters.findIndex(w => w.predicate(message));
        if (index>=0) {const waiter=this.waiters.splice(index,1)[0];clearTimeout(waiter.timer);waiter.resolve(message);}
        else this.queue.push(message);
    }
    wait(predicate) {
        const index=this.queue.findIndex(predicate); if(index>=0)return Promise.resolve(this.queue.splice(index,1)[0]);
        return new Promise((resolve,reject)=>{const entry={predicate,resolve};entry.timer=setTimeout(()=>reject(new Error('Timeout esperando respuesta WebSocket')),6000);this.waiters.push(entry);});
    }
    async auth(token) {await this.wait(m=>m.type==='open');this.send({type:'auth',token});return this.wait(m=>m.type==='ready'||m.type==='response'||m.type==='closed');}
    async rpc(id,action,data={}) {this.send({type:'request',id,action,data});return this.wait(m=>m.type==='response'&&m.id===id);}
    close() {this.waiters.forEach(w=>clearTimeout(w.timer));this.socket.destroy();}
}
async function session() {
    const response=await fetch(base); const html=await response.text();
    return {cookie:response.headers.get('set-cookie').split(';')[0],csrf:html.match(/name="csrf-token" content="([^"]+)"/)[1]};
}
async function api(session,action,data={},write=true) {
    const values=new URLSearchParams({action,...data,...(write?{csrf_token:session.csrf}:{})});
    const response=await fetch(base+'api.php'+(write?'':'?'+values),{method:write?'POST':'GET',headers:{Cookie:session.cookie,...(write?{'Content-Type':'application/x-www-form-urlencoded'}:{})},body:write?values:undefined});
    if(response.headers.get('set-cookie'))session.cookie=response.headers.get('set-cookie').split(';')[0];
    const body=await response.json();assert.equal(body.ok,true,body.message);return body;
}
(async()=>{
    try {
        const sessions=[await session(),await session()];
        for(let i=0;i<2;i++) {
            await api(sessions[i],'register',{usuario:names[i],email:names[i]+'@example.test',password:'Prueba-local-WebSocket-2026',reglas:'1'});
            const avatars=await api(sessions[i],'avatars',{},false);
            await api(sessions[i],'avatar',{avatar:avatars.avatars.find(a=>!a.propietario).id});
        }
        const tokenA=await api(sessions[0],'ws-token'),tokenB=await api(sessions[1],'ws-token');
        const a=new SocketClient(),b=new SocketClient();
        assert.equal((await a.auth(tokenA.token)).type,'ready');assert.equal((await b.auth(tokenB.token)).type,'ready');
        console.log('OK: dos conexiones autenticadas con credenciales HTTP temporales');
        const reused=new SocketClient();const denied=await reused.auth(tokenA.token);assert.ok(denied.status===401||denied.type==='closed');reused.close();
        const foreign=new SocketClient('https://sitio-ajeno.example');await foreign.wait(m=>m.type==='closed');foreign.close();
        console.log('OK: token reutilizado y origen ajeno rechazados');
        await a.rpc(1,'state',{room:1});await b.rpc(1,'state',{room:1});
        const payload={room:1,text:'Prueba WebSocket para @'+names[1],client_id:crypto.randomUUID()};
        const sent=await a.rpc(2,'send',payload);assert.equal(sent.status,200);
        await b.wait(m=>m.type==='changed');
        const received=await b.rpc(2,'messages',{room:1,after:sent.data.id-1});
        assert.equal(received.data.messages[0].texto,payload.text);
        const state=await b.rpc(3,'state',{room:1});assert.equal(state.data.notifications.length,1);
        console.log('OK: mensaje y mención entregados a la segunda conexión');
        const retry=await api(sessions[0],'send',payload);assert.equal(retry.id,sent.data.id);
        console.log('OK: reintento por AJAX conserva el ID sin duplicar el mensaje WebSocket');
        await api(sessions[0],'logout');
        a.send({type:'request',id:3,action:'state',data:{room:1}});
        const revoked=await a.wait(m=>m.type==='closed'||(m.type==='response'&&m.id===3));assert.ok(revoked.status===401||revoked.type==='closed');
        console.log('OK: cerrar sesión revoca el socket abierto');
        await api(sessions[1],'logout');
    } finally {
        clients.forEach(client=>client.close());
        execFileSync('C:\\xampp\\php\\php.exe',['tests/chat_ws_cleanup.php',...names],{stdio:'pipe'});
    }
})().catch(error=>{console.error(error.message);process.exitCode=1;});
