<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = Colt_Experience::logo_url();
$title = (string) ($atts['title'] ?? 'COLT Live Show');
$subtitle = (string) ($atts['subtitle'] ?? 'יריד קלפים חי: אספנים נכנסים כאורחים, vendors מקימים עמדות, וכולם מסתובבים באולם בזמן אמת.');
$map = Colt_Experience::live_show_map();
$live_show_config = [
    'restUrl' => esc_url_raw(rest_url('colt/v1/live-show')),
    'map' => $map,
    'npcs' => Colt_Experience::live_show_npcs(),
    'version' => COLT_EXPERIENCE_VERSION,
];
?>

<section
    class="colt-live-show"
    dir="rtl"
    data-colt-live-show
    data-map-width="<?php echo esc_attr((string) $map['width']); ?>"
    data-map-height="<?php echo esc_attr((string) $map['height']); ?>"
>
    <noscript>
        <div class="colt-live-show__noscript">כדי להיכנס ל-COLT Live Show צריך להפעיל JavaScript בדפדפן.</div>
    </noscript>

    <div class="colt-live-show__scanlines" aria-hidden="true"></div>

    <header class="colt-live-show__shellbar">
        <a class="colt-live-show__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-live-show__shell-title">
            <strong><?php echo esc_html($title); ?></strong>
            <small data-live-status>LOBBY READY</small>
        </div>
        <div class="colt-live-show__stats" aria-label="נתוני לייב">
            <span><b data-live-count="collectors">0</b> אספנים</span>
            <span><b data-live-count="vendors">0</b> vendors</span>
            <span><b data-live-count="booths">0</b> עמדות</span>
        </div>
    </header>

    <div class="colt-live-show__entry" data-live-entry>
        <div class="colt-live-show__arcade" aria-hidden="true">
            <div class="colt-live-show__arcade-wall">
                <span></span><span></span><span></span>
            </div>
            <div class="colt-live-show__arcade-floor"></div>
            <div class="colt-live-show__arcade-table colt-live-show__arcade-table--a">
                <i></i><i></i><i></i>
            </div>
            <div class="colt-live-show__arcade-table colt-live-show__arcade-table--b">
                <i></i><i></i><i></i>
            </div>
            <div class="colt-live-show__arcade-card colt-live-show__arcade-card--one"></div>
            <div class="colt-live-show__arcade-card colt-live-show__arcade-card--two"></div>
            <div class="colt-live-show__arcade-card colt-live-show__arcade-card--three"></div>
            <div class="colt-live-show__arcade-bot colt-live-show__arcade-bot--one"></div>
            <div class="colt-live-show__arcade-bot colt-live-show__arcade-bot--two"></div>
            <div class="colt-live-show__arcade-bot colt-live-show__arcade-bot--three"></div>
            <div class="colt-live-show__arcade-sign">LIVE HALL OPEN</div>
        </div>

        <div class="colt-live-show__entry-panel">
            <p class="colt-live-show__kicker">COLT LIVE SHOW</p>
            <h1><?php echo esc_html($title); ?></h1>
            <p><?php echo esc_html($subtitle); ?></p>

            <div class="colt-live-show__entry-error" data-live-entry-error role="alert" aria-live="polite"></div>

            <div class="colt-live-show__role-picker" aria-label="בחירת כניסה">
                <button class="colt-live-show__role is-active" type="button" data-role-option="collector" aria-pressed="true">
                    <span class="colt-live-show__role-sprite" aria-hidden="true"></span>
                    <span>
                        <strong>לקוח / מבקר</strong>
                        <small>נכנס לאולם, מתקרב לעמדות, פותח כרטיס vendor ומבקש שיחה.</small>
                    </span>
                </button>
                <button class="colt-live-show__role" type="button" data-role-option="vendor" aria-pressed="false">
                    <span class="colt-live-show__role-sprite" aria-hidden="true"></span>
                    <span>
                        <strong>Vendor עם עמדה</strong>
                        <small>ממקם שולחן בצדדים, בוחר צבע ומציג מוצרים למבקרים.</small>
                    </span>
                </button>
            </div>

            <div class="colt-live-show__entry-fields">
                <label>
                    <span>שם תצוגה</span>
                    <input type="text" data-live-name-input maxlength="28" placeholder="לדוגמה: Eli Cards" autocomplete="off">
                </label>
                <div class="colt-live-show__vendor-fields" data-vendor-fields hidden>
                    <label>
                        <span>כותרת העמדה</span>
                        <input type="text" data-live-booth-title maxlength="42" placeholder="Pokemon slabs / One Piece singles" autocomplete="off">
                    </label>
                    <div class="colt-live-show__color-palette" aria-label="צבע עמדה">
                        <button type="button" data-color-option="#86f7d4" class="is-active" style="--swatch:#86f7d4"></button>
                        <button type="button" data-color-option="#ffd36a" style="--swatch:#ffd36a"></button>
                        <button type="button" data-color-option="#ff6fae" style="--swatch:#ff6fae"></button>
                        <button type="button" data-color-option="#7cc8ff" style="--swatch:#7cc8ff"></button>
                    </div>
                </div>
            </div>

            <button class="colt-live-show__start" type="button" data-live-start>
                <span>כניסה ללייב</span>
                <i aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="colt-live-show__game" data-live-game aria-label="COLT Live Show game world" hidden>
        <div class="colt-live-show__viewport" data-live-viewport>
            <div class="colt-live-show__world" data-live-world>
                <div class="colt-live-show__floor" aria-hidden="true"></div>
                <div class="colt-live-show__aisle" aria-hidden="true">
                    <span>MAIN AISLE</span>
                </div>
                <div class="colt-live-show__layer" data-live-fixtures></div>
                <div class="colt-live-show__layer" data-live-booths></div>
                <div class="colt-live-show__layer" data-live-npcs></div>
                <div class="colt-live-show__layer" data-live-players></div>
            </div>
        </div>

        <aside class="colt-live-show__hud">
            <div class="colt-live-show__panel">
                <strong data-live-name>Guest</strong>
                <small data-live-role>Collector</small>
                <p>WASD / חצים לתנועה, או לחיצה על הרצפה. התקרב לעמדה כדי לפתוח תצוגה או לבקש שיחה.</p>
            </div>
            <div class="colt-live-show__actions">
                <button type="button" data-live-place-booth hidden>מיקום עמדה</button>
                <button type="button" data-live-edit-booth hidden>עריכת עמדה</button>
                <button type="button" data-live-view-booth disabled>צפייה בעמדה</button>
                <button type="button" data-live-request-chat disabled>בקשת שיחה</button>
            </div>
            <div class="colt-live-show__notice" data-live-notice aria-live="polite"></div>
        </aside>

        <div class="colt-live-show__mobile-controls" aria-label="בקרת תנועה">
            <button type="button" data-move="up">▲</button>
            <button type="button" data-move="left">◀</button>
            <button type="button" data-move="down">▼</button>
            <button type="button" data-move="right">▶</button>
        </div>
    </div>

    <div class="colt-live-show__placement" data-live-placement hidden>
        <div class="colt-live-show__placement-head">
            <div>
                <p class="colt-live-show__kicker">VENDOR SETUP</p>
                <h2>מקמים עמדה בצדדי האולם</h2>
                <p>ירוק פנוי. אדום תפוס או חוסם מעבר. המעבר המרכזי נשאר פתוח למבקרים.</p>
            </div>
            <button type="button" data-placement-close>חזרה לאולם</button>
        </div>
        <div class="colt-live-show__placement-map" data-placement-map>
            <div class="colt-live-show__placement-world" data-placement-world>
                <span class="colt-live-show__placement-core">מעבר ראשי</span>
            </div>
        </div>
        <button class="colt-live-show__placement-confirm" type="button" data-placement-confirm disabled>אישור מיקום</button>
    </div>

    <dialog class="colt-live-show__modal" data-booth-modal>
        <button class="colt-live-show__modal-close" type="button" data-modal-close aria-label="סגירה">×</button>
        <div data-booth-modal-content></div>
    </dialog>

    <dialog class="colt-live-show__modal colt-live-show__modal--edit" data-booth-editor>
        <button class="colt-live-show__modal-close" type="button" data-editor-close aria-label="סגירה">×</button>
        <div class="colt-live-show__editor">
            <p class="colt-live-show__kicker">BOOTH DESIGN</p>
            <h2>עיצוב העמדה</h2>
            <label>
                <span>כותרת</span>
                <input type="text" data-editor-title maxlength="42">
            </label>
            <label>
                <span>תיאור קצר</span>
                <textarea data-editor-description maxlength="180" rows="3"></textarea>
            </label>
            <div class="colt-live-show__editor-items">
                <?php for ($index = 0; $index < 4; $index += 1) : ?>
                    <fieldset>
                        <legend>פריט <?php echo esc_html((string) ($index + 1)); ?></legend>
                        <input type="text" data-editor-item-title="<?php echo esc_attr((string) $index); ?>" maxlength="40" placeholder="שם פריט">
                        <input type="text" data-editor-item-price="<?php echo esc_attr((string) $index); ?>" maxlength="18" placeholder="מחיר">
                        <input type="text" data-editor-item-note="<?php echo esc_attr((string) $index); ?>" maxlength="72" placeholder="הערה קצרה">
                    </fieldset>
                <?php endfor; ?>
            </div>
            <button type="button" data-editor-save>שמירת עמדה</button>
        </div>
    </dialog>

    <aside class="colt-live-show__chat" data-live-chat hidden>
        <header>
            <strong data-chat-title>שיחה</strong>
            <button type="button" data-chat-close aria-label="סגירה">×</button>
        </header>
        <div class="colt-live-show__chat-requests" data-chat-requests></div>
        <div class="colt-live-show__chat-log" data-chat-log></div>
        <div class="colt-live-show__chat-form">
            <input type="text" data-chat-message maxlength="280" placeholder="כתוב הודעה..." autocomplete="off">
            <button type="button" data-chat-send>שליחה</button>
        </div>
    </aside>

    <script>
        window.COLT_LIVE_SHOW = <?php echo wp_json_encode($live_show_config); ?>;
    </script>
    <script src="<?php echo esc_url(COLT_EXPERIENCE_URL . 'assets/js/colt-live-show.js?ver=' . rawurlencode(COLT_EXPERIENCE_VERSION)); ?>"></script>
</section>
