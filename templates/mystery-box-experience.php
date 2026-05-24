<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$product_url = !empty($atts['product_url']) ? (string) $atts['product_url'] : home_url('/shop/');
$contact_url = !empty($atts['contact_url']) ? (string) $atts['contact_url'] : home_url('/contact/');
$whatsapp_url = !empty($atts['whatsapp']) ? (string) $atts['whatsapp'] : $contact_url;
$price = '250 ש"ח';
$contents = [
    ['title' => 'קלף מדורג', 'text' => 'סלאב אחד שמייצר את רגע ה־showcase של הקופסה.', 'meta' => 'Graded'],
    ['title' => 'חפיסת בוסטר', 'text' => 'חבילת פתיחה אחת מתוך העולם שבחרת.', 'meta' => 'Booster'],
    ['title' => 'קלף סינגל', 'text' => 'קלף נוסף להשלמת החוויה ולבניית האוסף.', 'meta' => 'Single'],
];
$worlds = [
    ['value' => 'pokemon', 'label' => 'Pokemon', 'text' => 'מפלצות, סטים, להיטים ונוסטלגיה'],
    ['value' => 'one-piece', 'label' => 'One Piece', 'text' => 'פיראטים, דמויות ופתיחות באנרגיה גבוהה'],
];
$languages = [
    ['value' => 'english', 'label' => 'English', 'text' => 'מוצרים באנגלית בלבד'],
    ['value' => 'japanese', 'label' => 'Japanese', 'text' => 'מוצרים ביפנית בלבד'],
];
?>

<section
    class="colt-xp colt-mystery-xp"
    dir="rtl"
    data-colt-xp
    data-mystery-xp
    data-version="1.9.0"
