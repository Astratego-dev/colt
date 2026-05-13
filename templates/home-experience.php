<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_limit = isset($atts['products']) ? (int) $atts['products'] : 8;
$services = Colt_Experience::services();
$core_services = array_values(array_filter($services, static function ($service) {
    return isset($service['group']) && $service['group'] === 'core';
}));
$support_services = array_values(array_filter($services, static function ($service) {
    return !isset($service['group']) || $service['group'] !== 'core';
}));
$products = Colt_Experience::featured_products($product_limit);
$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$guardian_frames = [
    Colt_Experience::asset_url('assets/img/guardian-frame-01.svg'),
    Colt_Experience::asset_url('assets/img/guardian-frame-02.svg'),
    Colt_Experience::asset_url('assets/img/guardian-frame-03.svg'),
    Colt_Experience::asset_url('assets/img/guardian-frame-04.svg'),
    Colt_Experience::asset_url('assets/img/guardian-frame-05.svg'),
];
?>

<section class="colt-xp" dir="rtl" data-colt-xp data-version="0.5.0">
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav" aria-label="COLT">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#colt-core">חוויות מרכזיות</a>
            <a href="#colt-services">שירותים</a>
            <a href="<?php echo esc_url(home_url('/shop/')); ?>">חנות</a>
        </div>
    </nav>

    <section class="colt-origin" data-origin-sequence data-colt-scene="origin">
        <div class="colt-origin__pin">
            <div class="colt-origin__sky" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>

            <div class="colt-origin__copy">
                <p class="colt-kicker">COLT EXPERIENCE</p>
                <h1>קלפים, שירותים ואספנות יוקרתית במקום אחד.</h1>
                <p>חנות ושירותי אספנים לקנייה, שמירה, דירוג, איתור ומכירה של פריטים עם ערך.</p>
                <div class="colt-origin__actions">
                    <a href="#colt-core">לגלות את COLT</a>
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>">כניסה לחנות</a>
                </div>
            </div>

            <div class="colt-origin__guardian" aria-hidden="true">
                <?php foreach ($guardian_frames as $index => $frame_url) : ?>
                    <img src="<?php echo esc_url($frame_url); ?>" alt="" data-guardian-frame="<?php echo esc_attr((string) $index); ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                <?php endforeach; ?>
                <span class="colt-origin__void"></span>
            </div>

            <div class="colt-origin__portal" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>

            <div class="colt-origin__label" aria-hidden="true">
                <span>SCROLL</span>
                <b></b>
            </div>
        </div>
    </section>

    <section class="colt-core" id="colt-core" data-colt-scene="core">
        <div class="colt-section-head colt-reveal">
            <p class="colt-kicker">MAIN EXPERIENCES</p>
            <h2>ארבע חוויות מרכזיות לאספן.</h2>
            <p>המסלולים שמגדירים את COLT: לקנות, לשמור, להפוך פריט לאובייקט, ולפתוח Mystery Box.</p>
        </div>

        <div class="colt-core__grid">
            <?php foreach ($core_services as $index => $service) : ?>
                <a class="colt-core-card colt-reveal" href="<?php echo esc_url($service['url']); ?>" style="--i: <?php echo esc_attr((string) $index); ?>">
                    <img src="<?php echo esc_url($service['image']); ?>" alt="" loading="lazy">
                    <span>0<?php echo esc_html((string) ($index + 1)); ?></span>
                    <small><?php echo esc_html($service['eyebrow']); ?></small>
                    <strong><?php echo esc_html($service['title']); ?></strong>
                    <em><?php echo esc_html($service['text']); ?></em>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-deep" data-colt-scene="vault">
        <div class="colt-deep__visual colt-reveal" aria-hidden="true">
            <span></span><span></span><span></span>
            <b>VAULT</b>
        </div>
        <div class="colt-deep__copy colt-reveal">
            <p class="colt-kicker">SECURE VALUE</p>
            <h2>פריטים יקרים צריכים תנאים שמתאימים לערך שלהם.</h2>
            <p>THE VAULT מיועד לאחסון מבוטח והרמטי, עם חשיבה על שמירת ערך לאורך זמן וגישה מסודרת לפריטים החשובים שלך.</p>
            <a href="<?php echo esc_url(home_url('/services/the-vault/')); ?>">לעמוד הכספת</a>
        </div>
    </section>

    <section class="colt-services" id="colt-services" data-colt-scene="services">
        <div class="colt-section-head colt-reveal">
            <p class="colt-kicker">COLLECTOR SERVICES</p>
            <h2>שירותים לאספנים שרוצים יותר מעוד רכישה.</h2>
            <p>איתור, דירוג, מכירה, תיק השקעות ופריטים מבוקשים, כולם מחוברים למסע אספנות אחד.</p>
        </div>
        <div class="colt-services__list">
            <?php foreach ($support_services as $index => $service) : ?>
                <a class="colt-service-row colt-reveal" href="<?php echo esc_url($service['url']); ?>" style="--i: <?php echo esc_attr((string) $index); ?>">
                    <span>0<?php echo esc_html((string) ($index + 1)); ?></span>
                    <strong><?php echo esc_html($service['title']); ?></strong>
                    <em><?php echo esc_html($service['text']); ?></em>
                    <b>↗</b>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($products)) : ?>
        <section class="colt-products" data-colt-scene="products">
            <div class="colt-section-head colt-reveal">
                <p class="colt-kicker">LATEST DROPS</p>
                <h2>מהחנות אל החוויה.</h2>
                <p>מוצרים מתוך WooCommerce, עם במה שמתאימה לעולם אספנות יוקרתי.</p>
            </div>
            <div class="colt-products__grid">
                <?php foreach ($products as $product) : ?>
                    <a class="colt-product colt-reveal" href="<?php echo esc_url($product['url']); ?>">
                        <span><img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy"></span>
                        <strong><?php echo esc_html($product['title']); ?></strong>
                        <?php if (!empty($product['price'])) : ?>
                            <em><?php echo wp_kses_post($product['price']); ?></em>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="colt-end" data-colt-scene="end">
        <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
        <p class="colt-kicker">OWN THE LEGACY</p>
        <h2>בחר את הצעד הבא באוסף שלך.</h2>
        <div>
            <a href="<?php echo esc_url(home_url('/shop/')); ?>">לחנות</a>
            <a href="<?php echo esc_url(home_url('/services/the-vault/')); ?>">לשירותי COLT</a>
        </div>
    </section>
</section>
