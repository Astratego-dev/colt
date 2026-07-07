(function () {
    const boot = () => {
        const roots = document.querySelectorAll('[data-colt-live-show]:not([data-live-ready])');
        roots.forEach((root) => new ColtLiveShow(root).init());
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }

    window.addEventListener('pageshow', boot);
    setTimeout(boot, 200);

    function ColtLiveShow(root) {
        this.root = root;
        this.config = window.COLT_LIVE_SHOW || {};
        this.restUrl = String(this.config.restUrl || '').replace(/\/$/, '');
        this.map = this.config.map || { width: 3200, height: 1760, boothWidth: 250, boothHeight: 150, sideDepth: 430 };
        this.npcs = Array.isArray(this.config.npcs) ? this.config.npcs : [];

        this.entry = root.querySelector('[data-live-entry]');
        this.error = root.querySelector('[data-live-entry-error]');
        this.startButton = root.querySelector('[data-live-start]');
        this.roleButtons = root.querySelectorAll('[data-role-option]');
        this.nameInput = root.querySelector('[data-live-name-input]');
        this.boothTitleInput = root.querySelector('[data-live-booth-title]');
        this.vendorFields = root.querySelector('[data-vendor-fields]');
        this.colorButtons = root.querySelectorAll('[data-color-option]');

        this.game = root.querySelector('[data-live-game]');
        this.viewport = root.querySelector('[data-live-viewport]');
        this.world = root.querySelector('[data-live-world]');
        this.fixturesLayer = root.querySelector('[data-live-fixtures]');
        this.boothsLayer = root.querySelector('[data-live-booths]');
        this.npcsLayer = root.querySelector('[data-live-npcs]');
        this.playersLayer = root.querySelector('[data-live-players]');
        this.notice = root.querySelector('[data-live-notice]');
        this.placeButton = root.querySelector('[data-live-place-booth]');
        this.editButton = root.querySelector('[data-live-edit-booth]');
        this.viewButton = root.querySelector('[data-live-view-booth]');
        this.requestButton = root.querySelector('[data-live-request-chat]');
        this.status = root.querySelector('[data-live-status]');
        this.mobileButtons = root.querySelectorAll('[data-move]');

        this.placement = root.querySelector('[data-live-placement]');
        this.placementMap = root.querySelector('[data-placement-map]');
        this.placementWorld = root.querySelector('[data-placement-world]');
        this.placementConfirm = root.querySelector('[data-placement-confirm]');

        this.boothModal = root.querySelector('[data-booth-modal]');
        this.boothModalContent = root.querySelector('[data-booth-modal-content]');
        this.boothEditor = root.querySelector('[data-booth-editor]');
        this.editorTitle = root.querySelector('[data-editor-title]');
        this.editorDescription = root.querySelector('[data-editor-description]');
        this.editorSave = root.querySelector('[data-editor-save]');

        this.chatPanel = root.querySelector('[data-live-chat]');
        this.chatTitle = root.querySelector('[data-chat-title]');
        this.chatRequests = root.querySelector('[data-chat-requests]');
        this.chatLog = root.querySelector('[data-chat-log]');
        this.chatMessage = root.querySelector('[data-chat-message]');
        this.chatSend = root.querySelector('[data-chat-send]');

        this.role = 'collector';
        this.color = '#86f7d4';
        this.state = { sessions: [], booths: [], chats: [] };
        this.local = null;
        this.keys = new Set();
        this.mobileKeys = new Set();
        this.target = null;
        this.camera = { x: 0, y: 0, scale: 0.82 };
        this.nearBooth = null;
        this.selectedPlacement = null;
        this.activeRoomId = '';
        this.vendorDraft = {
            title: '',
            color: this.color,
            description: 'עמדת אספנים עם סינגלים, סלאבים ומוצרים מיוחדים למכירה בלייב.',
            items: [
                { title: 'Featured slab', price: 'Ask', note: 'PSA / CGC showcase' },
                { title: 'Singles binder', price: 'Live offer', note: 'Pokemon / One Piece' },
                { title: 'Sealed product', price: 'Limited', note: 'Packs, boxes and bundles' },
            ],
        };
        this.started = false;
        this.joining = false;
        this.lastFrame = performance.now();
        this.lastHeartbeat = 0;
        this.lastState = 0;
    }

    ColtLiveShow.prototype.init = function () {
        this.root.dataset.liveReady = '1';
        this.root.addEventListener('submit', (event) => event.preventDefault(), true);
        this.root.style.setProperty('--live-map-width', `${this.map.width}px`);
        this.root.style.setProperty('--live-map-height', `${this.map.height}px`);
        this.world.style.width = `${this.map.width}px`;
        this.world.style.height = `${this.map.height}px`;

        this.renderFixtures();
        this.renderNpcs();
        this.bindEntry();
        this.bindMovement();
        this.bindPlacement();
        this.bindBooths();
        this.bindEditor();
        this.bindChat();
        this.updatePlacementScale();
        window.addEventListener('resize', () => {
            this.updateCamera(true);
            this.updatePlacementScale();
        });

        if (!this.restUrl) {
            this.showEntryError('החיבור ל-REST API לא נטען. צריך לעדכן/לרענן את התוסף באתר.');
        }
    };

    ColtLiveShow.prototype.bindEntry = function () {
        this.roleButtons.forEach((button) => {
            button.addEventListener('click', () => this.selectRole(button.dataset.roleOption || 'collector'));
        });

        this.colorButtons.forEach((button) => {
            button.addEventListener('click', () => this.selectColor(button.dataset.colorOption || '#86f7d4'));
        });

        this.startButton.addEventListener('click', () => this.join());
        this.nameInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.join();
            }
        });
        if (this.boothTitleInput) {
            this.boothTitleInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.join();
                }
            });
        }
    };

    ColtLiveShow.prototype.selectRole = function (role) {
        this.role = role === 'vendor' ? 'vendor' : 'collector';
        this.root.dataset.liveRole = this.role;
        this.roleButtons.forEach((button) => {
            const active = button.dataset.roleOption === this.role;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        this.vendorFields.hidden = this.role !== 'vendor';
        this.clearEntryError();
    };

    ColtLiveShow.prototype.selectColor = function (color) {
        this.color = color;
        this.vendorDraft.color = color;
        this.colorButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.colorOption === color);
        });
    };

    ColtLiveShow.prototype.join = async function () {
        if (this.joining || this.started) return;
        const name = String(this.nameInput.value || '').trim();
        if (!name) {
            this.showEntryError('צריך להכניס שם תצוגה כדי להיכנס ללייב.');
            this.nameInput.focus();
            return;
        }
        if (!this.restUrl) {
            this.showEntryError('החיבור ללייב לא זמין כרגע. בדוק שהתוסף עודכן ושה-REST API פעיל.');
            return;
        }

        this.joining = true;
        this.startButton.disabled = true;
        this.startButton.querySelector('span').textContent = 'נכנס לאולם...';
        this.clearEntryError();

        this.vendorDraft.title = String((this.boothTitleInput && this.boothTitleInput.value) || '').trim();
        this.vendorDraft.color = this.color;

        try {
            const response = await this.post('/join', {
                role: this.role,
                name,
                color: this.color,
            });
            const session = findById(response.state.sessions || [], response.session);
            this.local = {
                session: response.session,
                token: response.token,
                role: this.role,
                name,
                color: this.color,
                x: session ? session.x : this.map.width / 2,
                y: session ? session.y : this.map.height / 2,
                dir: 'down',
            };
            this.state = response.state;
            this.enterGame();
        } catch (error) {
            this.joining = false;
            this.startButton.disabled = false;
            this.startButton.querySelector('span').textContent = 'כניסה ללייב';
            this.showEntryError(error.message || 'לא הצלחתי להיכנס ללייב. נסה לרענן את העמוד.');
        }
    };

    ColtLiveShow.prototype.enterGame = function () {
        this.started = true;
        this.entry.hidden = true;
        this.game.hidden = false;
        this.root.classList.add('is-live');
        this.root.querySelector('[data-live-name]').textContent = this.local.name;
        this.root.querySelector('[data-live-role]').textContent = this.local.role === 'vendor' ? 'Vendor' : 'Collector';
        this.placeButton.hidden = this.local.role !== 'vendor';
        this.editButton.hidden = this.local.role !== 'vendor';
        this.renderState();
        this.updateCamera(true);
        this.loop(performance.now());
        this.setNotice(this.local.role === 'vendor'
            ? 'ברוך הבא. בחר מקום פנוי בצדדי האולם כדי לפתוח את העמדה שלך.'
            : 'ברוך הבא לאולם. הסתובב בין העמדות, התקרב ולחץ כדי לפתוח שיחה.');
        if (this.local.role === 'vendor') this.openPlacement();
    };

    ColtLiveShow.prototype.bindMovement = function () {
        window.addEventListener('keydown', (event) => {
            if (!this.started || isTyping(event.target)) return;
            const key = normalizeMoveKey(event.key);
            if (!key) return;
            event.preventDefault();
            this.keys.add(key);
            this.target = null;
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
                this.target = null;
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

        this.viewport.addEventListener('click', (event) => {
            if (!this.started || event.target.closest('button, [data-booth-id], [data-npc-id]')) return;
            this.target = this.screenToWorld(event.clientX, event.clientY);
        });

        window.addEventListener('pagehide', () => {
            if (!this.local) return;
            try {
                fetch(`${this.restUrl}/leave`, {
                    method: 'POST',
                    keepalive: true,
                    headers: { 'content-type': 'application/json' },
                    body: JSON.stringify(this.authPayload()),
                });
            } catch (error) {}
        });
    };

    ColtLiveShow.prototype.loop = function (now) {
        if (!this.started || !this.local) return;
        const dt = Math.min(0.05, (now - this.lastFrame) / 1000);
        this.lastFrame = now;
        this.updateMovement(dt);
        this.updateCamera(false);
        this.updateNearbyBooth();

        if (now - this.lastHeartbeat > 650) {
            this.lastHeartbeat = now;
            this.heartbeat();
        }
        if (now - this.lastState > 1400) {
            this.lastState = now;
            this.fetchState();
        }

        requestAnimationFrame((frameTime) => this.loop(frameTime));
    };

    ColtLiveShow.prototype.updateMovement = function (dt) {
        const moveKeys = new Set([...this.keys, ...this.mobileKeys]);
        let dx = 0;
        let dy = 0;
        if (moveKeys.has('left')) dx -= 1;
        if (moveKeys.has('right')) dx += 1;
        if (moveKeys.has('up')) dy -= 1;
        if (moveKeys.has('down')) dy += 1;

        if (!dx && !dy && this.target) {
            const tx = this.target.x - this.local.x;
            const ty = this.target.y - this.local.y;
            const dist = Math.hypot(tx, ty);
            if (dist < 12) {
                this.target = null;
                return;
            }
            dx = tx / dist;
            dy = ty / dist;
        }

        if (!dx && !dy) return;
        const len = Math.hypot(dx, dy) || 1;
        const speed = this.local.role === 'vendor' ? 250 : 285;
        this.local.x = clamp(this.local.x + (dx / len) * speed * dt, 70, this.map.width - 70);
        this.local.y = clamp(this.local.y + (dy / len) * speed * dt, 135, this.map.height - 80);
        this.local.dir = Math.abs(dx) > Math.abs(dy) ? (dx < 0 ? 'left' : 'right') : (dy < 0 ? 'up' : 'down');
        this.updateLocalPlayerPosition();
    };

    ColtLiveShow.prototype.updateCamera = function (immediate) {
        if (!this.local) return;
        const vw = this.viewport.clientWidth || window.innerWidth;
        const vh = this.viewport.clientHeight || window.innerHeight;
        const desiredScale = vw < 720 ? 0.58 : vw < 1180 ? 0.72 : 0.84;
        const desiredX = clamp(vw / 2 - this.local.x * desiredScale, Math.min(0, vw - this.map.width * desiredScale), 0);
        const desiredY = clamp(vh / 2 - this.local.y * desiredScale, Math.min(0, vh - this.map.height * desiredScale), 0);
        if (immediate) {
            this.camera = { x: desiredX, y: desiredY, scale: desiredScale };
        } else {
            this.camera.x += (desiredX - this.camera.x) * 0.14;
            this.camera.y += (desiredY - this.camera.y) * 0.14;
            this.camera.scale += (desiredScale - this.camera.scale) * 0.1;
        }
        this.world.style.transform = `translate3d(${this.camera.x}px, ${this.camera.y}px, 0) scale(${this.camera.scale})`;
    };

    ColtLiveShow.prototype.screenToWorld = function (clientX, clientY) {
        const rect = this.viewport.getBoundingClientRect();
        return {
            x: clamp((clientX - rect.left - this.camera.x) / this.camera.scale, 70, this.map.width - 70),
            y: clamp((clientY - rect.top - this.camera.y) / this.camera.scale, 135, this.map.height - 80),
        };
    };

    ColtLiveShow.prototype.heartbeat = async function () {
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

    ColtLiveShow.prototype.fetchState = async function () {
        if (!this.local) return;
        try {
            const url = new URL(`${this.restUrl}/state`);
            url.searchParams.set('session', this.local.session);
            url.searchParams.set('token', this.local.token);
            const response = await fetch(url.href, { credentials: 'same-origin' });
            const json = await response.json();
            if (!response.ok) throw new Error(json.message || 'state failed');
            this.state = json;
            this.renderState();
        } catch (error) {}
    };

    ColtLiveShow.prototype.renderState = function () {
        this.renderStats();
        this.renderBooths();
        this.renderPlayers();
        this.renderPlacementBooths();
        this.renderChat();
    };

    ColtLiveShow.prototype.renderStats = function () {
        const sessions = this.state.sessions || [];
        const vendors = sessions.filter((item) => item.role === 'vendor').length;
        setText(this.root.querySelector('[data-live-count="collectors"]'), sessions.length - vendors);
        setText(this.root.querySelector('[data-live-count="vendors"]'), vendors);
        setText(this.root.querySelector('[data-live-count="booths"]'), (this.state.booths || []).length);
        setText(this.status, `LIVE / ${sessions.length} ONLINE`);
    };

    ColtLiveShow.prototype.renderFixtures = function () {
        const fixtures = [
            { x: 360, y: 315, type: 'showcase', title: 'PSA / CGC WALL', note: 'graded cards' },
            { x: 1010, y: 285, type: 'packs', title: 'SEALED BAR', note: 'boosters & boxes' },
            { x: 2560, y: 320, type: 'vault', title: 'HIGH VALUE', note: 'slabs display' },
            { x: 440, y: 1450, type: 'trade', title: 'TRADE ZONE', note: 'binder checks' },
            { x: 1680, y: 1480, type: 'stream', title: 'LIVE STAGE', note: 'whatnot energy' },
            { x: 2820, y: 1330, type: 'mystery', title: 'MYSTERY DESK', note: 'pull station' },
        ];
        this.fixturesLayer.innerHTML = fixtures.map((fixture) => `
            <article class="colt-live-show__fixture colt-live-show__fixture--${escapeHtml(fixture.type)}" style="left:${fixture.x}px;top:${fixture.y}px;z-index:${Math.round(fixture.y)}">
                <strong>${escapeHtml(fixture.title)}</strong>
                <span>${escapeHtml(fixture.note)}</span>
                <i></i>
            </article>
        `).join('');
    };

    ColtLiveShow.prototype.renderBooths = function () {
        const booths = this.state.booths || [];
        this.boothsLayer.innerHTML = booths.map((booth) => `
            <article class="colt-live-show__booth ${this.nearBooth && this.nearBooth.id === booth.id ? 'is-near' : ''}" data-booth-id="${escapeHtml(booth.id)}" style="left:${booth.x}px;top:${booth.y}px;z-index:${Math.round(booth.y)};--booth-color:${escapeHtml(booth.color || '#86f7d4')}">
                <div class="colt-live-show__booth-back"></div>
                <strong>${escapeHtml(booth.title || 'Vendor table')}</strong>
                <span>${escapeHtml(booth.description || '')}</span>
                <small>${escapeHtml(String((booth.items || []).length || 0))} פריטים</small>
            </article>
        `).join('');
    };

    ColtLiveShow.prototype.renderPlayers = function () {
        const sessions = this.state.sessions || [];
        const localId = this.local ? this.local.session : '';
        this.playersLayer.innerHTML = sessions.map((session) => {
            const isLocal = session.id === localId;
            const x = isLocal ? this.local.x : session.x;
            const y = isLocal ? this.local.y : session.y;
            return `
                <div class="colt-live-show__player ${isLocal ? 'is-local' : ''}" data-player-id="${escapeHtml(session.id)}" style="left:${x}px;top:${y}px;z-index:${Math.round(y + 15)}">
                    ${spriteMarkup(session.name, session.color, session.role, isLocal)}
                </div>
            `;
        }).join('');
    };

    ColtLiveShow.prototype.updateLocalPlayerPosition = function () {
        if (!this.local) return;
        const el = this.playersLayer.querySelector(`[data-player-id="${cssEscape(this.local.session)}"]`);
        if (!el) return;
        el.style.left = `${this.local.x}px`;
        el.style.top = `${this.local.y}px`;
        el.style.zIndex = String(Math.round(this.local.y + 15));
    };

    ColtLiveShow.prototype.renderNpcs = function () {
        this.npcsLayer.innerHTML = this.npcs.map((npc, index) => `
            <button type="button" class="colt-live-show__npc" data-npc-id="${escapeHtml(npc.id)}" style="left:${npc.x}px;top:${npc.y}px;z-index:${Math.round(npc.y + 10)};--sprite-color:${npcColor(npc.tone)};animation-delay:${(index % 6) * -0.22}s">
                ${spriteMarkup(npc.name, npcColor(npc.tone), 'npc', false)}
                <span class="colt-live-show__npc-bubble">${escapeHtml(npc.line)}</span>
            </button>
        `).join('');
        this.npcsLayer.addEventListener('click', (event) => {
            const npc = event.target.closest('[data-npc-id]');
            if (!npc) return;
            npc.classList.add('is-speaking');
            setTimeout(() => npc.classList.remove('is-speaking'), 2600);
        }, { once: false });
    };

    ColtLiveShow.prototype.updateNearbyBooth = function () {
        let nearest = null;
        let distance = Infinity;
        (this.state.booths || []).forEach((booth) => {
            const d = Math.hypot((booth.x || 0) - this.local.x, (booth.y || 0) - this.local.y);
            if (d < distance) {
                nearest = booth;
                distance = d;
            }
        });
        const previousId = this.nearBooth ? this.nearBooth.id : '';
        this.nearBooth = distance < 250 ? nearest : null;
        const own = this.nearBooth && this.nearBooth.owner === this.local.session;
        this.viewButton.disabled = !this.nearBooth;
        this.requestButton.disabled = !this.nearBooth || own;
        this.viewButton.textContent = this.nearBooth ? `צפייה: ${this.nearBooth.title}` : 'צפייה בעמדה';
        this.requestButton.textContent = own ? 'זו העמדה שלך' : 'בקשת שיחה';
        const currentId = this.nearBooth ? this.nearBooth.id : '';
        if (previousId !== currentId) this.renderBooths();
    };

    ColtLiveShow.prototype.bindBooths = function () {
        this.boothsLayer.addEventListener('click', (event) => {
            const boothEl = event.target.closest('[data-booth-id]');
            if (!boothEl) return;
            const booth = findById(this.state.booths || [], boothEl.dataset.boothId);
            if (booth) this.openBoothModal(booth);
        });
        this.viewButton.addEventListener('click', () => {
            if (this.nearBooth) this.openBoothModal(this.nearBooth);
        });
        this.requestButton.addEventListener('click', async () => {
            if (!this.nearBooth) return;
            try {
                const response = await this.post('/chat/request', {
                    ...this.authPayload(),
                    boothId: this.nearBooth.id,
                });
                this.state = response.state || this.state;
                this.activeRoomId = response.roomId || this.activeRoomId;
                this.renderState();
                this.openChat(this.activeRoomId);
                this.setNotice('בקשת השיחה נשלחה ל-vendor.');
            } catch (error) {
                this.setNotice(error.message || 'לא ניתן לשלוח בקשת שיחה כרגע.');
            }
        });
    };

    ColtLiveShow.prototype.openBoothModal = function (booth) {
        const owner = findById(this.state.sessions || [], booth.owner);
        const items = booth.items && booth.items.length ? booth.items : [
            { title: 'Showcase item', price: 'Ask vendor', note: 'ה-vendor עדיין לא הזין פריטים.' },
        ];
        this.boothModalContent.innerHTML = `
            <div class="colt-live-show__booth-card" style="--booth-color:${escapeHtml(booth.color || '#86f7d4')}">
                <p class="colt-live-show__kicker">${escapeHtml(owner ? owner.name : 'Vendor')}</p>
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

    ColtLiveShow.prototype.bindPlacement = function () {
        this.placeButton.addEventListener('click', () => this.openPlacement());
        this.root.querySelector('[data-placement-close]').addEventListener('click', () => this.closePlacement());
        this.placementMap.addEventListener('pointermove', (event) => this.updatePlacementCursor(event));
        this.placementMap.addEventListener('click', (event) => {
            this.updatePlacementCursor(event);
            this.placementConfirm.disabled = !(this.selectedPlacement && this.selectedPlacement.valid);
        });
        this.placementConfirm.addEventListener('click', async () => {
            if (!this.selectedPlacement || !this.selectedPlacement.valid) return;
            await this.saveBooth({ x: this.selectedPlacement.x, y: this.selectedPlacement.y });
            this.closePlacement();
        });
    };

    ColtLiveShow.prototype.openPlacement = function () {
        this.placement.hidden = false;
        this.selectedPlacement = null;
        this.placementConfirm.disabled = true;
        this.updatePlacementScale();
        this.renderPlacementBooths();
        this.setNotice('בחר אזור ירוק בצדדי האולם. אי אפשר לחסום את המעבר המרכזי.');
    };

    ColtLiveShow.prototype.closePlacement = function () {
        this.placement.hidden = true;
    };

    ColtLiveShow.prototype.updatePlacementScale = function () {
        const width = Math.max(320, this.placementMap.clientWidth || window.innerWidth);
        const height = Math.max(240, this.placementMap.clientHeight || window.innerHeight);
        const scale = Math.min(width / this.map.width, height / this.map.height) * 0.94;
        this.placementWorld.style.setProperty('--placement-scale', scale.toFixed(4));
    };

    ColtLiveShow.prototype.renderPlacementBooths = function () {
        this.placementWorld.querySelectorAll('[data-placement-existing], [data-placement-cursor]').forEach((node) => node.remove());
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

    ColtLiveShow.prototype.updatePlacementCursor = function (event) {
        const rect = this.placementWorld.getBoundingClientRect();
        const scale = rect.width / this.map.width || 1;
        const x = clamp((event.clientX - rect.left) / scale, 0, this.map.width);
        const y = clamp((event.clientY - rect.top) / scale, 0, this.map.height);
        const valid = this.isBoothSpotAvailable(x, y);
        this.selectedPlacement = { x: Math.round(x), y: Math.round(y), valid };
        this.placementWorld.querySelectorAll('[data-placement-cursor]').forEach((node) => node.remove());
        const cursor = document.createElement('span');
        cursor.className = `colt-live-show__placement-cursor ${valid ? 'is-valid' : ''}`;
        cursor.dataset.placementCursor = '1';
        cursor.style.left = `${x}px`;
        cursor.style.top = `${y}px`;
        this.placementWorld.appendChild(cursor);
        this.placementConfirm.disabled = !valid;
    };

    ColtLiveShow.prototype.isBoothSpotAvailable = function (x, y) {
        const width = this.map.boothWidth;
        const height = this.map.boothHeight;
        const sideDepth = this.map.sideDepth || 430;
        const halfW = width / 2;
        const halfH = height / 2;
        if (x - halfW < 20 || x + halfW > this.map.width - 20 || y - halfH < 40 || y + halfH > this.map.height - 30) return false;
        const inSideZone = x <= sideDepth || x >= this.map.width - sideDepth || y <= sideDepth || y >= this.map.height - sideDepth;
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

    ColtLiveShow.prototype.bindEditor = function () {
        this.editButton.addEventListener('click', () => this.openBoothEditor());
        this.root.querySelector('[data-editor-close]').addEventListener('click', () => closeDialog(this.boothEditor));
        this.root.querySelector('[data-modal-close]').addEventListener('click', () => closeDialog(this.boothModal));
        this.editorSave.addEventListener('click', async () => {
            const items = [];
            for (let index = 0; index < 4; index += 1) {
                const title = valueOf(this.root.querySelector(`[data-editor-item-title="${index}"]`));
                const price = valueOf(this.root.querySelector(`[data-editor-item-price="${index}"]`));
                const note = valueOf(this.root.querySelector(`[data-editor-item-note="${index}"]`));
                if (title || price || note) items.push({ title, price, note });
            }
            this.vendorDraft = {
                title: valueOf(this.editorTitle) || `${this.local.name} table`,
                color: this.vendorDraft.color || this.color,
                description: valueOf(this.editorDescription) || 'עמדת אספנים עם פריטים נבחרים למכירה ושיחה בלייב.',
                items,
            };
            const booth = this.getOwnBooth();
            closeDialog(this.boothEditor);
            if (booth) await this.saveBooth({ x: booth.x, y: booth.y });
            else this.openPlacement();
        });
    };

    ColtLiveShow.prototype.openBoothEditor = function () {
        const booth = this.getOwnBooth();
        this.editorTitle.value = booth ? booth.title : (this.vendorDraft.title || `${this.local.name} table`);
        this.editorDescription.value = booth ? booth.description : this.vendorDraft.description;
        for (let index = 0; index < 4; index += 1) {
            const item = booth && booth.items ? booth.items[index] : this.vendorDraft.items[index];
            const title = this.root.querySelector(`[data-editor-item-title="${index}"]`);
            const price = this.root.querySelector(`[data-editor-item-price="${index}"]`);
            const note = this.root.querySelector(`[data-editor-item-note="${index}"]`);
            title.value = item ? item.title || '' : '';
            price.value = item ? item.price || '' : '';
            note.value = item ? item.note || '' : '';
        }
        openDialog(this.boothEditor);
    };

    ColtLiveShow.prototype.saveBooth = async function (position) {
        try {
            this.state = await this.post('/booth', {
                ...this.authPayload(),
                ...position,
                title: this.vendorDraft.title || `${this.local.name} table`,
                color: this.vendorDraft.color || this.color,
                description: this.vendorDraft.description,
                items: this.vendorDraft.items || [],
            });
            this.renderState();
            this.setNotice('העמדה נשמרה ונפתחה למבקרים.');
        } catch (error) {
            this.setNotice(error.message || 'לא ניתן למקם שם עמדה.');
        }
    };

    ColtLiveShow.prototype.bindChat = function () {
        this.root.querySelector('[data-chat-close]').addEventListener('click', () => {
            this.chatPanel.hidden = true;
        });
        this.chatRequests.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-chat-response]');
            if (!button) return;
            try {
                this.state = await this.post('/chat/respond', {
                    ...this.authPayload(),
                    roomId: button.dataset.roomId,
                    accept: button.dataset.chatResponse === 'accept',
                });
                if (button.dataset.chatResponse === 'accept') {
                    this.activeRoomId = button.dataset.roomId;
                    this.openChat(this.activeRoomId);
                }
                this.renderState();
            } catch (error) {
                this.setNotice(error.message || 'לא הצלחתי לעדכן את בקשת השיחה.');
            }
        });
        this.chatSend.addEventListener('click', () => this.sendChatMessage());
        this.chatMessage.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.sendChatMessage();
            }
        });
    };

    ColtLiveShow.prototype.sendChatMessage = async function () {
        const message = valueOf(this.chatMessage);
        if (!message || !this.activeRoomId) return;
        this.chatMessage.value = '';
        try {
            this.state = await this.post('/chat/message', {
                ...this.authPayload(),
                roomId: this.activeRoomId,
                message,
            });
            this.renderChat();
        } catch (error) {
            this.setNotice(error.message || 'ההודעה לא נשלחה.');
        }
    };

    ColtLiveShow.prototype.renderChat = function () {
        const chats = this.state.chats || [];
        const pendingForMe = chats.filter((room) => room.status === 'pending' && room.target === (this.local && this.local.session));
        this.chatRequests.innerHTML = pendingForMe.map((room) => {
            const requester = findById(this.state.sessions || [], room.requester);
            return `
                <div class="colt-live-show__chat-request">
                    <span>${escapeHtml(requester ? requester.name : 'Collector')} מבקש שיחה</span>
                    <button type="button" data-room-id="${escapeHtml(room.id)}" data-chat-response="accept">אישור</button>
                    <button type="button" data-room-id="${escapeHtml(room.id)}" data-chat-response="decline">דחייה</button>
                </div>
            `;
        }).join('');

        if (pendingForMe.length) {
            this.chatPanel.hidden = false;
            this.setNotice('יש בקשת שיחה חדשה בעמדה שלך.');
        }

        const active = chats.find((room) => room.id === this.activeRoomId)
            || chats.find((room) => room.status === 'active' && room.participants && room.participants.includes(this.local.session))
            || chats.find((room) => room.status === 'pending' && room.requester === this.local.session);
        if (!active) return;
        this.activeRoomId = active.id;
        this.chatPanel.hidden = false;
        const otherId = (active.participants || []).find((id) => id !== this.local.session);
        const other = findById(this.state.sessions || [], otherId);
        this.chatTitle.textContent = active.status === 'active' ? `שיחה עם ${other ? other.name : 'Vendor'}` : 'ממתין לאישור vendor';
        this.chatMessage.disabled = active.status !== 'active';
        this.chatSend.disabled = active.status !== 'active';
        this.chatLog.innerHTML = (active.messages || []).map((message) => {
            const sender = findById(this.state.sessions || [], message.from);
            return `
                <div class="colt-live-show__chat-message ${message.from === this.local.session ? 'is-mine' : ''}">
                    <strong>${escapeHtml(sender ? sender.name : 'Guest')}</strong>
                    <span>${escapeHtml(message.text)}</span>
                </div>
            `;
        }).join('');
        this.chatLog.scrollTop = this.chatLog.scrollHeight;
    };

    ColtLiveShow.prototype.openChat = function (roomId) {
        this.activeRoomId = roomId;
        this.chatPanel.hidden = false;
        this.renderChat();
    };

    ColtLiveShow.prototype.post = async function (path, payload) {
        let response;
        try {
            response = await fetch(`${this.restUrl}${path}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'content-type': 'application/json' },
                body: JSON.stringify(payload || {}),
            });
        } catch (error) {
            throw new Error('לא ניתן להתחבר לשרת הלייב כרגע.');
        }
        const json = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(json.message || 'השרת החזיר שגיאה. נסה שוב.');
        }
        return json;
    };

    ColtLiveShow.prototype.authPayload = function () {
        return {
            session: this.local ? this.local.session : '',
            token: this.local ? this.local.token : '',
        };
    };

    ColtLiveShow.prototype.getOwnBooth = function () {
        if (!this.local) return null;
        return (this.state.booths || []).find((booth) => booth.owner === this.local.session) || null;
    };

    ColtLiveShow.prototype.showEntryError = function (message) {
        this.error.textContent = message;
        this.error.classList.add('is-visible');
    };

    ColtLiveShow.prototype.clearEntryError = function () {
        this.error.textContent = '';
        this.error.classList.remove('is-visible');
    };

    ColtLiveShow.prototype.setNotice = function (message) {
        if (this.notice) this.notice.textContent = message || '';
    };

    function spriteMarkup(name, color, role, local) {
        return `
            <span class="colt-live-show__sprite ${local ? 'is-local' : ''}" data-role="${escapeHtml(role || '')}" style="--sprite-color:${escapeHtml(color || '#86f7d4')}">
                <span class="colt-live-show__sprite-shadow"></span>
                <span class="colt-live-show__sprite-name">${escapeHtml(name || '')}</span>
                <span class="colt-live-show__sprite-body">
                    <i></i>
                </span>
            </span>
        `;
    }

    function normalizeMoveKey(key) {
        const value = String(key || '').toLowerCase();
        if (value === 'arrowleft' || value === 'a') return 'left';
        if (value === 'arrowright' || value === 'd') return 'right';
        if (value === 'arrowup' || value === 'w') return 'up';
        if (value === 'arrowdown' || value === 's') return 'down';
        return '';
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

    function isTyping(target) {
        if (!target) return false;
        return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable;
    }

    function setText(node, value) {
        if (node) node.textContent = String(value);
    }

    function valueOf(node) {
        return String(node && node.value ? node.value : '').trim();
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, Number(value) || 0));
    }

    function findById(items, id) {
        return (items || []).find((item) => item && item.id === id);
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(value);
        return String(value).replace(/["\\]/g, '\\$&');
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
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
