(function () {
    const roots = document.querySelectorAll('[data-colt-live-show]');
    if (!roots.length) return;

    const config = window.COLT_LIVE_SHOW || {};
    const restUrl = (config.restUrl || '').replace(/\/$/, '');
    const mapConfig = config.map || { width: 3200, height: 1760, boothWidth: 250, boothHeight: 150, sideDepth: 430 };
    const npcs = config.npcs || [];

    roots.forEach((root) => new LiveShow(root).init());

    function LiveShow(root) {
        this.root = root;
        this.entry = root.querySelector('[data-live-entry]');
        this.joinForm = root.querySelector('[data-live-join]');
        this.game = root.querySelector('[data-live-game]');
        this.viewport = root.querySelector('[data-live-viewport]');
        this.world = root.querySelector('[data-live-world]');
        this.playersLayer = root.querySelector('[data-live-players]');
        this.boothsLayer = root.querySelector('[data-live-booths]');
        this.npcsLayer = root.querySelector('[data-live-npcs]');
        this.notice = root.querySelector('[data-live-notice]');
        this.placeButton = root.querySelector('[data-live-place-booth]');
        this.editButton = root.querySelector('[data-live-edit-booth]');
        this.viewButton = root.querySelector('[data-live-view-booth]');
        this.requestButton = root.querySelector('[data-live-request-chat]');
        this.placement = root.querySelector('[data-live-placement]');
        this.placementMap = root.querySelector('[data-placement-map]');
        this.placementWorld = root.querySelector('[data-placement-world]');
        this.placementConfirm = root.querySelector('[data-placement-confirm]');
        this.placementGhost = root.querySelector('[data-placement-ghost]');
        this.boothModal = root.querySelector('[data-booth-modal]');
        this.boothModalContent = root.querySelector('[data-booth-modal-content]');
        this.boothEditor = root.querySelector('[data-booth-editor]');
        this.boothEditorForm = root.querySelector('[data-booth-editor-form]');
        this.chatPanel = root.querySelector('[data-live-chat]');
        this.chatTitle = root.querySelector('[data-chat-title]');
        this.chatRequests = root.querySelector('[data-chat-requests]');
        this.chatLog = root.querySelector('[data-chat-log]');
        this.chatForm = root.querySelector('[data-chat-form]');
        this.status = root.querySelector('[data-live-status]');
        this.mobileButtons = root.querySelectorAll('[data-move]');

        this.local = null;
        this.state = { sessions: [], booths: [], chats: [] };
        this.keys = new Set();
        this.mobileKeys = new Set();
        this.nearBooth = null;
        this.selectedPlacement = null;
        this.vendorDraft = { title: '', color: '#86f7d4', description: '', items: [] };
        this.activeRoomId = '';
        this.lastFrame = performance.now();
        this.lastHeartbeat = 0;
        this.lastState = 0;
        this.scale = 0.86;
        this.started = false;
    }

    LiveShow.prototype.init = function () {
        if (!restUrl) {
            this.setNotice('REST API לא זמין בעמוד הזה.');
            return;
        }

        this.root.style.setProperty('--live-map-width', `${mapConfig.width}px`);
        this.root.style.setProperty('--live-map-height', `${mapConfig.height}px`);
        this.world.style.width = `${mapConfig.width}px`;
        this.world.style.height = `${mapConfig.height}px`;
        this.renderNpcs();
        this.bindEntry();
        this.bindControls();
        this.bindPlacement();
        this.bindBoothActions();
        this.bindChat();
        this.updatePlacementScale();
        window.addEventListener('resize', () => this.updatePlacementScale());
    };

    LiveShow.prototype.bindEntry = function () {
        this.joinForm.querySelectorAll('input[name="role"]').forEach((input) => {
            input.addEventListener('change', () => {
                this.root.dataset.liveRole = this.joinForm.elements.role.value;
            });
        });
        this.root.dataset.liveRole = this.joinForm.elements.role.value;

        this.joinForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = new FormData(this.joinForm);
            const role = String(form.get('role') || 'collector');
            const name = String(form.get('name') || '').trim();
            const color = String(form.get('color') || '#86f7d4');
            this.vendorDraft.title = String(form.get('booth_title') || '').trim();
            this.vendorDraft.color = color;
            this.vendorDraft.description = 'סינגלים, סלאבים ומוצרים מיוחדים לתצוגה בלייב.';
            this.vendorDraft.items = [
                { title: 'Featured slab', price: 'Ask', note: 'PSA / CGC showcase' },
                { title: 'Singles binder', price: 'Live offer', note: 'Pokemon / One Piece' },
                { title: 'Sealed pack', price: 'Limited', note: 'Fresh sealed product' },
            ];

            this.setNotice('נכנס ללייב...');
            try {
                const response = await this.post('/join', { role, name, color });
                this.local = {
                    session: response.session,
                    token: response.token,
                    role,
                    name: name || (role === 'vendor' ? 'Vendor' : 'Collector'),
                    color,
                    x: response.state.sessions.find((item) => item.id === response.session)?.x || mapConfig.width / 2,
                    y: response.state.sessions.find((item) => item.id === response.session)?.y || mapConfig.height / 2,
                    dir: 'down',
                };
                this.state = response.state;
                this.root.classList.add('is-live');
                this.root.dataset.liveRole = role;
                this.root.querySelector('[data-live-name]').textContent = this.local.name;
                this.root.querySelector('[data-live-role]').textContent = role === 'vendor' ? 'Vendor' : 'Collector';
                this.placeButton.hidden = role !== 'vendor';
                this.editButton.hidden = role !== 'vendor';
                this.started = true;
                this.setNotice(role === 'vendor' ? 'בחר מקום לעמדה שלך בצדדים של המפה.' : 'ברוך הבא. הסתובב בין העמדות והתקרב כדי לפתוח שיחה.');
                this.renderState();
                this.loop(performance.now());
                if (role === 'vendor') this.openPlacement();
            } catch (error) {
                this.setNotice(error.message || 'לא הצלחתי להיכנס ללייב.');
            }
        });
    };

    LiveShow.prototype.bindControls = function () {
        window.addEventListener('keydown', (event) => {
            if (isTyping(event.target)) return;
            const key = normalizeMoveKey(event.key);
            if (!key) return;
            event.preventDefault();
            this.keys.add(key);
        });
        window.addEventListener('keyup', (event) => {
            const key = normalizeMoveKey(event.key);
            if (key) this.keys.delete(key);
        });

        this.mobileButtons.forEach((button) => {
            const key = button.dataset.move;
            const start = (event) => {
                event.preventDefault();
                this.mobileKeys.add(key);
            };
            const stop = (event) => {
                event.preventDefault();
                this.mobileKeys.delete(key);
            };
            button.addEventListener('pointerdown', start);
            button.addEventListener('pointerup', stop);
            button.addEventListener('pointercancel', stop);
            button.addEventListener('pointerleave', stop);
        });

        window.addEventListener('pagehide', () => {
            if (!this.local) return;
            try {
                fetch(`${restUrl}/leave`, {
                    method: 'POST',
                    keepalive: true,
                    headers: { 'content-type': 'application/json' },
                    body: JSON.stringify(this.authPayload()),
                });
            } catch (error) {}
        });
    };

    LiveShow.prototype.bindPlacement = function () {
        this.placeButton.addEventListener('click', () => this.openPlacement());
        this.root.querySelector('[data-placement-close]').addEventListener('click', () => this.closePlacement());
        this.placementMap.addEventListener('pointermove', (event) => this.updatePlacementCursor(event));
        this.placementMap.addEventListener('click', (event) => {
            this.updatePlacementCursor(event);
            if (!this.selectedPlacement || !this.selectedPlacement.valid) return;
            this.placementConfirm.disabled = false;
        });
        this.placementConfirm.addEventListener('click', async () => {
            if (!this.selectedPlacement || !this.selectedPlacement.valid) return;
            await this.saveBooth({ x: this.selectedPlacement.x, y: this.selectedPlacement.y });
            this.closePlacement();
        });
    };

    LiveShow.prototype.bindBoothActions = function () {
        this.viewButton.addEventListener('click', () => {
            if (this.nearBooth) this.openBoothModal(this.nearBooth);
        });
        this.requestButton.addEventListener('click', async () => {
            if (!this.nearBooth) return;
            try {
                const response = await this.post('/chat/request', { ...this.authPayload(), boothId: this.nearBooth.id });
                this.state = response.state || this.state;
                this.activeRoomId = response.roomId || this.activeRoomId;
                this.openChat(this.activeRoomId);
                this.renderState();
                this.setNotice('בקשת השיחה נשלחה ל-vendor.');
            } catch (error) {
                this.setNotice(error.message || 'לא ניתן לשלוח בקשת שיחה כרגע.');
            }
        });
        this.editButton.addEventListener('click', () => this.openBoothEditor());

        this.boothsLayer.addEventListener('click', (event) => {
            const boothElement = event.target.closest('[data-booth-id]');
            if (!boothElement) return;
            const booth = this.state.booths.find((item) => item.id === boothElement.dataset.boothId);
            if (booth) this.openBoothModal(booth);
        });
    };

    LiveShow.prototype.bindChat = function () {
        this.root.querySelector('[data-chat-close]').addEventListener('click', () => {
            this.chatPanel.hidden = true;
        });
        this.chatRequests.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-chat-response]');
            if (!button) return;
            try {
                const response = await this.post('/chat/respond', {
                    ...this.authPayload(),
                    roomId: button.dataset.roomId,
                    accept: button.dataset.chatResponse === 'accept',
                });
                this.state = response;
                if (button.dataset.chatResponse === 'accept') {
                    this.activeRoomId = button.dataset.roomId;
                    this.openChat(this.activeRoomId);
                }
                this.renderState();
            } catch (error) {
                this.setNotice(error.message || 'לא הצלחתי לעדכן את בקשת השיחה.');
            }
        });
        this.chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = this.chatForm.elements.message;
            const message = String(input.value || '').trim();
            if (!message || !this.activeRoomId) return;
            input.value = '';
            try {
                const response = await this.post('/chat/message', {
                    ...this.authPayload(),
                    roomId: this.activeRoomId,
                    message,
                });
                this.state = response;
                this.renderChat();
            } catch (error) {
                this.setNotice(error.message || 'ההודעה לא נשלחה.');
            }
        });
    };

    LiveShow.prototype.loop = function (now) {
        if (!this.started || !this.local) return;
        const dt = Math.min(0.05, (now - this.lastFrame) / 1000);
        this.lastFrame = now;
        this.updateLocalMovement(dt);
        this.updateCamera();
        this.updateNearbyBooth();

        if (now - this.lastHeartbeat > 700) {
            this.lastHeartbeat = now;
            this.heartbeat();
        }
        if (now - this.lastState > 1350) {
            this.lastState = now;
            this.fetchState();
        }

        requestAnimationFrame((frameTime) => this.loop(frameTime));
    };

    LiveShow.prototype.updateLocalMovement = function (dt) {
        const moveKeys = new Set([...this.keys, ...this.mobileKeys]);
        let dx = 0;
        let dy = 0;
        if (moveKeys.has('left')) dx -= 1;
        if (moveKeys.has('right')) dx += 1;
        if (moveKeys.has('up')) dy -= 1;
        if (moveKeys.has('down')) dy += 1;
        if (dx === 0 && dy === 0) return;

        const len = Math.hypot(dx, dy) || 1;
        const speed = this.local.role === 'vendor' ? 238 : 268;
        this.local.x = clamp(this.local.x + (dx / len) * speed * dt, 55, mapConfig.width - 55);
        this.local.y = clamp(this.local.y + (dy / len) * speed * dt, 115, mapConfig.height - 70);
        this.local.dir = Math.abs(dx) > Math.abs(dy) ? (dx < 0 ? 'left' : 'right') : (dy < 0 ? 'up' : 'down');
        this.updateLocalPlayerPosition();
    };

    LiveShow.prototype.updateCamera = function () {
        const viewportWidth = this.viewport.clientWidth || window.innerWidth;
        const viewportHeight = this.viewport.clientHeight || window.innerHeight;
        this.scale = viewportWidth < 760 ? 0.62 : viewportWidth < 1160 ? 0.76 : 0.86;
        const x = viewportWidth / 2 - this.local.x * this.scale;
        const y = viewportHeight / 2 - this.local.y * this.scale;
        const minX = viewportWidth - mapConfig.width * this.scale;
        const minY = viewportHeight - mapConfig.height * this.scale;
        const tx = clamp(x, Math.min(0, minX), 0);
        const ty = clamp(y, Math.min(0, minY), 0);
        this.world.style.transform = `translate3d(${tx}px, ${ty}px, 0) scale(${this.scale})`;
    };

    LiveShow.prototype.updateNearbyBooth = function () {
        let nearest = null;
        let nearestDistance = Infinity;
        for (const booth of this.state.booths || []) {
            const distance = Math.hypot((booth.x || 0) - this.local.x, (booth.y || 0) - this.local.y);
            if (distance < nearestDistance) {
                nearest = booth;
                nearestDistance = distance;
            }
        }
        this.nearBooth = nearestDistance < 245 ? nearest : null;
        this.viewButton.disabled = !this.nearBooth;
        const isOwnBooth = this.nearBooth && this.nearBooth.owner === this.local.session;
        this.requestButton.disabled = !this.nearBooth || isOwnBooth;
        if (this.nearBooth) {
            this.viewButton.textContent = 'צפייה: ' + this.nearBooth.title;
            this.requestButton.textContent = isOwnBooth ? 'זו העמדה שלך' : 'בקשת שיחה';
        } else {
            this.viewButton.textContent = 'צפייה בעמדה';
            this.requestButton.textContent = 'בקשת שיחה';
        }
    };

    LiveShow.prototype.heartbeat = async function () {
        try {
            const response = await this.post('/heartbeat', {
                ...this.authPayload(),
                x: Math.round(this.local.x),
                y: Math.round(this.local.y),
                dir: this.local.dir,
            });
            this.state = response;
            this.renderState();
        } catch (error) {
            this.setNotice('החיבור ללייב התנתק. אפשר לרענן ולהיכנס מחדש.');
        }
    };

    LiveShow.prototype.fetchState = async function () {
        if (!this.local) return;
        try {
            const url = new URL(`${restUrl}/state`);
            url.searchParams.set('session', this.local.session);
            url.searchParams.set('token', this.local.token);
            const response = await fetch(url.href, { credentials: 'same-origin' });
            const json = await response.json();
            if (!response.ok) throw new Error(json.message || 'state failed');
            this.state = json;
            this.renderState();
        } catch (error) {}
    };

    LiveShow.prototype.renderState = function () {
        this.renderStats();
        this.renderBooths();
        this.renderPlayers();
        this.renderPlacementBooths();
        this.renderChat();
    };

    LiveShow.prototype.renderStats = function () {
        const sessions = this.state.sessions || [];
        const vendors = sessions.filter((item) => item.role === 'vendor').length;
        const collectors = sessions.length - vendors;
        setText(this.root.querySelector('[data-live-count="collectors"]'), collectors);
        setText(this.root.querySelector('[data-live-count="vendors"]'), vendors);
        setText(this.root.querySelector('[data-live-count="booths"]'), (this.state.booths || []).length);
        setText(this.status, `LIVE / ${sessions.length} ONLINE`);
    };

    LiveShow.prototype.renderBooths = function () {
        const booths = this.state.booths || [];
        this.boothsLayer.innerHTML = booths.map((booth) => `
            <article class="colt-live-show__booth" data-booth-id="${escapeHtml(booth.id)}" style="left:${booth.x}px;top:${booth.y}px;--booth-color:${escapeHtml(booth.color || '#86f7d4')}">
                <strong>${escapeHtml(booth.title || 'Vendor table')}</strong>
                <span>${escapeHtml(booth.description || '')}</span>
                <small>${escapeHtml((booth.items || []).length || 0)} פריטים בתצוגה</small>
            </article>
        `).join('');
    };

    LiveShow.prototype.renderPlayers = function () {
        const sessions = this.state.sessions || [];
        const localSession = this.local?.session;
        this.playersLayer.innerHTML = sessions.map((session) => {
            const isLocal = session.id === localSession;
            const x = isLocal ? this.local.x : session.x;
            const y = isLocal ? this.local.y : session.y;
            return `
                <div class="colt-live-show__player ${isLocal ? 'is-local' : ''}" data-player-id="${escapeHtml(session.id)}" style="left:${x}px;top:${y}px;z-index:${Math.round(y)}">
                    ${spriteMarkup(session.name, session.color, session.role)}
                </div>
            `;
        }).join('');
    };

    LiveShow.prototype.updateLocalPlayerPosition = function () {
        const el = this.playersLayer.querySelector(`[data-player-id="${this.local.session}"]`);
        if (!el) return;
        el.style.left = `${this.local.x}px`;
        el.style.top = `${this.local.y}px`;
        el.style.zIndex = String(Math.round(this.local.y));
    };

    LiveShow.prototype.renderNpcs = function () {
        this.npcsLayer.innerHTML = npcs.map((npc, index) => `
            <div class="colt-live-show__npc" data-npc-id="${escapeHtml(npc.id)}" style="left:${npc.x}px;top:${npc.y}px;z-index:${Math.round(npc.y)};--sprite-color:${npcColor(npc.tone)};animation-delay:${(index % 5) * -.28}s">
                ${spriteMarkup(npc.name, npcColor(npc.tone), 'npc')}
                <span class="colt-live-show__npc-bubble">${escapeHtml(npc.line)}</span>
            </div>
        `).join('');
        this.npcsLayer.addEventListener('click', (event) => {
            const npcEl = event.target.closest('[data-npc-id]');
            if (!npcEl) return;
            npcEl.classList.add('is-speaking');
            setTimeout(() => npcEl.classList.remove('is-speaking'), 2600);
        });
    };

    LiveShow.prototype.openPlacement = function () {
        this.placement.hidden = false;
        this.selectedPlacement = null;
        this.placementConfirm.disabled = true;
        this.updatePlacementScale();
        this.renderPlacementBooths();
        this.setNotice('בחר מיקום ירוק בצדדים של המפה. המעבר המרכזי נשאר פנוי.');
    };

    LiveShow.prototype.closePlacement = function () {
        this.placement.hidden = true;
        this.placementGhost.classList.remove('is-active', 'is-valid');
    };

    LiveShow.prototype.updatePlacementScale = function () {
        if (!this.placementWorld || !this.placementMap) return;
        const width = Math.max(320, this.placementMap.clientWidth || window.innerWidth);
        const height = Math.max(240, this.placementMap.clientHeight || window.innerHeight);
        const scale = Math.min(width / mapConfig.width, height / mapConfig.height) * 0.94;
        this.placementWorld.style.setProperty('--placement-scale', scale.toFixed(4));
    };

    LiveShow.prototype.renderPlacementBooths = function () {
        if (!this.placementWorld) return;
        this.placementWorld.querySelectorAll('[data-placement-existing]').forEach((item) => item.remove());
        (this.state.booths || []).forEach((booth) => {
            const el = document.createElement('span');
            el.className = 'colt-live-show__placement-booth';
            el.dataset.placementExisting = booth.id;
            el.style.left = `${booth.x}px`;
            el.style.top = `${booth.y}px`;
            el.style.borderColor = booth.color || '#ff7f70';
            this.placementWorld.appendChild(el);
        });
    };

    LiveShow.prototype.updatePlacementCursor = function (event) {
        const rect = this.placementWorld.getBoundingClientRect();
        const scale = rect.width / mapConfig.width;
        const x = clamp((event.clientX - rect.left) / scale, 0, mapConfig.width);
        const y = clamp((event.clientY - rect.top) / scale, 0, mapConfig.height);
        const valid = this.isBoothSpotAvailable(x, y);
        this.selectedPlacement = { x: Math.round(x), y: Math.round(y), valid };
        this.placementWorld.querySelectorAll('[data-placement-cursor]').forEach((item) => item.remove());
        const cursor = document.createElement('span');
        cursor.className = `colt-live-show__placement-cursor ${valid ? 'is-valid' : ''}`;
        cursor.dataset.placementCursor = '1';
        cursor.style.left = `${x}px`;
        cursor.style.top = `${y}px`;
        this.placementWorld.appendChild(cursor);
        this.placementConfirm.disabled = !valid;
        this.placementGhost.classList.add('is-active');
        this.placementGhost.classList.toggle('is-valid', valid);
        this.placementGhost.style.left = `${x}px`;
        this.placementGhost.style.top = `${y}px`;
    };

    LiveShow.prototype.isBoothSpotAvailable = function (x, y) {
        const width = mapConfig.boothWidth;
        const height = mapConfig.boothHeight;
        const sideDepth = mapConfig.sideDepth || 430;
        const halfW = width / 2;
        const halfH = height / 2;
        if (x - halfW < 20 || x + halfW > mapConfig.width - 20 || y - halfH < 40 || y + halfH > mapConfig.height - 30) return false;
        const inSideZone = x <= sideDepth || x >= mapConfig.width - sideDepth || y <= sideDepth || y >= mapConfig.height - sideDepth;
        if (!inSideZone) return false;
        const ownBooth = this.getOwnBooth();
        const candidate = { left: x - halfW - 28, right: x + halfW + 28, top: y - halfH - 28, bottom: y + halfH + 28 };
        return !(this.state.booths || []).some((booth) => {
            if (ownBooth && booth.id === ownBooth.id) return false;
            const bw = booth.w || width;
            const bh = booth.h || height;
            const existing = {
                left: booth.x - bw / 2,
                right: booth.x + bw / 2,
                top: booth.y - bh / 2,
                bottom: booth.y + bh / 2,
            };
            return candidate.left < existing.right && candidate.right > existing.left && candidate.top < existing.bottom && candidate.bottom > existing.top;
        });
    };

    LiveShow.prototype.openBoothEditor = function () {
        const booth = this.getOwnBooth();
        const form = this.boothEditorForm;
        form.elements.title.value = booth?.title || this.vendorDraft.title || `${this.local.name} table`;
        form.elements.color.value = booth?.color || this.vendorDraft.color || this.local.color || '#86f7d4';
        form.elements.description.value = booth?.description || this.vendorDraft.description || '';
        for (let index = 0; index < 4; index += 1) {
            const item = booth?.items?.[index] || this.vendorDraft.items[index] || {};
            form.elements[`item_title_${index}`].value = item.title || '';
            form.elements[`item_price_${index}`].value = item.price || '';
            form.elements[`item_note_${index}`].value = item.note || '';
        }
        openDialog(this.boothEditor);
        form.onsubmit = async (event) => {
            event.preventDefault();
            const items = [];
            for (let index = 0; index < 4; index += 1) {
                const title = form.elements[`item_title_${index}`].value.trim();
                const price = form.elements[`item_price_${index}`].value.trim();
                const note = form.elements[`item_note_${index}`].value.trim();
                if (title || price || note) items.push({ title, price, note });
            }
            this.vendorDraft = {
                title: form.elements.title.value.trim(),
                color: form.elements.color.value,
                description: form.elements.description.value.trim(),
                items,
            };
            if (booth) {
                await this.saveBooth({ x: booth.x, y: booth.y });
                closeDialog(this.boothEditor);
            } else {
                closeDialog(this.boothEditor);
                this.openPlacement();
            }
        };
    };

    LiveShow.prototype.saveBooth = async function (position) {
        const payload = {
            ...this.authPayload(),
            ...position,
            title: this.vendorDraft.title || `${this.local.name} table`,
            color: this.vendorDraft.color || this.local.color,
            description: this.vendorDraft.description || 'עמדת אספנים עם פריטים נבחרים למכירה ושיחה בלייב.',
            items: this.vendorDraft.items || [],
        };
        try {
            this.state = await this.post('/booth', payload);
            this.renderState();
            this.setNotice('העמדה נשמרה ונפתחה למבקרים.');
        } catch (error) {
            this.setNotice(error.message || 'לא ניתן למקם שם עמדה.');
        }
    };

    LiveShow.prototype.openBoothModal = function (booth) {
        const owner = (this.state.sessions || []).find((session) => session.id === booth.owner);
        const items = booth.items && booth.items.length ? booth.items : [
            { title: 'Showcase item', price: 'Ask vendor', note: 'ה-vendor עדיין לא הזין פריטים.' },
        ];
        this.boothModalContent.innerHTML = `
            <div class="colt-live-show__booth-card" style="--booth-color:${escapeHtml(booth.color || '#86f7d4')}">
                <p class="colt-live-show__kicker">${escapeHtml(owner?.name || 'Vendor')}</p>
                <h2>${escapeHtml(booth.title || 'Vendor table')}</h2>
                <p>${escapeHtml(booth.description || '')}</p>
                <div class="colt-live-show__booth-items">
                    ${items.map((item) => `
                        <article>
                            <strong>${escapeHtml(item.title || 'Item')}</strong>
                            <span>${escapeHtml(item.price || '')}</span>
                            <small>${escapeHtml(item.note || '')}</small>
                        </article>
                    `).join('')}
                </div>
            </div>
        `;
        openDialog(this.boothModal);
    };

    LiveShow.prototype.renderChat = function () {
        const chats = this.state.chats || [];
        const pendingForMe = chats.filter((room) => room.status === 'pending' && room.target === this.local?.session);
        this.chatRequests.innerHTML = pendingForMe.map((room) => {
            const requester = (this.state.sessions || []).find((session) => session.id === room.requester);
            return `
                <div class="colt-live-show__chat-request">
                    <span>${escapeHtml(requester?.name || 'Collector')} מבקש שיחה</span>
                    <button type="button" data-room-id="${escapeHtml(room.id)}" data-chat-response="accept">אישור</button>
                    <button type="button" data-room-id="${escapeHtml(room.id)}" data-chat-response="decline">דחייה</button>
                </div>
            `;
        }).join('');

        if (pendingForMe.length) {
            this.chatPanel.hidden = false;
            this.setNotice('יש בקשת שיחה חדשה בעמדה שלך.');
        }

        const active = chats.find((room) => room.id === this.activeRoomId) || chats.find((room) => room.status === 'active') || chats.find((room) => room.status === 'pending' && room.requester === this.local?.session);
        if (!active) return;
        this.activeRoomId = active.id;
        this.chatPanel.hidden = false;
        const otherId = (active.participants || []).find((id) => id !== this.local.session);
        const other = (this.state.sessions || []).find((session) => session.id === otherId);
        this.chatTitle.textContent = active.status === 'active' ? `שיחה עם ${other?.name || 'Vendor'}` : 'ממתין לאישור vendor';
        this.chatForm.querySelector('input').disabled = active.status !== 'active';
        this.chatForm.querySelector('button').disabled = active.status !== 'active';
        this.chatLog.innerHTML = (active.messages || []).map((message) => `
            <div class="colt-live-show__chat-message ${message.from === this.local.session ? 'is-mine' : ''}">
                <strong>${escapeHtml(message.name || '')}</strong>
                ${escapeHtml(message.text || '')}
            </div>
        `).join('');
        this.chatLog.scrollTop = this.chatLog.scrollHeight;
    };

    LiveShow.prototype.openChat = function (roomId) {
        this.activeRoomId = roomId;
        this.chatPanel.hidden = false;
        this.renderChat();
    };

    LiveShow.prototype.getOwnBooth = function () {
        if (!this.local) return null;
        return (this.state.booths || []).find((booth) => booth.owner === this.local.session) || null;
    };

    LiveShow.prototype.post = async function (path, payload) {
        const response = await fetch(`${restUrl}${path}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'content-type': 'application/json' },
            body: JSON.stringify(payload || {}),
        });
        const json = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(json.message || `Request failed: ${path}`);
        return json;
    };

    LiveShow.prototype.authPayload = function () {
        return {
            session: this.local?.session || '',
            token: this.local?.token || '',
        };
    };

    LiveShow.prototype.setNotice = function (message) {
        if (this.notice) this.notice.textContent = message || '';
    };

    function spriteMarkup(name, color, role) {
        return `
            <span class="colt-live-show__sprite" data-role="${escapeHtml(role || '')}" style="--sprite-color:${escapeHtml(color || '#86f7d4')}">
                <span class="colt-live-show__sprite-name">${escapeHtml(name || '')}</span>
                <span class="colt-live-show__sprite-body"></span>
            </span>
        `;
    }

    function npcColor(tone) {
        const colors = {
            mint: '#86f7d4',
            gold: '#ffd36a',
            pink: '#ff6fae',
            blue: '#7cc8ff',
            violet: '#b796ff',
            red: '#ff8a70',
        };
        return colors[tone] || colors.mint;
    }

    function normalizeMoveKey(key) {
        const value = String(key || '').toLowerCase();
        if (value === 'arrowleft' || value === 'a') return 'left';
        if (value === 'arrowright' || value === 'd') return 'right';
        if (value === 'arrowup' || value === 'w') return 'up';
        if (value === 'arrowdown' || value === 's') return 'down';
        return '';
    }

    function isTyping(target) {
        if (!target) return false;
        return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable;
    }

    function setText(node, value) {
        if (node) node.textContent = String(value);
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, Number(value) || 0));
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openDialog(dialog) {
        if (!dialog) return;
        if (typeof dialog.showModal === 'function') {
            if (!dialog.open) dialog.showModal();
        } else {
            dialog.setAttribute('open', 'open');
        }
    }

    function closeDialog(dialog) {
        if (!dialog) return;
        if (typeof dialog.close === 'function') dialog.close();
        else dialog.removeAttribute('open');
    }
})();