>
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav colt-mystery-nav" aria-label="COLT Mystery Box">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#mystery-open">הפתיחה</a>
            <a href="#mystery-contents">מה בפנים</a>
            <a href="#mystery-options">בחירה</a>
            <a href="#mystery-buy">רכישה</a>
        </div>
    </nav>

    <section class="colt-mystery-hero" id="mystery-open" data-mystery-hero>
        <div class="colt-mystery-hero__pin">
            <div class="colt-mystery-hero__backdrop" data-mystery-hero-bg aria-hidden="true"></div>
            <div class="colt-mystery-orbit" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>
            <div class="colt-mystery-box" data-mystery-box aria-hidden="true">
                <span class="colt-mystery-box__shadow"></span>
                <span class="colt-mystery-box__tray"></span>
                <span class="colt-mystery-box__lid"></span>
                <span class="colt-mystery-box__core"></span>
                <span class="colt-mystery-box__seal"></span>
                <span class="colt-mystery-box__glow"></span>
            </div>
            <div class="colt-mystery-stream" data-mystery-stream aria-hidden="true">
                <span class="colt-mystery-stream__slab"><i></i><em>Graded</em></span>
                <span class="colt-mystery-stream__booster"><i></i><em>Booster</em></span>
                <span class="colt-mystery-stream__single"><i></i><em>Single</em></span>
            </div>
            <div class="colt-mystery-foil" data-mystery-foil aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-mystery-hero__copy" data-mystery-hero-copy>
                <p class="colt-kicker">COLT MYSTERY BOX</p>
                <h1>פותחים קופסה אחת. מקבלים שלושה רגעי אספנות.</h1>
                <p>בכל מיסטרי בוקס מחכה קלף מדורג, חפיסת בוסטר וקלף סינגל. בוחרים עולם, בוחרים שפה, ונותנים לפתיחה לעשות את שלה.</p>
                <div class="colt-mystery-price">
                    <span><?php echo esc_html($price); ?></span>
                    <small>Pokemon או One Piece · English או Japanese</small>
                </div>
            </div>

            <div class="colt-mystery-hero__rail" data-mystery-rail aria-hidden="true">
                <span>sealed</span>
                <span>crack</span>
                <span>reveal</span>
                <span>choose</span>
            </div>
        </div>
    </section>

    <section class="colt-mystery-reveal" id="mystery-contents" data-mystery-reveal>
        <div class="colt-mystery-reveal__pin">
            <div class="colt-mystery-reveal__backdrop" data-mystery-reveal-bg aria-hidden="true"></div>
            <div class="colt-mystery-reveal__burst" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div class="colt-mystery-rip" data-mystery-rip aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="colt-mystery-showcase" data-mystery-showcase aria-hidden="true">
                <span class="colt-mystery-showcase__slab">
                    <i></i>
                    <em>GRADED</em>
                </span>
                <span class="colt-mystery-showcase__booster">
                    <i></i>
                    <em>BOOSTER</em>
                </span>
                <span class="colt-mystery-showcase__single">
                    <i></i>
                    <em>SINGLE</em>
                </span>
            </div>

            <div class="colt-mystery-reveal__copy" data-mystery-reveal-copy>
                <p class="colt-kicker">WHAT IS INSIDE</p>
                <h2>הפתיחה בנויה כמו דרופ קטן.</h2>
                <p>כל רכיב בקופסה מקבל תפקיד: סלאב שנותן ערך מרכזי, בוסטר שנותן מתח של פתיחה, וסינגל שמוסיף עוד שכבת אספנות.</p>
            </div>

            <div class="colt-mystery-items" aria-label="תכולת המיסטרי בוקס">
                <?php foreach ($contents as $index => $item) : ?>
                    <article class="colt-mystery-item" data-mystery-item style="--i: <?php echo esc_attr((string) $index); ?>">
                        <small><?php echo esc_html($item['meta']); ?></small>
                        <strong><?php echo esc_html($item['title']); ?></strong>
                        <p><?php echo esc_html($item['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="colt-mystery-options" id="mystery-options" data-mystery-options>
        <div class="colt-mystery-options__shell">
            <div class="colt-mystery-options__copy" data-mystery-options-copy>
                <p class="colt-kicker">PICK YOUR DROP</p>
                <h2>ארבע אפשרויות, מחיר אחד.</h2>
                <p>הקופסה זמינה רק בשילובים האלה: Pokemon או One Piece, ובתוך כל עולם English או Japanese. המחיר קבוע: <?php echo esc_html($price); ?>.</p>
            </div>

            <div class="colt-mystery-picker" data-mystery-picker data-product-url="<?php echo esc_url($product_url); ?>">
                <div class="colt-mystery-picker__group" aria-label="בחירת עולם">
                    <small>עולם</small>
                    <?php foreach ($worlds as $index => $world) : ?>
                        <button type="button" data-mystery-choice="world" data-value="<?php echo esc_attr($world['value']); ?>" class="<?php echo $index === 0 ? 'is-active' : ''; ?>">
                            <span><?php echo esc_html($world['label']); ?></span>
                            <em><?php echo esc_html($world['text']); ?></em>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="colt-mystery-picker__group" aria-label="בחירת שפה">
                    <small>שפה</small>
                    <?php foreach ($languages as $index => $language) : ?>
                        <button type="button" data-mystery-choice="language" data-value="<?php echo esc_attr($language['value']); ?>" class="<?php echo $index === 0 ? 'is-active' : ''; ?>">
                            <span><?php echo esc_html($language['label']); ?></span>
                            <em><?php echo esc_html($language['text']); ?></em>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="colt-mystery-summary" data-mystery-summary>
                <small>הבחירה שלך</small>
                <strong><span data-mystery-summary-world>Pokemon</span> · <span data-mystery-summary-language>English</span></strong>
                <em><?php echo esc_html($price); ?></em>
            </div>
        </div>
    </section>

    <section class="colt-mystery-buy" id="mystery-buy" data-mystery-buy>
        <div class="colt-mystery-buy__shell">
            <div class="colt-mystery-buy__brand" data-mystery-buy-brand>
                <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
                <div>
                    <p class="colt-kicker">READY TO OPEN</p>
                    <h2>בחרת שילוב? עכשיו נשאר לפתוח.</h2>
                    <p>הקופסה מיועדת לאספנים שרוצים חוויית פתיחה מוגדרת וברורה: עולם אחד, שפה אחת, שלושה פריטים, מחיר אחד.</p>
                </div>
            </div>

            <div class="colt-mystery-buy__panel" data-mystery-buy-panel>
                <a href="<?php echo esc_url($product_url); ?>" data-mystery-checkout>רכישת Mystery Box</a>
                <a href="<?php echo esc_url($whatsapp_url); ?>">שאלה לפני רכישה</a>
                <a href="<?php echo esc_url($contact_url); ?>">יצירת קשר</a>
            </div>
        </div>
    </section>
</section>
