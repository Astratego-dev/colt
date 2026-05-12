<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_limit = isset($atts['products']) ? (int) $atts['products'] : 8;
$services = Colt_Experience::services();
$products = Colt_Experience::featured_products($product_limit);
$logo_url = Colt_Experience::logo_url();
?>

<section class="colt-xp" dir="rtl" data-colt-xp>
    <div class="colt-xp__ambient" aria-hidden="true"></div>

    <header class="colt-xp__hero">
        <div class="colt-xp__nav">
            <a class="colt-xp__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
                <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
            </a>
            <div class="colt-xp__nav-actions">
                <a href="<?php echo esc_url(home_url('/shop/')); ?>">חנות</a>
                <a href="<?php echo esc_url(home_url('/services/the-vault/')); ?>">הכספת</a>
                <a class="colt-xp__pill" href="<?php echo esc_url(home_url('/contact/')); ?>">דברו איתנו</a>
            </div>
        </div>

        <div class="colt-xp__hero-grid">
            <div class="colt-xp__hero-copy colt-reveal">
                <p class="colt-xp__kicker">COLLECT THE RARE. OWN THE LEGACY.</p>
                <h1>עולם אספנות פרימיום לקלפים, מוצרים נדירים ושירותי אספנים.</h1>
                <p class="colt-xp__lead">
                    COLT מחברת בין חנות קלפים, שירותי איתור, דירוג, מכירה, כספת ותיקי אספנות
                    לחוויה אחת שמרגישה כמו מועדון אספנים יוקרתי.
                </p>
                <div class="colt-xp__hero-actions">
                    <a class="colt-xp__button colt-xp__button--primary" href="<?php echo esc_url(home_url('/shop/')); ?>">כניסה לחנות</a>
                    <a class="colt-xp__button colt-xp__button--ghost" href="#colt-services">לגלות שירותים</a>
                </div>
            </div>

            <div class="colt-xp__stage" aria-label="COLT collectible card experience">
                <div class="colt-xp__vault-ring" aria-hidden="true"></div>
                <div class="colt-xp__slab" data-colt-card>
                    <div class="colt-xp__slab-top">COLT CERTIFIED</div>
                    <div class="colt-xp__card-art">
                        <span class="colt-xp__shine"></span>
                        <strong>RARE</strong>
                        <small>COLLECTIBLE ASSET</small>
                    </div>
                    <div class="colt-xp__slab-meta">
                        <span>Vault Ready</span>
                        <b>10</b>
                    </div>
                </div>
                <div class="colt-xp__orbit colt-xp__orbit--one" aria-hidden="true">Vault</div>
                <div class="colt-xp__orbit colt-xp__orbit--two" aria-hidden="true">Grading</div>
                <div class="colt-xp__orbit colt-xp__orbit--three" aria-hidden="true">Search</div>
            </div>
        </div>
    </header>

    <div class="colt-xp__marquee" aria-hidden="true">
        <span>POKEMON</span><span>ONE PIECE</span><span>SPORT</span><span>MARVEL</span><span>DISNEY</span><span>GRADED</span><span>SEALED</span>
    </div>

    <section class="colt-xp__section colt-xp__section--services" id="colt-services">
        <div class="colt-xp__section-head colt-reveal">
            <p class="colt-xp__kicker">מה אנחנו עושים</p>
            <h2>שישה מסלולים שמכסים את כל חיי האספן.</h2>
            <p>קנייה, מכירה, שמירה, דירוג, איתור ובנייה חכמה של אוסף עם ערך.</p>
        </div>

        <div class="colt-xp__services">
            <?php foreach ($services as $index => $service) : ?>
                <a class="colt-xp__service colt-reveal" style="--i: <?php echo esc_attr((string) $index); ?>" href="<?php echo esc_url($service['url']); ?>" data-tone="<?php echo esc_attr($service['tone']); ?>">
                    <span class="colt-xp__service-index">0<?php echo esc_html((string) ($index + 1)); ?></span>
                    <span class="colt-xp__service-eyebrow"><?php echo esc_html($service['eyebrow']); ?></span>
                    <strong><?php echo esc_html($service['title']); ?></strong>
                    <em><?php echo esc_html($service['text']); ?></em>
                    <span class="colt-xp__service-arrow">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-xp__section colt-xp__section--vault">
        <div class="colt-xp__vault-copy colt-reveal">
            <p class="colt-xp__kicker">THE VAULT</p>
            <h2>לפריטים שלא אמורים לשכב במגירה.</h2>
            <p>
                שירות הכספת מציג את COLT בצד הפרימיום שלו: שמירה, אבטחה, ביטוח, קטלוג וגישה מסודרת
                לפריטים שהערך שלהם צריך להישמר לאורך זמן.
            </p>
            <a class="colt-xp__button colt-xp__button--primary" href="<?php echo esc_url(home_url('/services/the-vault/')); ?>">לעמוד הכספת</a>
        </div>
        <div class="colt-xp__vault-visual colt-reveal" aria-hidden="true">
            <div class="colt-xp__vault-door"></div>
            <div class="colt-xp__vault-card"></div>
            <div class="colt-xp__vault-lines">
                <span>insured storage</span>
                <span>sealed climate</span>
                <span>collector access</span>
            </div>
        </div>
    </section>

    <?php if (!empty($products)) : ?>
        <section class="colt-xp__section">
            <div class="colt-xp__section-head colt-reveal">
                <p class="colt-xp__kicker">חדשים על המדף</p>
                <h2>מוצרים מתוך החנות, באותה שפה של החוויה.</h2>
            </div>
            <div class="colt-xp__products">
                <?php foreach ($products as $product) : ?>
                    <a class="colt-xp__product colt-reveal" href="<?php echo esc_url($product['url']); ?>">
                        <span class="colt-xp__product-img">
                            <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                        </span>
                        <strong><?php echo esc_html($product['title']); ?></strong>
                        <?php if (!empty($product['price'])) : ?>
                            <span class="colt-xp__price"><?php echo wp_kses_post($product['price']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="colt-xp__closing colt-reveal">
        <p class="colt-xp__kicker">COLT CLUB</p>
        <h2>האתר הוא לא רק חנות. הוא נקודת כניסה לעולם אספנות שלם.</h2>
        <div class="colt-xp__closing-actions">
            <a class="colt-xp__button colt-xp__button--primary" href="<?php echo esc_url(home_url('/shop/')); ?>">לקנות עכשיו</a>
            <a class="colt-xp__button colt-xp__button--ghost" href="<?php echo esc_url(home_url('/services/most-wanted/')); ?>">יש לי פריט למכור</a>
        </div>
    </section>
</section>
