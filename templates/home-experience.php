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
$origin_world_url = Colt_Experience::asset_url('assets/scene-01-origin/colt-origin-world-clean.png');
$origin_frame_base_url = Colt_Experience::asset_url('assets/scene-01-origin/sequence/frame_');
$origin_frame_poster_url = Colt_Experience::asset_url('assets/scene-01-origin/sequence/frame_0000.webp');
$orbit_guardian_url = Colt_Experience::asset_url('assets/scene-01-origin/guardian-frame-01.png');
?>

<section class="colt-xp" dir="rtl" data-colt-xp data-version="1.2.1">
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

    <section
        class="colt-origin"
        data-origin-sequence
        data-colt-scene="origin"
        style="<?php echo esc_attr("--colt-origin-world: url(" . esc_url($origin_world_url) . ");"); ?>"
    >
        <div class="colt-origin__pin">
            <canvas
                class="colt-origin__sequence"
                data-origin-canvas
                data-frame-base="<?php echo esc_url($origin_frame_base_url); ?>"
                data-frame-count="180"
                data-frame-pad="4"
                data-frame-ext="webp"
                aria-hidden="true"
            ></canvas>
            <img
                class="colt-origin__poster"
                src="<?php echo esc_url($origin_frame_poster_url); ?>"
                alt=""
                loading="eager"
                decoding="sync"
                aria-hidden="true"
            >
            <div class="colt-origin__world" aria-hidden="true"></div>
            <div class="colt-origin__atmosphere" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="colt-origin__sky" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="colt-origin__rail" aria-label="ניווט פתיחת COLT">
                <p>[ COLT ORIGIN ]</p>
                <a href="#colt-core" data-origin-step="0">כניסה לעולם</a>
                <a href="#colt-core" data-origin-step="1">ערך ונדירות</a>
                <a href="<?php echo esc_url(home_url('/shop/')); ?>" data-origin-step="2">התחלה</a>
            </div>

            <a class="colt-origin__service-tab" href="#colt-core">לגלות</a>

            <div class="colt-origin__copy">
                <div class="colt-origin__chapter" data-origin-chapter="0">
                    <p class="colt-kicker">COLT COLLECTORS CLUB</p>
                    <h1>נכנסים לעולם שבו אוסף הופך לנכס.</h1>
                    <p>קלפים נדירים, סלאבים ופריטי אספנות מקבלים כאן במה שנבנית סביב ערך, נדירות וסיפור אישי.</p>
                </div>
                <div class="colt-origin__chapter" data-origin-chapter="1">
                    <p class="colt-kicker">CURATED VALUE</p>
                    <h2>לא רק קונים פריט. בונים אסטרטגיית אספנות.</h2>
                    <div class="colt-origin__metrics" aria-label="שירותי COLT מרכזיים">
                        <span><b>01</b>סינגלים וסלאבים</span>
                        <span><b>02</b>THE VAULT</span>
                        <span><b>03</b>תכשיטי אספנים</span>
                        <span><b>04</b>Mystery Box</span>
                    </div>
                </div>
                <div class="colt-origin__chapter" data-origin-chapter="2">
                    <p class="colt-kicker">ENTER COLT</p>
                    <h2>מצא, שמור, דרג ובנה את הצעד הבא שלך.</h2>
                    <div class="colt-origin__actions">
                        <a href="#colt-core">להתחיל את המסע</a>
                        <a href="<?php echo esc_url(home_url('/shop/')); ?>">כניסה לחנות</a>
                    </div>
                </div>
            </div>

            <div class="colt-origin__label" aria-hidden="true">
                <span>גלול כדי להתקרב</span>
                <b></b>
            </div>
        </div>
    </section>

    <section class="colt-core-world" id="colt-core" data-core-world data-colt-scene="core">
        <div class="colt-core-world__pin">
            <div class="colt-core-world__garden" aria-hidden="true">
                <span class="colt-core-world__sun"></span>
                <span class="colt-core-world__river"></span>
            </div>

            <div class="colt-core-world__rings" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-core-world__bloom" aria-hidden="true">
                <?php for ($petal = 0; $petal < 18; $petal++) : ?>
                    <span></span>
                <?php endfor; ?>
            </div>

            <div class="colt-core-world__copy" data-core-copy>
                <p class="colt-kicker">MAIN EXPERIENCES</p>
                <h2>עולם השירותים המרכזיים של COLT.</h2>
                <p>ארבע כניסות שונות לאותו מסע אספנות: קנייה מדויקת, שמירת ערך, אובייקט אישי וחוויית פתיחה.</p>
            </div>

            <div class="colt-core-world__services" aria-label="שירותי COLT מרכזיים">
                <?php foreach ($core_services as $index => $service) : ?>
                    <a
                        class="colt-core-world__card colt-core-world__card--<?php echo esc_attr($service['tone']); ?>"
                        data-core-card
                        href="<?php echo esc_url($service['url']); ?>"
                        style="--i: <?php echo esc_attr((string) $index); ?>"
                    >
                        <span class="colt-core-world__number">0<?php echo esc_html((string) ($index + 1)); ?></span>
                        <span class="colt-core-world__visual" aria-hidden="true"><i></i><b></b></span>
                        <small><?php echo esc_html($service['eyebrow']); ?></small>
                        <strong><?php echo esc_html($service['title']); ?></strong>
                        <em><?php echo esc_html($service['text']); ?></em>
                        <span class="colt-core-world__cta">לעמוד השירות</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="colt-core-world__progress" aria-hidden="true">
                <span data-core-dot="0"></span>
                <span data-core-dot="1"></span>
                <span data-core-dot="2"></span>
                <span data-core-dot="3"></span>
            </div>

            <div class="colt-core-world__aperture" aria-hidden="true"></div>
        </div>
    </section>

    <section class="colt-orbit" id="colt-services" data-orbit-world data-colt-scene="orbit">
        <div class="colt-orbit__pin">
            <div class="colt-orbit__space" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-orbit__copy" data-orbit-copy>
                <p class="colt-kicker">COLLECTOR UNIVERSE</p>
                <h2>שירותים שמקיפים את האוסף שלך מכל כיוון.</h2>
                <p>איתור, דירוג, מכירה חיה, פריטים מבוקשים ותיק אספנות פיננסי, כולם נפתחים מתוך מערכת אחת.</p>
            </div>

            <div class="colt-orbit__system" aria-label="שירותי אספנים נוספים">
                <div class="colt-orbit__rings" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>

                <div class="colt-orbit__guardian-wrap" aria-hidden="true">
                    <img class="colt-orbit__guardian" src="<?php echo esc_url($orbit_guardian_url); ?>" alt="" loading="lazy">
                    <span></span>
                </div>

                <?php foreach ($support_services as $index => $service) : ?>
                    <a
                        class="colt-orbit__planet colt-orbit__planet--<?php echo esc_attr($service['tone']); ?>"
                        data-orbit-planet
                        data-hyperspace-link
                        href="<?php echo esc_url($service['url']); ?>"
                        style="--i: <?php echo esc_attr((string) $index); ?>"
                    >
                        <span class="colt-orbit__planet-body" aria-hidden="true"><i></i><b></b></span>
                        <span class="colt-orbit__planet-copy">
                            <small>0<?php echo esc_html((string) ($index + 1)); ?> / <?php echo esc_html($service['eyebrow']); ?></small>
                            <strong><?php echo esc_html($service['title']); ?></strong>
                            <em><?php echo esc_html($service['text']); ?></em>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="colt-orbit__dock" aria-hidden="true">
                <span>SEARCH</span>
                <span>GRADE</span>
                <span>SELL</span>
                <span>BUILD</span>
            </div>
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

    <div class="colt-hyperspace" data-hyperspace aria-hidden="true">
        <?php for ($ray = 0; $ray < 32; $ray++) : ?>
            <span style="--angle: <?php echo esc_attr((string) ($ray * 11.25)); ?>deg; --delay: -<?php echo esc_attr((string) ($ray * 8)); ?>ms;"></span>
        <?php endfor; ?>
    </div>
</section>
