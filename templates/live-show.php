<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = Colt_Experience::logo_url();
$title = (string) ($atts['title'] ?? 'COLT Live Show');
$subtitle = (string) ($atts['subtitle'] ?? 'יריד קלפים חי שבו אספנים ו-vendors נפגשים בזמן אמת.');
$map = Colt_Experience::live_show_map();
?>

<section
    class="colt-live-show"
    dir="rtl"
    data-colt-live-show
    data-map-width="<?php echo esc_attr((string) $map['width']); ?>"
    data-map-height="<?php echo esc_attr((string) $map['height']); ?>"
>
    <div class="colt-live-show__chrome" aria-hidden="true">
        <span></span><span></span><span></span><span></span>
    </div>

    <header class="colt-live-show__topbar">
        <a class="colt-live-show__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div>
            <strong><?php echo esc_html($title); ?></strong>
            <small data-live-status>OFFLINE LOBBY</small>
        </div>
        <div class="colt-live-show__stats" aria-label="נתוני לייב">
            <span><b data-live-count="collectors">0</b> אספנים</span>
            <span><b data-live-count="vendors">0</b> Vendors</span>
            <span><b data-live-count="booths">0</b> עמדות</span>
        </div>
    </header>

    <div class="colt-live-show__entry" data-live-entry>
        <div class="colt-live-show__entry-stage" aria-hidden="true">
            <span class="colt-live-show__entry-neon colt-live-show__entry-neon--one"></span>
            <span class="colt-live-show__entry-neon colt-live-show__entry-neon--two"></span>
            <span class="colt-live-show__entry-neon colt-live-show__entry-neon--three"></span>
            <span class="colt-live-show__entry-booth colt-live-show__entry-booth--left"></span>
            <span class="colt-live-show__entry-booth colt-live-show__entry-booth--right"></span>
            <span class="colt-live-show__entry-avatar colt-live-show__entry-avatar--one"></span>
            <span class="colt-live-show__entry-avatar colt-live-show__entry-avatar--two"></span>
            <span class="colt-live-show__entry-avatar colt-live-show__entry-avatar--three"></span>
        </div>
        <div class="colt-live-show__entry-card">
            <p class="colt-live-show__kicker">COLT LIVE SHOW</p>
            <h1><?php echo esc_html($title); ?></h1>
            <p><?php echo esc_html($subtitle); ?></p>

            <form class="colt-live-show__join" data-live-join method="post" action="#" autocomplete="off" novalidate>
                <fieldset class="colt-live-show__role-grid">
                    <legend>איך אתה נכנס ללייב?</legend>
                    <label class="colt-live-show__role-card">
                        <input class="colt-live-show__role-input" type="radio" name="role" value="collector" checked>
                        <span class="colt-live-show__role-icon" aria-hidden="true"></span>
                        <span>
                            <strong>לקוח / מבקר</strong>
                            <small>נכנס, מסתובב בין עמדות ומבקש שיחה כשמשהו מעניין.</small>
                        </span>
                    </label>
                    <label class="colt-live-show__role-card">
                        <input class="colt-live-show__role-input" type="radio" name="role" value="vendor">
                        <span class="colt-live-show__role-icon" aria-hidden="true"></span>
                        <span>
                            <strong>Vendor עם עמדה</strong>
                            <small>ממקם שולחן בצדדי האולם, בוחר צבע ומציג פריטים למכירה.</small>
                        </span>
                    </label>
                </fieldset>

                <label>
                    <span>שם שיופיע מעל הדמות</span>
                    <input type="text" name="name" maxlength="28" placeholder="Collector">
                </label>

                <div class="colt-live-show__vendor-fields" data-vendor-fields>
                    <label>
                        <span>כותרת העמדה</span>
                        <input type="text" name="booth_title" maxlength="42" placeholder="Pokemon slabs / One Piece singles">
                    </label>
                    <label>
                        <span>צבע עמדה</span>
                        <input type="color" name="color" value="#86f7d4">
                    </label>
                </div>

                <button type="button" data-live-start>כניסה ללייב</button>
            </form>
        </div>
    </div>

    <div class="colt-live-show__game" data-live-game aria-label="COLT Live Show game world">
        <div class="colt-live-show__viewport" data-live-viewport>
            <div class="colt-live-show__world" data-live-world>
                <div class="colt-live-show__floor" aria-hidden="true"></div>
                <div class="colt-live-show__walkway" aria-hidden="true">
                    <span>COLT LIVE HALL</span>
                    <i></i>
                </div>
                <div class="colt-live-show__side-zone colt-live-show__side-zone--top" aria-hidden="true"></div>
                <div class="colt-live-show__side-zone colt-live-show__side-zone--right" aria-hidden="true"></div>
                <div class="colt-live-show__side-zone colt-live-show__side-zone--bottom" aria-hidden="true"></div>
                <div class="colt-live-show__side-zone colt-live-show__side-zone--left" aria-hidden="true"></div>
                <div class="colt-live-show__layer" data-live-booths></div>
                <div class="colt-live-show__layer" data-live-npcs></div>
                <div class="colt-live-show__layer" data-live-players></div>
                <div class="colt-live-show__placement-ghost" data-placement-ghost aria-hidden="true">עמדה</div>
            </div>
        </div>

        <aside class="colt-live-show__hud">
            <div class="colt-live-show__panel">
                <strong data-live-name>Guest</strong>
                <small data-live-role>Collector</small>
                <p>חצים / WASD לתנועה. התקרב לעמדות כדי לפתוח כרטיס vendor או לבקש שיחה.</p>
            </div>
            <div class="colt-live-show__actions">
                <button type="button" data-live-place-booth hidden>מיקום עמדה</button>
                <button type="button" data-live-edit-booth hidden>עריכת עמדה</button>
                <button type="button" data-live-view-booth disabled>צפייה בעמדה</button>
                <button type="button" data-live-request-chat disabled>בקשת שיחה</button>
            </div>
            <div class="colt-live-show__notice" data-live-notice></div>
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
                <h2>בחר מקום פנוי לעמדה שלך</h2>
                <p>אפשר למקם עמדות רק בצדדים של המפה. ירוק פנוי, אדום תפוס או חוסם מעבר.</p>
            </div>
            <button type="button" data-placement-close>סגירה</button>
        </div>
        <div class="colt-live-show__placement-map" data-placement-map>
            <div class="colt-live-show__placement-world" data-placement-world>
                <span class="colt-live-show__placement-core">מעבר ראשי פתוח</span>
            </div>
        </div>
        <button class="colt-live-show__placement-confirm" type="button" data-placement-confirm disabled>אישור מיקום</button>
    </div>

    <dialog class="colt-live-show__modal" data-booth-modal>
        <form method="dialog">
            <button class="colt-live-show__modal-close" value="close" aria-label="סגירה">×</button>
        </form>
        <div data-booth-modal-content></div>
    </dialog>

    <dialog class="colt-live-show__modal colt-live-show__modal--edit" data-booth-editor>
        <form class="colt-live-show__editor" data-booth-editor-form method="dialog">
            <button class="colt-live-show__modal-close" value="close" aria-label="סגירה">×</button>
            <p class="colt-live-show__kicker">BOOTH DESIGN</p>
            <h2>עיצוב העמדה שלך</h2>
            <label>
                <span>כותרת</span>
                <input type="text" name="title" maxlength="42">
            </label>
            <label>
                <span>צבע</span>
                <input type="color" name="color" value="#86f7d4">
            </label>
            <label>
                <span>תיאור קצר</span>
                <textarea name="description" maxlength="180" rows="3"></textarea>
            </label>
            <div class="colt-live-show__editor-items">
                <?php for ($index = 0; $index < 4; $index += 1) : ?>
                    <fieldset>
                        <legend>מוצר <?php echo esc_html((string) ($index + 1)); ?></legend>
                        <input type="text" name="item_title_<?php echo esc_attr((string) $index); ?>" maxlength="40" placeholder="שם פריט">
                        <input type="text" name="item_price_<?php echo esc_attr((string) $index); ?>" maxlength="18" placeholder="מחיר">
                        <input type="text" name="item_note_<?php echo esc_attr((string) $index); ?>" maxlength="72" placeholder="הערה קצרה">
                    </fieldset>
                <?php endfor; ?>
            </div>
            <button type="submit">שמירת עמדה</button>
        </form>
    </dialog>

    <aside class="colt-live-show__chat" data-live-chat hidden>
        <header>
            <strong data-chat-title>שיחה</strong>
            <button type="button" data-chat-close aria-label="סגירה">×</button>
        </header>
        <div class="colt-live-show__chat-requests" data-chat-requests></div>
        <div class="colt-live-show__chat-log" data-chat-log></div>
        <form class="colt-live-show__chat-form" data-chat-form>
            <input type="text" name="message" maxlength="280" placeholder="כתוב הודעה...">
            <button type="submit">שליחה</button>
        </form>
    </aside>
</section>
