(function (root) {
    'use strict';

    /** RPC WebSocket con el mismo contrato JSON que api.php y recuperación HTTP automática. */
    class ChatTransport {
        constructor(http, options = {}) {
            this.http = http;
            this.onMode = options.onMode || (() => {});
            this.onChange = options.onChange || (() => {});
            this.mode = 'ajax';
            this.socket = null;
            this.pending = new Map();
            this.sequence = 0;
            this.epoch = 0;
            this.user = null;
            this.enabled = false;
            this.attempt = 0;
            this.retryTimer = null;
            this.connectTimer = null;
        }

        start(user) {
            if (this.user === user) return;
            this.stop();
            this.user = user;
            this.enabled = true;
            this.connect();
        }

        stop() {
            this.enabled = false;
            this.user = null;
            this.epoch++;
            clearTimeout(this.retryTimer);
            clearTimeout(this.connectTimer);
            this.retryTimer = null;
            this.connectTimer = null;
            this.disconnect();
            this.attempt = 0;
        }

        setMode(mode) {
            this.mode = mode;
            this.onMode(mode);
        }

        disconnect() {
            const socket = this.socket;
            this.socket = null;
            if (socket) { socket.onclose = null; socket.onerror = null; socket.close(); }
            for (const pending of this.pending.values()) {
                clearTimeout(pending.timer);
                pending.reject({transport: true});
            }
            this.pending.clear();
            this.setMode('ajax');
        }

        recover() {
            clearTimeout(this.connectTimer);
            this.disconnect();
            if (!this.enabled || this.retryTimer) return;
            const delay = Math.min(30000, 1000 * 2 ** Math.min(this.attempt++, 5));
            this.retryTimer = setTimeout(() => { this.retryTimer = null; this.connect(); }, delay);
        }

        async connect() {
            if (!this.enabled || this.socket) return;
            const epoch = this.epoch;
            try {
                const credential = await this.http('ws-token', {}, true);
                if (!this.enabled || epoch !== this.epoch) return;
                if (!credential.url || !root.WebSocket) { this.enabled = false; return; }
                const socket = new root.WebSocket(credential.url);
                this.socket = socket;
                this.connectTimer = setTimeout(() => { if (this.socket === socket) this.recover(); }, 4000);
                socket.onopen = () => {
                    if (this.socket === socket) socket.send(JSON.stringify({type: 'auth', token: credential.token}));
                };
                socket.onclose = socket.onerror = () => { if (this.socket === socket) this.recover(); };
                socket.onmessage = event => {
                    if (this.socket !== socket) return;
                    try {
                        const message = JSON.parse(event.data);
                        if (message.type === 'ready') {
                            clearTimeout(this.connectTimer); this.attempt = 0;
                            this.setMode('ws'); this.onChange();
                        } else if (message.type === 'changed' && this.mode === 'ws') {
                            this.onChange();
                        } else if (message.type === 'response') {
                            const pending = this.pending.get(message.id);
                            if (!pending) return;
                            this.pending.delete(message.id); clearTimeout(pending.timer);
                            if (message.data?.ok) pending.resolve(message.data);
                            else pending.reject({status: message.status, responseJSON: message.data});
                        }
                    } catch (_) { this.recover(); }
                };
            } catch (_) {
                if (this.enabled && epoch === this.epoch) this.recover();
            }
        }

        async request(action, data = {}, write = false) {
            const epoch = this.epoch;
            if (this.mode !== 'ws' || !this.socket || ['login', 'register', 'logout', 'ws-token'].includes(action)) {
                return this.http(action, data, write);
            }
            try {
                return await new Promise((resolve, reject) => {
                    const id = ++this.sequence;
                    const timer = setTimeout(() => this.recover(), 5000);
                    this.pending.set(id, {resolve, reject, timer});
                    try { this.socket.send(JSON.stringify({type: 'request', id, action, data})); }
                    catch (_) { this.recover(); }
                });
            } catch (error) {
                if (!error.transport || epoch !== this.epoch) throw error;
                // Mismos parámetros/client_id: una confirmación perdida no duplica el envío.
                return this.http(action, data, write);
            }
        }
    }

    root.ChatTransport = ChatTransport;
})(typeof window === 'undefined' ? globalThis : window);
